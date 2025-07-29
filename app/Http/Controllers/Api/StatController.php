<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Validator;
use Storage;
use Config;
use Carbon\Carbon;
use PDF;


use App\Models\Form;
use App\Models\Term;
use App\Models\Student;
use App\Models\User;
use App\Models\AppMessage;
use App\Models\Formstream;
use App\Models\Subject;
use App\Models\Payment;
use App\Models\Attendance;
use App\Models\Enrollment;
use App\Http\Requests\StatsRequest;

class StatController extends Controller
{
    public function dashboard(StatsRequest $request)
    {
        $xMonthsAgo = Carbon::now()->subMonths($request->monthsAgo);
        $doubleXMonthsAgo = Carbon::now()->subMonths($request->monthsAgo * 2);
        $this->aut_update_current_trm();
    
        $stat = [
            'payment' => $this->paymentStats($xMonthsAgo),
            'appMessages' => $this->appMessages($xMonthsAgo),
            'teachers' => $this->teachersStats($xMonthsAgo, $doubleXMonthsAgo),
            'students' => $this->studentsStats($xMonthsAgo, $doubleXMonthsAgo),
            'parents' => $this->parentsStats($xMonthsAgo, $doubleXMonthsAgo),
            'levels' => Form::all()->count(),
            'levelStreams' => Formstream::all()->count(),
            'programs' => Subject::all()->count(),
            'attendance' => $this->attendanceStat($xMonthsAgo),
            'enrollment' => $this->enrollmentStats($xMonthsAgo, $doubleXMonthsAgo)
        ];
        
        return response([
            'status' => 200,
            'data' => $stat,
        ], 200);
    }

    protected function enrollmentStats($xMonthsAgo, $doubleXMonthsAgo){
        $total = Enrollment::where('status', 'enrolled')
            ->count();
        $period = Enrollment::where('created_at', '>=', $xMonthsAgo)
            ->where('status', 'enrolled')
            ->count();
        $doublePeriod = Enrollment::where('created_at', '>=', $doubleXMonthsAgo)
            ->where('status', 'enrolled')
            ->count();
        $stat = new \stdClass();
        $stat->previous = $doublePeriod - $period;
        $stat->current = $period;
        $stat->difference = $stat->previous - $stat->current;
        $stat->ratio = $this->findRatio($stat->previous, $stat->current);
        $stat->total = $total;
        return $stat;
    }

    protected function studentsStats($xMonthsAgo, $doubleXMonthsAgo){
        $period = Student::where('created_at', '>=', $xMonthsAgo)
            ->where('is_active', 1)
            ->count();
        $doublePeriod = Student::where('created_at', '>=', $doubleXMonthsAgo)
            ->where('is_active', 1)
            ->count();
        $stat = new \stdClass();
        $stat->previous = $doublePeriod - $period;
        $stat->current = $period;
        $stat->difference = $stat->previous - $stat->current;
        $stat->ratio = $this->findRatio($stat->previous, $stat->current);
        return $stat;
    }

    protected function teachersStats($xMonthsAgo, $doubleXMonthsAgo){
        $period = User::where('created_at', '>=', $xMonthsAgo)
            ->where('is_active', 1)
            ->where('is_teacher', 1)
            ->count();
        $doublePeriod = User::where('created_at', '>=', $doubleXMonthsAgo)
            ->where('is_active', 1)
            ->where('is_teacher', 1)
            ->count();
        $stat = new \stdClass();
        $stat->previous = $doublePeriod - $period;
        $stat->current = $period;
        $stat->difference = $stat->previous - $stat->current;
        $stat->ratio = $this->findRatio($stat->previous, $stat->current);
        return $stat;
    }

     protected function parentsStats($xMonthsAgo, $doubleXMonthsAgo){
        $period = User::where('created_at', '>=', $xMonthsAgo)
            ->where('is_active', 1)
            ->where('is_parent', 1)
            ->count();
        $doublePeriod = User::where('created_at', '>=', $doubleXMonthsAgo)
            ->where('is_active', 1)
            ->where('is_parent', 1)
            ->count();
        $stat = new \stdClass();
        $stat->previous = $doublePeriod - $period;
        $stat->current = $period;
        $stat->difference = $stat->previous - $stat->current;
        $stat->ratio = $this->findRatio($stat->previous, $stat->current);
        return $stat;
    }

    protected function findRatio($previous, $current){
        $dif = $previous - $current;
        if($dif == 0 ){
            return 0.0;
        }
        if($dif < 0){
            return (($current - $previous)/($previous + $current)) * 100;
        }

        return -(($previous - $current)/($previous + $current)) * 100;
        
    }

    protected function attendanceStat($xMonthsAgo)
    {
        $attendanceStats = Attendance::where('created_at', '>=', $xMonthsAgo)
            ->selectRaw('
                YEAR(created_at) as year,
                MONTH(created_at) as month,
                SUM(CASE WHEN is_in = 1 THEN 1 ELSE 0 END) as present,
                SUM(CASE WHEN is_in = 0 THEN 1 ELSE 0 END) as absent'
            )
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();
        return $attendanceStats;
    }

    protected function paymentStats($xMonthsAgo)
    {
        $totalAmount = Payment::where('created_at', '>=', $xMonthsAgo)->sum('amount');
        $monthlyTotals = Payment::where('created_at', '>=', $xMonthsAgo)
            ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, SUM(amount) as total')
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();
        $stat = new \stdClass();
        $stat->count = $totalAmount;
        $stat->trend = $monthlyTotals;
        return $stat;
    }
   
    protected function appMessages($xMonthsAgo)
    {
        $sendCount = AppMessage::where('created_at', '>=', $xMonthsAgo)
            ->where('send', true)
            ->count();
        $sendTrend = AppMessage::where('created_at', '>=', $xMonthsAgo)
            ->where('send', true)
            ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, COUNT(*) as total')
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();
        $pendingApprovalCount = AppMessage::where('created_at', '>=', $xMonthsAgo)
            ->where('send', false)
            ->where('approved', false)
            ->count();
        $pendingApprovalTrend = AppMessage::where('created_at', '>=', $xMonthsAgo)
            ->where('send', false)
            ->where('approved', false)
            ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, COUNT(*) as total')
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();
        
        $stat = new \stdClass();
        $stat->sendCount = $sendCount;
        $stat->sendTrend = $sendTrend;
        $stat->pendingApprovalCount = $pendingApprovalCount;
        $stat->pendingApprovalTrend = $pendingApprovalTrend;
        return $stat;
    }
 
    protected function has_any_terms()
    {
        if(Term::where('id', '!=', 0)->count())
        {
            return true;
        }
        return false;
    }

    protected function aut_update_current_trm()
    {
        $now = date('Y-m-d');
        $cr = Term::where('is_current', true)->first();
        if( is_null($cr) )
        {
            if( !$this->has_any_terms() )
            {
                return;
            }
            $term = Term::where('start', '<=', $now)
                ->where('is_current', false)
                ->orderBy('id', 'desc')->first();
            $term->is_current = true;
            $term->save();
        }
        else
        {
            $ccurent_trm_id = $cr->id;
            $ccurent_trm_end = date('Y-m-d', strtotime($cr->end));
            if( $ccurent_trm_end < $now )
            {
                Term::find($ccurent_trm_id + 1)->update([
                    'is_current' => true,
                ]);
            }
        }
        return;
    }
}
