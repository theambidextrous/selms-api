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

use App\Models\Enrollment;
use App\Models\Student;
use App\Models\Term;
use App\Models\Subject;
use App\Models\Form;
use App\Models\Fee;
/** mail */
use Illuminate\Support\Facades\Mail;
use App\Mail\Welcome;
use App\Mail\Code;

class EnrollmentController extends Controller
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
                'subject' => 'required|string',
                'student' => 'required|string',
                'status' => 'required|string',
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
            $input['year'] = $this->find_current_trm_yr();
            $student_meta = Student::where('admission', trim($input['student']))->first();
            if(is_null($student_meta))
            {
                return response([
                    'status' => 400,
                    'message' => 'Student not found. Try gain',
                    'data' => [],
                ], 400);
            }
            $input['student'] = $student_meta->id;
            if( !$this->is_enrollable_stud_sub($student_meta->form, $input['subject']) )
            {
                return response([
                    'status' => 400,
                    'message' => 'Enrollment failed. Make sure you are selecting the correct subject for the student',
                    'data' => [],
                ], 400);
            }
            $alreadyEnrolled = Enrollment::where('student', $student_meta->id)->where('subject', $input['subject'])->exists();
            if($alreadyEnrolled){
                return response([
                    'status' => 400,
                    'message' => 'Enrollment failed. Student already enrolled',
                    'data' => [],
                ], 400);
            }

            Enrollment::create($input);
            $_subject = Subject::find($input['subject']);
            $term_data = $this->find_current_term();
            $fee_meta = [
                'term' => $term_data->id,
                'narration' => 'Tution fees for ' . $_subject->name, 
                'student' => $student_meta->id,
                'fee' => $_subject->tution_fee,
                'subject' => $_subject->id,
            ];
            Fee::create($fee_meta);
            return response([
                'status' => 200,
                'message' => 'Success. Done',
                'data' => $this->find_enrollments_data(),
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
    public function unenroll(Request $request)
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
                'subject' => 'required|string',
                'student' => 'required|string',
            ]);
            if( $validator->fails() ){
                return response([
                    'status' => 400,
                    'message' => 'A required field was not found',
                    'errors' => $validator->errors()->all(),
                ], 400);
            }
            $input = $request->all();
            $student_meta = Student::where('admission', trim($input['student']))->first();
            if(is_null($student_meta))
            {
                return response([
                    'status' => 400,
                    'message' => 'Student not found. Try gain',
                    'data' => [],
                ], 400);
            }
            $input['student'] = $student_meta->id;
            Enrollment::where('student', $input['student'])
                ->where('subject', $input['subject'])->delete();

            Fee::where('subject', $input['subject'])
                ->where('student', $input['student'])
                ->where('cleared', 0)
                ->where('type', 'Tution')
                ->delete();
                
            return response([
                'status' => 200,
                'message' => 'Success. Done',
                'data' => [],
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
                // 'year' => 'required|string',
                'subject' => 'required|string',
                'student' => 'required|string',
                'status' => 'required|string',
            ]);
            if( $validator->fails() ){
                return response([
                    'status' => 400,
                    'message' => 'A required field was not found',
                    'errors' => $validator->errors()->all(),
                ], 400);
            }
            $input = $request->all();
            $input['year'] = $this->find_current_trm_yr();
            $student_meta = Student::where('admission', trim($input['student']))->first();
            $input['student'] = $student_meta->id;
            Enrollment::find($id)->update($input);
            return response([
                'status' => 200,
                'message' => 'Success. Information updated',
                'data' => $this->find_enrollments_data(),
            ], 200);
        } catch (\Illuminate\Database\QueryException $e) {
            return response([
                'status' => 400,
                'message' => "Server error. Invalid data",
                'errors' => ['error' => $e->getMessage()],
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
        Enrollment::find($id)->delete();
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
            'data' => $this->find_enrollments_data(),
        ], 200);
    }
    public function searchall(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'year' => 'required|string|not_in:nn',
            'subject' => 'required|string|not_in:nn',
            'estatus' => 'required|string|not_in:nn',
        ]);
        if( $validator->fails() ){
            return response([
                'status' => 400,
                'message' => 'Please select year, subject and status',
                'errors' => $validator->errors()->all(),
            ], 400);
        }
        $input = $request->all();
        $data = Enrollment::where('year', $input['year'])
            ->where('subject', $input['subject'])
            ->where('status', $input['estatus'])
            ->get();
        if(is_null( $data ))
        {
            return response([
                'status' => 200,
                'message' => "Done successfully. No records found",
                'data' => [],
            ], 200);
        }
        return response([
            'status' => 200,
            'message' => "Done successfully",
            'data' => $this->format_enrollments_data($data->toArray()),
        ], 200);
    }
    public function find($id)
    {
        $data = Enrollment::find($id);
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
    protected function find_enrollments_data()
    {
        $d = Enrollment::where('id', '!=', 0)->orderBy('id', 'desc')->get();
        if(is_null($d))
        {
            return [];
        }
        return $this->format_enrollments_data($d->toArray());
    }
    protected function format_enrollments_data($data)
    {
        $rtn = [];
        foreach( $data as $_data ):
            $p_meta = Student::find($_data['student']);
            if(!is_null($p_meta))
            {
                $_data['student_label'] = $p_meta;

            }
            $_data['enrollment_date_label'] = date('m/d/Y', strtotime($_data['created_at']));
            $_data['subject_label'] = Subject::find($_data['subject']);
            $_data['form_label'] = Form::find($p_meta->form);
            array_push($rtn, $_data);
        endforeach;
        return $rtn;
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
    protected function find_current_trm_yr()
    {
        return $this->find_current_term()->year;
    }

    protected function find_current_term()
    {
        $d = Term::where('is_current', true)->first();
        if( is_null($d) )
        {
            throw new \Exception("No current term set");
        }
        return $d;
    }

    protected function is_enrollable_stud_sub($stud_form, $subject)
    {
        $sub_form = Subject::find($subject)->form;
        if( $stud_form == $sub_form )
        {
            return true;
        }
        return false;
    }
}
