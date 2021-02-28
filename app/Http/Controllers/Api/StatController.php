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


use App\Models\Setup;
use App\Models\Term;
use App\Models\Student;
use App\Models\Teacher;

class StatController extends Controller
{
    public function dashboard()
    {
       $this->aut_update_current_trm();

        $stat = [
            'cont' => 'KES. ' . $this->find_feespay_avr(),
            'me' => $this->find_learners_count(),
            'def' => $this->find_teachers_count(),
            'loan' => $this->find_bal_avr(),
            'toptentrucks' => [],
            'top_five_exp' => [],
            'top_five_loads' => [],
            'top_five_mileage' => [],
        ];
        return response([
            'status' => 200,
            'data' => $stat,
        ], 200);
    }
    protected function find_bal_avr()
    {
        return 3.5;
    }
    protected function find_six_m_ago()
    {
        return [
            date('Y-m-d'),
            date("Y-m-d", strtotime("-1 months")),
            date("Y-m-d", strtotime("-2 months")),
            date("Y-m-d", strtotime("-3 months")),
            date("Y-m-d", strtotime("-4 months")),
            date("Y-m-d", strtotime("-5 months")),
        ];
    }
    protected function find_feespay_avr()
    {
        return 75;
    }
    protected function find_teachers_count()
    {
        return 0;
    }
    protected function find_learners_count()
    {
        return Student::where('id', '!=', 0)->count();
    }
    protected function count_months($fdate, $sdate)
    {
        $ts1 = strtotime($fdate);
        $ts2 = strtotime($sdate);

        $year1 = date('Y', $ts1);
        $year2 = date('Y', $ts2);
        
        $month1 = intval(date('m', $ts1));
        $month2 = intval(date('m', $ts2));

        $diff = (($year2 - $year1) * 12) + ($month2 - $month1);
        if( $diff == 0 )
        {
            return 1;
        }
        return $diff;
    }
    protected function format_ks($k)
    {
        if(intval($k) > 1000 )
        {
            return number_format($k/1000, 2) . 'k';
        }
        return number_format($k, 2);
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
