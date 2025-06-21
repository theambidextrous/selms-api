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

use App\Models\Student;
use App\Models\Enrollment;
use App\Models\Subject;
use App\Models\Term;
use App\Models\Fee;
use App\Models\User;
use App\Models\Form;
use App\Models\Formstream;
/** mail */
use Illuminate\Support\Facades\Mail;
use App\Mail\Welcome;
use App\Mail\Code;


class StudentController extends Controller
{
    public function add(Request $request)
    {
        $file_uuid = (string) Str::uuid();
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
                'admission' => 'required|string',
                'date_of_admission' => 'required|string',
                'fname' => 'required|string',
                'lname' => 'required|string',
                'address' => 'required|string',
                'city' => 'required|string',
                'county' => 'required|string',
                'zip' => 'required|string',
                // 'parent' => 'required|string',
                'form' => 'required|string|not_in:nn',
                'stream' => 'required|string|not_in:nn',
                'expected_grad' => 'required|string',
                'gender' => 'required|string|not_in:nn',
                'dob' => 'required|string',
                'birth_cert' => 'required|string',
                'kcpe' => 'required|string',
                // 'huduma_no' => 'required|string',
            ]);
            if( $validator->fails() ){
                return response([
                    'status' => 400,
                    'message' => 'A required field was not found',
                    'errors' => $validator->errors()->all(),
                ], 400);
            }
            $input = $request->all();
            if( !$this->has_current_trm() )
            {
                return response([
                    'status' => 400,
                    'message' => 'Current term not set',
                    'data' => [],
                ], 400);
            }
            $input['current_term'] = $this->find_current_trm();
            if( $request->hasfile('pic') )
            {
                $file_content = $request->file('pic');
                $exten = strtolower($file_content->getClientOriginalExtension());
                if( !in_array($exten, ['png','jpg']) )
                {
                    return response([
                        'status' => 400,
                        'message' => 'Invalid image type. Use png or JPG files',
                        'data' => [],
                    ], 400);
                }
                $file_content_name = $file_uuid . '.' . $exten;
                Storage::disk('local')
                    ->putFileAs('cls/trt/content', $file_content, $file_content_name);
                $input['pic'] = $file_content_name;
            }else
            {
                unset($input['pic']);
            }
            $user = Student::create($input)->id;
            $this->enroll_default_subjects($user, $input['current_term'],  $input['form']);
            $this->create_default_fee($user, $input['current_term'], $input['form']);
            return response([
                'status' => 200,
                'message' => 'Success. Account created',
                'data' => $this->find_stud_data(),
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
        $file_uuid = (string) Str::uuid();
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
                'admission' => 'required|string',
                'date_of_admission' => 'required|string',
                'fname' => 'required|string',
                'lname' => 'required|string',
                'address' => 'required|string',
                'city' => 'required|string',
                'county' => 'required|string',
                'zip' => 'required|string',
                // 'parent' => 'required|string',
                'form' => 'required|string|not_in:nn',
                'stream' => 'required|string|not_in:nn',
                'expected_grad' => 'required|string',
                'gender' => 'required|string|not_in:nn',
                'dob' => 'required|string',
                'birth_cert' => 'required|string',
                'kcpe' => 'required|string',
                // 'nemis_no' => 'required|string',
                // 'huduma_no' => 'required|string',
            ]);
            if( $validator->fails() ){
                return response([
                    'status' => 400,
                    'message' => 'A required field was not found',
                    'errors' => $validator->errors()->all(),
                ], 400);
            }
            $input = $request->all();
            if( !$this->has_current_trm() )
            {
                return response([
                    'status' => 400,
                    'message' => 'Current term not set',
                    'data' => [],
                ], 400);
            }
            $input['current_term'] = $this->find_current_trm();
            if( $request->hasfile('pic') )
            {
                $file_content = $request->file('pic');
                $exten = strtolower($file_content->getClientOriginalExtension());
                if( !in_array($exten, ['png','jpg']) )
                {
                    return response([
                        'status' => 400,
                        'message' => 'Invalid image type. Use png or JPG files',
                        'data' => [],
                    ], 400);
                }
                $file_content_name = $file_uuid . '.' . $exten;
                Storage::disk('local')
                    ->putFileAs('cls/trt/content', $file_content, $file_content_name);
                $input['pic'] = $file_content_name;
            }
            if(!strlen($input['parent']))
            {
                unset($input['parent']);
            }
            Student::find($id)->update($input);
            return response([
                'status' => 200,
                'message' => 'Success. Information updated',
                'data' => $this->find_stud_data(),
            ], 200);
        } catch (\Illuminate\Database\QueryException $e) {
            return response([
                'status' => 400,
                'message' => "Server error. Invalid data",
                'errors' => [],
            ], 400);
        } catch (PDOException $e) {
            return response([
                'status' => 400,
                'message' => "Db error. Invalid data",
                'errors' => [],
            ], 400);
        }
    }
    public function drop($id)
    {
        Student::find($id)->update([ 'is_active' => false ]);
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
            'data' => $this->find_stud_data(),
        ], 200);
    }
    public function searchall()
    {
        return response([
            'status' => 200,
            'message' => "Search results...",
            'subjects' => [],
            'performance' => [],
            'fees' => [],
            'hasbooks' => [],
            'lostbooks' => [],
            'displinery' => [],
        ], 200);
    }
    public function find($id)
    {
        $data = Student::find($id);
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
    public function rolloveradm(Request $request)
    {
        if( !Auth::user()->is_super && !Auth::user()->is_admin )
        {
            return response([
                'status' => 400,
                'message' => 'Permission Denied. Only super admins allowed.',
                'errors' => [],
            ], 400);
        }
        $validator = Validator::make($request->all(), [
            'adm' => 'required|string',
        ]);
        if( $validator->fails() ){
            return response([
                'status' => 400,
                'message' => 'Admission number is required',
                'errors' => $validator->errors()->all(),
            ], 400);
        }
        $input = $request->all();
        return response([
            'status' => 200,
            'message' => 'Process completed ...',
            'data' => [],
        ], 200);
    }
    public function rolloverform(Request $request)
    {
        if( !Auth::user()->is_super && !Auth::user()->is_admin )
        {
            return response([
                'status' => 400,
                'message' => 'Permission Denied. Only super admins allowed.',
                'errors' => [],
            ], 400);
        }
        $validator = Validator::make($request->all(), [
            'form' => 'required|string',
        ]);
        if( $validator->fails() ){
            return response([
                'status' => 400,
                'message' => 'Form field is required',
                'errors' => $validator->errors()->all(),
            ], 400);
        }
        $input = $request->all();
        return response([
            'status' => 200,
            'message' => 'Process completed ...',
            'data' => [],
        ], 200);
    }
    protected function has_current_trm()
    {
        $d = Term::where('is_current', true)->count();
        if( $d )
        {
            return true;
        }
        return false;
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
    protected function create_default_fee($stud, $term, $form)
    {
        $d = Term::find($term);
        $fee_meta = [
            'term' => $term,
            'narration' => 'School fees for ' . $d->label . ' of ' . $d->year, 
            'student' => $stud,
            'fee' => -(intval($this->extract_fee($d, $form))),
        ];
        Fee::create($fee_meta);
        return true;
    }
    protected function enroll_default_subjects($stud, $term, $form)
    {
        $d = Term::find($term);
        $year = $d->year;
        $subjects = $this->find_form_subjects($form);
        foreach( $subjects as $subject ):
            Enrollment::create([
                'year' => $year,
                'subject' => $subject['id'],
                'student' => $stud,
            ]);
        endforeach;
    }
    protected function find_form_subjects($form)
    {
        $d = Subject::where('form', $form)->get();
        if( is_null($d) ) { return []; }
        return $d->toArray();
    }
    protected function extract_fee($d, $form)
    {
        if(intval($form) == 1) { return $d->f1_fee; }
        if(intval($form) == 2) { return $d->f2_fee; }
        if(intval($form) == 3) { return $d->f3_fee; }
        if(intval($form) == 4) { return $d->f4_fee; }

        return 0;
    }
    protected function find_stud_data()
    {
        $d = Student::where('is_active', true)->orderBy('id', 'desc')->get();
        if(is_null($d))
        {
            return [];
        }
        return $this->format_stud_data($d->toArray());
    }
    protected function format_stud_data($data)
    {
        $rtn = [];
        foreach( $data as $_data ):
            $p_meta = User::find($_data['parent']);
            if(!is_null( $p_meta ))
            {
                $_data['plabel'] = $p_meta->fname . ' ' . $p_meta->lname;
            }
            $f_meta = Form::find($_data['form']);
            if(!is_null( $f_meta ))
            {
                $_data['flabel'] = $f_meta->name;
            }
            $s_meta = Formstream::find($_data['stream']);
            if(!is_null( $s_meta ))
            {
                $_data['slabel'] = $s_meta->form.$s_meta->name;
            }
            if(!is_null($_data['pic']))
            {
                $_data['pic'] = route('stream', ['file' => $_data['pic']]);
            }
            array_push($rtn, $_data);
        endforeach;
        return $rtn;
    }
}
