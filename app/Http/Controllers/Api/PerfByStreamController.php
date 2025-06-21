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
/** mail */
use Illuminate\Support\Facades\Mail;
use App\Mail\Welcome;
use App\Mail\Code;

$file_uuid = (string) Str::uuid();

class PerfByStreamController extends Controller
{
    public function findbystream(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'stream' => 'required|string|not_in:nn',
            'term' => 'required|string|not_in:nn',
            'group' => 'required|string|not_in:nn',
        ]);
        if( $validator->fails() ){
            return response([
                'status' => 400,
                'message' => 'Form error. Select term and stream',
                'errors' => $validator->errors()->all(),
            ], 400);
        }
        if( !Auth::user()->is_super && !Auth::user()->is_admin )
        {
            return response([
                'status' => 200,
                'message' => 'No records found. Permission denied',
                'data' => [],
            ], 400);
        }
        $input = $request->all();
        $ids = Student::select('id')->where('stream', $input['stream'])->get();
        if( is_null($ids) )
        {
            return response([
                'status' => 200,
                'message' => 'No records found.',
                'data' => [],
            ], 200);
        }
        $data = Performance::where('term', $input['term'])
            ->whereIn('student', $ids->toArray())
            ->where('group', $input['group'])
            ->orderBy('subject', 'asc')
            ->get();
        if( is_null($data) )
        {
            return response([
                'status' => 200,
                'message' => 'No records found.',
                'data' => [],
            ], 200);
        }
        $data = $data->toArray();

        return response([
            'status' => 200,
            'message' => 'records found.',
            'data' => $this->format_performance_data($data),
        ], 200);
        
    }
    public function downloadlist(Request $request)
    {
        $uuid_string = (string)Str::uuid() . '.pdf';
        $validator = Validator::make($request->all(), [
            'stream' => 'required|string|not_in:nn',
            'term' => 'required|string|not_in:nn',
            'group' => 'required|string|not_in:nn',
        ]);
        if( $validator->fails() ){
            return response([
                'status' => 400,
                'message' => 'Error. Missing info',
                'errors' => $validator->errors()->all(),
            ], 400);
        }
        $input = $request->all();
        if( !Auth::user()->is_super && !Auth::user()->is_admin )
        {
            return response([
                'status' => 400,
                'message' => 'Error. Permission denied',
                'errors' => [],
            ], 400);
        }
        $stream_meta = Formstream::find($input['stream']);
        $term_meta = Term::find($input['term']);
        $teacher_meta = User::find($stream_meta->class_teacher);
        $enroll_count = Student::where('stream', $input['stream'])->count();
        $the_assess = Assessmentgroup::find($input['group']);
        $stream_subjects = [];
        $stream_stud_ids = Student::select('id')->where('stream', $input['stream'])->get();
        if(!is_null($stream_stud_ids))
        {
            $stream_stud_ids = $stream_stud_ids->toArray();
            $stream_sub_ids = Enrollment::select('subject')->whereIn('student', $stream_stud_ids)
                ->where('status', 'enrolled')
                ->where('year', $term_meta->year)
                ->get();
            if(!is_null($stream_sub_ids))
            {
                $stream_sub_ids = $this->filter_dup_ids($stream_sub_ids->toArray());
                foreach( $stream_sub_ids as $one ):
                    $meta_one = Subject::find($one);
                    $entry = [
                        'id' => $one,
                        'title' => $meta_one->name . ' Performance',
                        'data' => [],
                    ];
                    array_push($stream_subjects, $entry);
                endforeach;
            }
        }
        $f_meta = [
            'stream' => $stream_meta->form . $stream_meta->name,
            'flabel' => Form::find($stream_meta->form)->name,
            'cat' => $the_assess->name,
            'count' => $enroll_count,
            'cteacher' => $teacher_meta->fname . ' ' . $teacher_meta->lname,
            'termname' => $term_meta->year . ' ' . $term_meta->label,
        ];
        $pdf_data = [
            'meta' => $f_meta,
            'setup' => $this->find_setup(),
            'marks' => $this->get_sub_str_Scores($stream_subjects, $input, $stream_stud_ids),
        ];
        $filename = ('app/cls/trt/content/' . $uuid_string);
        PDF::loadView('reports.streammarklist', $pdf_data, [], ['orientation' => 'L', 'margin_top' => 2, 'margin_right' => 5, 'margin_left' => 5, 'margin_bottom' => 2])->save(storage_path($filename));
        return response([
            'status' => 200,
            'message' => 'report generated',
            'fileurl' => route('stream', ['file' => $uuid_string]),
            'errors' => [],
        ], 200);
    }
    public function downloadreports(Request $request)
    {
        $uuid_string = (string)Str::uuid() . '.pdf';
        $validator = Validator::make($request->all(), [
            'stream' => 'required|string|not_in:nn',
            'term' => 'required|string|not_in:nn',
            'group' => 'required|string|not_in:nn',
        ]);
        if( $validator->fails() ){
            return response([
                'status' => 400,
                'message' => 'Error. Missing info',
                'errors' => $validator->errors()->all(),
            ], 400);
        }
        if( !Auth::user()->is_super && !Auth::user()->is_admin )
        {
            return response([
                'status' => 400,
                'message' => 'Error. Permission denied',
                'errors' => [],
            ], 400);
        }
        $input = $request->all();
        $allstudents = Student::where('stream', $input['stream'])->get();
        if(is_null($allstudents))
        {
            $allstudents = [];
        }else
        {
            $allstudents = $allstudents->toArray();
        }
        $bulk_pdf_data = [];
        foreach( $allstudents as $as ):
            $input['student'] = $as['id'];
            $return_data = Performance::where('term', $input['term'])
            ->where('student', $as['id'])->orderBy('id', 'desc')->get();
            if(is_null($return_data))
            {
                $return_data = [];
            }
            $return_data = $return_data->toArray();
            $f_meta = $this->find_stud_rpt_meta($input);
            $headers = $this->model_rpt_headers();
            $the_marks = $this->find_stud_sub_mark($input, $as['form']);
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
                'scale' => $this->g_scale($as['form']),
                'fees' => $this->find_std_fee_balances($input),
                'chart' => $this->make_chart($chart_data),
            ];
            array_push($bulk_pdf_data, $pdf_data);
        endforeach;
        $bulk_pdfdata = [
            'bulk_pdfdata' => $bulk_pdf_data,
        ];
        $filename = ('app/cls/trt/content/' . $uuid_string);
        $pdf = PDF::loadView('reports.studentreportbulk', $bulk_pdfdata, [], ['orientation' => 'P', 'margin_top' => 2, 'margin_right' => 5, 'margin_left' => 5, 'margin_bottom' => 2])->save(storage_path($filename));

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
    protected function get_sub_str_Scores($stream_subjects, $input, $stream_stud_ids)
    {
        $data = [];
        foreach( $stream_subjects as $sb):
            $scores = Performance::where('term', $input['term'])
                ->where('group', $input['group'])
                ->where('subject', $sb['id'])
                ->whereIn('student', $stream_stud_ids)
                ->orderBy('mark', 'desc')
                ->get();
            if(!is_null($scores))
            {
                $scores = $scores->toArray();
                $summ = [];
                $one_data = [];
                $st_form = 0;
                foreach( $scores as $sc){
                    array_push($summ, $sc['mark']);
                    $student_m = Student::find($sc['student']);
                    $st_form = $student_m->form;
                    $entry = [
                        'admission' => $student_m->admission,
                        'fullname' => $student_m->fname . ' ' . $student_m->lname,
                        'score' => $sc['mark'],
                        'grade' => $sc['grade'],
                        'remark' => $sc['remark'],
                        'sum' => array_sum($summ),
                    ];
                    array_push($one_data, $entry);
                }
                $sb['data'] = $one_data;
                $sb['mean'] = number_format( array_sum($summ)/count($scores), 2);
                $sb['meangrade'] = $this->extract_g_scale($sb['mean'], $st_form);
                array_push($data, $sb);
            }
        endforeach;
        return $data;
    }
    protected function filter_dup_ids($data)
    {
        $rtn = [];
        foreach( $data as $_data ){
            array_push($rtn, $_data['subject']);
        }
        return array_unique($rtn);
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
    protected function get_stud_frm_position($allmarks, $mysum)
    {
        $mks = [];
        foreach( $allmarks as $mark ):
            array_push($mks, $mark['mk']);
        endforeach;
        sort($mks);
        if(count($mks) == 1 && $mks[0] == $mysum)
        {
            return 'pos : 1/1';
        }
        $pos = intval(array_search($mysum, array_reverse($mks))) + 1;
        return 'pos : ' .  $pos . '/' . count($mks);
    }
    protected function model_rpt_headers()
    {
        $gn = Assessmentgroup::where('id', '!=', 0)->orderBy('id', 'asc')->get();
        $rtn = $gn->toArray();
        $data = [];
        foreach( $rtn as $r){

            array_push($data, $r['name']);
        }
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
        $g = $this->g_scale($form);
        $mark = number_format($mark, 0);
        $g = array_combine($g[0], $g[1]);
        foreach( $g as $k => $v )
        {
            $s = explode('-', $v);
            if($mark >= $s[0] &&  $mark <= $s[1] )
            {
                return $k;
            }
        }
        return 'n/a';
    }
    protected function g_scale($form)
    {
        $scales = Scale::where('form', $form)->orderBy('id', 'desc')->get();
        if(is_null($scales))
        {
            return [];
        }
        $scales = $scales->toArray();
        $m = $g = [];
        foreach( $scales as $scale ):
            array_push($m, $scale['mark']);
            array_push($g, $scale['grade']);
        endforeach;
        return [array_reverse($g), array_reverse($m)];
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
                $_data['ylabel'] = $term_meta->year . ' ' . $term_meta->label;
            }
            $stud_meta = Student::find($_data['student']);
            if(!is_null( $stud_meta ))
            {
                $_data['slabel'] = $stud_meta->fname . ' ' . $stud_meta->lname;
                $_data['admlabel'] = $stud_meta->admission;
                $_data['flabel'] = 'Form ' . $stud_meta->form;
                $stream_meta = Formstream::find($stud_meta->stream);
                if(!is_null($stream_meta))
                {
                    $_data['streamlabel'] = $stream_meta->form . $stream_meta->name;
                }
            }
            $subject_meta = Subject::find($_data['subject']);
            if(!is_null($subject_meta))
            {
                $_data['sublabel'] = $subject_meta->name;
            }
            $assess_meta = Assessmentgroup::find($_data['group']);
            if(!is_null($assess_meta))
            {
                $_data['alabel'] = $assess_meta->name;
            }
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
