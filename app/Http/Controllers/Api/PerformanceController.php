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

/** custom controllers */
use App\Http\Controllers\Api\PHPlot;

use App\Models\Performance;
use App\Models\Term;
use App\Models\Assessmentgroup;
use App\Models\Subject;
use App\Models\Student;
use App\Models\Formstream;
use App\Models\Enrollment;
use App\Models\User;
use App\Models\Setup;
use App\Models\Form;
use App\Models\Scale;
use App\Models\Fee;
use App\Models\Studentsport;
use App\Models\Sport;

/** Charts */
use App\Charts\StudentChart;
/** mail */
use Illuminate\Support\Facades\Mail;
use App\Mail\Welcome;
use App\Mail\Code;

class PerformanceController extends Controller
{
    public function add(Request $request)
    {
        if( !Auth::user()->is_super && !Auth::user()->is_admin )
        {
            return response([
                'status' => 400,
                'message' => 'Permission Denied. Only super admins allowed.',
                'errors' => [],
            ], 400);
        }
        try{
            $validator = Validator::make($request->all(), [
                'student' => 'required|string|not_in:nn',
                'subject' => 'required|string|not_in:nn',
                'group' => 'required|string|not_in:nn',
                'mark' => 'required|integer',
                'remark' => 'string',
            ]);
            if( $validator->fails() ){
                return response([
                    'status' => 400,
                    'message' => 'Error: Invalid field(s) detected',
                    'errors' => $validator->errors()->all(),
                ], 400);
            }
            $input = $request->all();
            if(!strlen($input['remark']))
            {
                $input['remark'] = 'n/a';
            }
            if( !$this->has_current_trm() )
            {
                return response([
                    'status' => 400,
                    'message' => 'Current term not set',
                    'data' => [],
                ], 400);
            }
            if( intval($input['mark']) > 100 || intval($input['mark']) < 0 )
            {
                return response([
                    'status' => 400,
                    'message' => 'Invalid marks',
                    'data' => [],
                ], 400);
            }
            $input['term'] = $this->find_current_trm();
            $stud_meta = Student::find($input['student']);
            $isValidSubject = Enrollment::where('student', $input['student'])
                ->where('subject', $input['subject'])
                ->where('status', 'enrolled')
                ->exists();
            if(!$isValidSubject){
                return response([
                    'status' => 400,
                    'message' => 'Invalid subject, learner not enrolled',
                    'data' => [],
                ], 400);
            }
            $input['grade'] = $this->extract_g_scale($input['mark'], $stud_meta->form);
            Performance::create($input);
            return response([
                'status' => 200,
                'message' => 'Success. Done',
                'data' => $this->find_performance_data(),
            ], 200);
        } catch (\Illuminate\Database\QueryException $e) {
            return response([
                'status' => 400,
                'message' => "Server error. Invalid data",
                'errors' => $e->getMessage(),
            ], 400);
        } catch (PDOException $e) {
            return response([
                'status' => 400,
                'message' => "Db error. Invalid data",
                'errors' => $e->getMessage(),
            ], 400);
        }
    }
    public function edit(Request $request, $id)
    {
        if( !Auth::user()->is_super && !Auth::user()->is_admin )
        {
            return response([
                'status' => 400,
                'message' => 'Permission Denied. Only super admins allowed.',
                'errors' => [],
            ], 400);
        }
        try{
            $validator = Validator::make($request->all(), [
                'student' => 'required|string|not_in:nn',
                'subject' => 'required|string|not_in:nn',
                'group' => 'required|string|not_in:nn',
                'mark' => 'required|integer',
                'remark' => 'string',
            ]);
            if( $validator->fails() ){
                return response([
                    'status' => 400,
                    'message' => 'Error: Invalid field(s) detected',
                    'errors' => $validator->errors()->all(),
                ], 400);
            }
            $input = $request->all();
            if(!strlen($input['remark']))
            {
                $input['remark'] = 'n/a';
            }
            if( !$this->has_current_trm() )
            {
                return response([
                    'status' => 400,
                    'message' => 'Current term not set',
                    'data' => [],
                ], 400);
            }
            if( intval($input['mark']) > 100 || intval($input['mark']) < 0 )
            {
                return response([
                    'status' => 400,
                    'message' => 'Invalid marks',
                    'data' => [],
                ], 400);
            }
            $input['term'] = $this->find_current_trm();
            $stud_meta = Student::find($input['student']);
            $isValidSubject = Enrollment::where('student', $input['student'])
                ->where('subject', $input['subject'])
                ->where('status', 'enrolled')
                ->exists();
            if(!$isValidSubject){
                return response([
                    'status' => 400,
                    'message' => 'Invalid subject, learner not enrolled',
                    'data' => [],
                ], 400);
            }
            $input['grade'] = $this->extract_g_scale($input['mark'], $stud_meta->form);
            Performance::find($id)->update($input);
            return response([
                'status' => 200,
                'message' => 'Success. Done',
                'data' => $this->find_performance_data(),
            ], 200);
        } catch (\Illuminate\Database\QueryException $e) {
            return response([
                'status' => 400,
                'message' => "Server error. Invalid data",
                'errors' => $e->getMessage(),
            ], 400);
        } catch (PDOException $e) {
            return response([
                'status' => 400,
                'message' => "Db error. Invalid data",
                'errors' => $e->getMessage(),
            ], 400);
        }
    }
    public function drop($id)
    {
        if( !Auth::user()->is_super && !Auth::user()->is_admin )
        {
            return response([
                'status' => 200,
                'message' => "Not deleted. Permission denied",
                'errors' => [],
            ], 200);
        }
        Performance::find($id)->delete();
        return response([
            'status' => 200,
            'message' => "Done successfully",
            'errors' => [],
        ], 200);
    }
    
    public function findall()
    {
        return response([
            'status' => 200,
            'message' => "Done successfully",
            'data' => $this->find_performance_data(),
        ], 200);
    }
    public function find($id)
    {
        $data = Performance::find($id);
        if( is_null($data) )
        {
            return response([
                'status' => 200,
                'message' => "Done successfully",
                'data' => [],
            ], 200);
        }
        return response([
            'status' => 200,
            'message' => "Done successfully",
            'data' => $data,
        ], 200);
    }
    public function findbystudent(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'term' => 'required|string|not_in:nn',
            'student' => 'required|string',
        ]);
        if( $validator->fails() ){
            return response([
                'status' => 400,
                'message' => 'Select term and enter admission number',
                'errors' => $validator->errors()->all(),
            ], 400);
        }
        $input = $request->all();
        $student_m = Student::where('admission', $input['student'])->first();
        if(is_null($student_m))
        {
            return response([
                'status' => 400,
                'message' => 'No student was found with that admission number',
                'errors' => [],
            ], 400);
        }
        $return_data = [];
        if( !Auth::user()->is_super && !Auth::user()->is_admin )
        {
            $return_data = [];
        }

        $return_data = Performance::where('term', $input['term'])
            ->where('student', $student_m->id)->orderBy('id', 'desc')->get();
        if(is_null($return_data))
        {
            $return_data = [];
        }
        return response([
            'status' => 200,
            'message' => 'Done successfully',
            'data' => $this->format_performance_data($return_data->toArray()),
        ], 200);
    }
    public function downloadbystudent(Request $request)
    {
        $uuid_string = (string)Str::uuid() . '.pdf';
        $validator = Validator::make($request->all(), [
            'term' => 'required|string|not_in:nn',
            'student' => 'required|string',
        ]);
        if( $validator->fails() ){
            return response([
                'status' => 400,
                'message' => 'Select term and enter admission number',
                'errors' => $validator->errors()->all(),
            ], 400);
        }
        $input = $request->all();
        $student_m = Student::where('admission', $input['student'])->first();
        if(is_null($student_m))
        {
            return response([
                'status' => 400,
                'message' => 'No student was found with that admission number',
                'errors' => [],
            ], 400);
        }
        $return_data = [];
        if( !Auth::user()->is_super && !Auth::user()->is_admin )
        {
            $return_data = [];
        }
        $input['student'] = $student_m->id;
        $return_data = Performance::where('term', $input['term'])
            ->where('student', $student_m->id)->orderBy('id', 'desc')->get();
        if(is_null($return_data))
        {
            $return_data = [];
        }
        $return_data = $return_data->toArray();
        $f_meta = $this->find_stud_rpt_meta($input);
        $headers = $this->model_rpt_headers();
        $the_marks = $this->find_stud_sub_mark($input, $student_m->form);
        $chart_data = [$headers, $the_marks[2]];
        $this_term_perf = 0;
        if(count($the_marks[2]) == 3 )
        {
            $this_term_perf = $the_marks[2][2];
        }
        $_last_term_deviation = $this->find_stud_lst_trm_perf($input, $this_term_perf);
        $pdf_data = [
            'meta' => $f_meta,
            'last_perf' => $_last_term_deviation[0],
            'deviation' => $_last_term_deviation[1],
            'setup' => $this->find_setup(),
            'headers' => $headers,
            'marks' => $the_marks[0],
            'avr' => $the_marks[1],
            'scale' => $this->g_scale($student_m->form),
            'fees' => $this->find_std_fee_balances($input),
            'chart' => $this->make_chart($chart_data),
        ];
        $filename = ('app/cls/trt/content/' . $uuid_string);
        PDF::loadView('reports.studentreport', $pdf_data, [], ['orientation' => 'P', 'margin_top' => 2, 'margin_right' => 5, 'margin_left' => 5, 'margin_bottom' => 2])->save(storage_path($filename));
        return response([
            'status' => 200,
            'message' => 'report generated',
            'fileurl' => route('stream', ['file' => $uuid_string]),
            'errors' => [],
        ], 200);
    }
    protected function find_stud_lst_trm_perf($input, $thisterm)
    {
        $last_term_id = 0;
        $student = Student::find($input['student']);
        $this_term = Term::find($input['term']);
        $this_term_name = $this_term->label;
        $this_term_year = $this_term->year;
        $this_term_index = intval(substr($this_term_name, -1));
        if( $this_term_index == 1 )
        {
            $last_term_index = 3;
            $last_term_year = $this_term_year - 1;
            $last_term_name = 'TERM' . $last_term_index;
            $last_term_meta = Term::where('year', $last_term_year)
                ->where('label', $last_term_name)->first();
            if(is_null($last_term_meta))
            {
                return [0,0];
            }
            $last_term_id = $last_term_meta->id;

        }
        else
        {
            $last_term_index = $this_term_index - 1;
            $last_term_year = $this_term_year;
            $last_term_name = 'TERM' . $last_term_index;
            $last_term_meta = Term::where('year', $last_term_year)
                ->where('label', $last_term_name)->first();
            if(is_null($last_term_meta))
            {
                return [0,0];
            }
            $last_term_id = $last_term_meta->id;
        }
        $gn = Assessmentgroup::where('id', '!=', 0)->orderBy('id', 'asc')->get();
        $rtn = $gn->toArray();
        $data = [];
        foreach( $rtn as $r){
            array_push($data, $r['id']);
        }
        $last_term_final_assess = 0;
        if(count( $data ) == 3) 
        {
            $last_term_final_assess = $data[2];
        }
        $last_sum = Performance::where('student', $input['student'])
            ->where('group', $last_term_final_assess)
            ->where('term', $last_term_id)
            ->sum('mark');
        $last_count = Performance::where('student', $input['student'])
            ->where('group', $last_term_final_assess)
            ->where('term', $last_term_id)
            ->count();
        if( $last_sum == 0 )
        {
            return [0,0];
        }
        $last_avr = number_format(($last_sum/$last_count), 2);
        $last_perf_mark = $last_avr . ' ' . $this->extract_g_scale($last_avr, $student->form);
        $deviation = $last_avr - $thisterm;
        if( $deviation < 0)
        {
            return [$last_perf_mark, $deviation];
        }
        return [$last_perf_mark, '+' . $deviation];
    }
    protected function find_std_fee_balances($input)
    {
        $fee = Fee::where('student', $input['student'])
            ->where('term', $input['term'])->sum('fee');
        if($fee > 0 )
        {
             return $fee . ' (overpaid)';
        }
        return number_format(abs($fee), 0);
    }
    protected function find_stud_sub_mark($input, $stud_form)
    {
        $data = [];
        $groups = $this->assess_grp_ids();
        $subjects = [];
        $subject_id = Enrollment::select('subject')->where('student', $input['student'])->get();
        if(!is_null($subject_id))
        {
            $ids = $subject_id->toArray();
        }
        foreach($ids as $_sub ):
            $c_a = $c_b = $c_c = 0;
            $sub_name = Subject::find($_sub['subject'])->name;
            $per_grp_a = Performance::where('student', $input['student'])
                ->where('subject', $_sub['subject'])
                ->where('group', $groups[0])
                ->where('term', $input['term'])
                ->first();
            if(!is_null($per_grp_a))
            {
                $c_a = $per_grp_a->mark . '__' . $per_grp_a->grade;
            }
            $per_grp_b = Performance::where('student', $input['student'])
                ->where('subject', $_sub['subject'])
                ->where('group', $groups[1])
                ->where('term', $input['term'])
                ->first();
            if(!is_null($per_grp_b))
            {
                $c_b = $per_grp_b->mark . '__' . $per_grp_b->grade;
            }
            $per_grp_c = Performance::where('student', $input['student'])
                ->where('subject', $_sub['subject'])
                ->where('group', $groups[2])
                ->where('term', $input['term'])
                ->first();
            if(!is_null($per_grp_c))
            {
                $c_c = $per_grp_c->mark . '__' . $per_grp_c->grade;
            }
            $final = $sub_name . '~' . $c_a . '~' . $c_b . '~' . $c_c;
            array_push($data, $final);
        endforeach;
        /** avaeraged */
        $nof_sub = count($ids);
        $averages = [];
        $_chart_meta = [];
        foreach ( $groups as $grp ):
            $entry = '0__0__0';
            $chart_entry = 0;
            $sum = Performance::where('student', $input['student'])
                ->whereIn('subject', $ids)
                ->where('group', $grp)
                ->where('term', $input['term'])
                ->sum('mark');
            $sums_all = Performance::selectRaw('SUM(mark) as mk')
                // ->whereIn('subject', $ids)
                ->where('group', $grp)
                ->where('term', $input['term'])
                ->groupBy('student')->get();
            if($sum > 0 && !is_null($sums_all))
            {
                $sums_all = $sums_all->toArray();
                $position = $this->get_stud_frm_position($sums_all, $sum, $stud_form);
                $avr = number_format($sum/$nof_sub, 0);
                $grade = $this->extract_g_scale($avr, $stud_form);
                $entry = $avr . '__' . $grade . '__' . $position;
                $chart_entry = $avr;
            }
            array_push($averages, $entry);
            array_push($_chart_meta, $chart_entry);
        endforeach;
        return [$data, $averages, $_chart_meta];
    }
    protected function get_stud_frm_position($allmarks, $mysum, $stud_form)
    {
        $mks = [];
        $student_counts = Student::where('form', $stud_form)->count();
        foreach( $allmarks as $mark ):
            array_push($mks, $mark['mk']);
        endforeach;
        sort($mks);
        if(count($mks) == 1 && $mks[0] == $mysum)
        {
            return 'p: 1 out of 1';
        }
        $pos = intval(array_search($mysum, array_reverse($mks))) + 1;
        return 'p: ' .  $pos . ' out of ' . $student_counts;
    }
    protected function model_rpt_headers()
    {
        $gn = Assessmentgroup::where('id', '!=', 0)->orderBy('id', 'asc')->get();
        $rtn = $gn->toArray();
        $data = [];
        foreach( $rtn as $r){

            array_push($data, $r['name']);
        }
        // array_push($data, 'Average');
        return $data;
    }
    protected function assess_grp_ids()
    {
        $gn = Assessmentgroup::where('id', '!=', 0)->orderBy('id', 'asc')->get();
        $rtn = $gn->toArray();
        $data = [];
        foreach( $rtn as $r){
            array_push($data, $r['id']);
        }
        sort($data);
        return array_slice($data, 0, 3);
    }
    protected function find_stud_rpt_meta($input)
    {
        $meta = [
            'student' => null,
            'termname' => null,
            'average' => null,
            'grade' => null,
            'fullname' => null,
            'admission' => null,
            'flabel' => null,
            'slabel' => null,
            'cteacher' => null,
            'parentname' => null,
            'kcpe' => null,
            'sport' => null,
        ];
        $student_subject_en = [];
        $term = Term::find($input['term']);
        if(!is_null($term))
        {
            $meta['termname'] = $term->year . ' ' . ucwords(strtolower($term->label));
        }
        $student = Student::find($input['student']);
        if(!is_null($student))
        {
            $meta['student'] = ucwords(strtolower($student->fname . ' ' . $student->lname));
            $meta['fullname'] = ucwords(strtolower($student->fname . ' ' . $student->lname));
            $meta['admission'] = $student->admission;
            $meta['kcpe'] = $student->kcpe;
            $meta['sport'] = $this->find_stud_sports($student->id);
            $form = Form::find($student->form);
            if(!is_null($form))
            {
                $meta['flabel'] = $form->name;
            }
            $fstream = Formstream::find($student->stream);
            if(!is_null($fstream))
            {
                $meta['slabel'] = $fstream->form . $fstream->name;
                $classteacher = User::find($fstream->class_teacher);
                if( !is_null($classteacher) )
                {
                    $meta['cteacher'] = ucwords(strtolower($classteacher->fname . ' ' . $classteacher->lname));
                }
            }
            $parentmeta = User::find($student->parent);
            if( !is_null($parentmeta) )
            {
                $meta['parentname'] = ucwords(strtolower($parentmeta->fname . ' ' . $parentmeta->lname));
            }
        }
        return $meta;
    }
    protected function find_stud_sports($id)
    {
        $rtn = [];
        $data = Studentsport::where('student', $id)->get();
        if(!is_null($data) )
        {
            $data = $data->toArray();
            foreach($data as $d ):
                $sport = Sport::find($d['sport']);
                array_push($rtn, $sport->name);
            endforeach;
        }
        return implode(',', $rtn);
    }
    protected function extract_g_scale($mark, $form)
    {
        $scale = $this->g_scale($form, $mark);
        if(!$scale){
            return 'NA';
        }
        return $scale->grade;
    }
    protected function g_scale($form, $score)
    {
        return Scale::where('form', $form)
            ->where('min_mark', '<=', $score)
            ->where('max_mark', '>=', $score)
            ->first();
    }
    protected function has_current_trm()
    {
        $d = Term::where('is_current', true)->first();
        if( is_null($d) )
        {
            return false;
        }
        return true;
    }
    protected function find_current_trm()
    {
        $d = Term::where('is_current', true)->first();
        if( is_null($d) )
        {
            return 0;
        }
        return $d->id;
    }
    protected function find_performance_data()
    {
        if( !Auth::user()->is_super && !Auth::user()->is_admin )
        {
            return [];
        }

        $d = Performance::where('id', '!=', 0)->orderBy('id', 'desc')->get();
        if(is_null($d))
        {
            return [];
        }
        return $this->format_performance_data($d->toArray());
    }
    protected function find_setup()
    {
        $s = Setup::where('id', '!=', 0)->first();
        if(!is_null($s))
        {
            return $s->toArray();
        }
        return [
            'school' => null,
            'address' => null,
            'city' => null,
            'county' => null,
            'zip' => null,
            'email' => null,
            'phone' => null,
            'website' => null,
            'motto' => null,
            'logo' => null,
        ];
    }
    protected function format_performance_data($data)
    {
        $rtn = [];
        foreach( $data as $_data ):
            $term_meta = Term::find($_data['term']);
            if(!is_null( $term_meta ))
            {
                $_data['term_year'] = $term_meta->year . ' ' . $term_meta->label;
            }
            $stud_meta = Student::find($_data['student']);
            if(!is_null( $stud_meta ))
            {
                $_data['student_data'] = $stud_meta;
                $_data['level_data'] = Form::find($stud_meta->form);
                $_data['stream_data'] = Formstream::find($stud_meta->stream);
            }
            $_data['subject_data'] = Subject::find($_data['subject']);
            $assess_meta = Assessmentgroup::find($_data['group']);
            $_data['assessment_group_data'] = $assess_meta;
            array_push($rtn, $_data);
        endforeach;
        return $rtn;
    }
    protected function make_chart($input)
    {
        $name_of_graph = (string)Str::uuid() . '.png';
        $filename = ('app/cls/trt/content/' . $name_of_graph);
        $data = [];
        $loop = 0;
        foreach($input[0] as $label):
            array_push($data, [$label, $input[1][$loop]]);
            $loop++;
        endforeach;
        $plot = new PHPlot(200, 200);
        $plot->SetImageBorderType('plain');
        
        $plot->SetPlotType('bars');
        $plot->SetDataType('text-data');
        $plot->SetDataValues($data);
        
        # Main plot title:
        $plot->SetTitle('Performance Trend');
        
        # Make a legend for the 3 data sets plotted:
        // $plot->SetLegend(array('Engineering', 'Manufacturing', 'Administration'));
        
        # Turn off X tick labels and ticks because they don't apply here:
        $plot->SetXTickLabelPos('none');
        $plot->SetXTickPos('none');

        $plot->SetIsInline(true);
        $plot->SetOutputFile(storage_path($filename));
        $plot->DrawGraph();
        return $filename;
    }
}
