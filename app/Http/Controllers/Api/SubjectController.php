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

use App\Models\Subject;
use App\Models\Form;
use App\Models\Student;
use App\Models\Enrollment;
/** mail */
use Illuminate\Support\Facades\Mail;
use App\Mail\Welcome;
use App\Mail\Code;

class SubjectController extends Controller
{
    /**
     * @OA\Post(
     *     path="/pci/api/v1/subjects/add",
     *     tags={"Subjects"},
     *     summary="Add subject",
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function add(Request $request)
    {
        if( !Auth::user()->is_super )
        {
            return response([
                'status' => 400,
                'message' => 'Permission Denied. Only super admins allowed.',
                'errors' => [],
            ], 400);
        }
        try{
            $validator = Validator::make($request->all(), [
                'tution_fee' => 'required|decimal:2',
                'form' => 'required|string',
                'name' => 'required|string',
                'pass_mark' => 'required|string',
            ]);
            if( $validator->fails() ){
                return response([
                    'status' => 400,
                    'message' => 'Error: Invalid field(s) detected',
                    'errors' => $validator->errors()->all(),
                ], 400);
            }
            $input = $request->all();
            $input['label'] = ucwords(explode('form', strtolower($input['name']))[0]) . ' Form ' . $input['form'];
            Subject::create($input);
            return response([
                'status' => 200,
                'message' => 'Success. Done',
                'data' => $this->find_subjects_data(),
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

        /**
     * @OA\Post(
     *     path="/pci/api/v1/subjects/edit/{id}",
     *     tags={"Subjects"},
     *     summary="Edit subject",
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function edit(Request $request, $id)
    {
        if( !Auth::user()->is_super )
        {
            return response([
                'status' => 400,
                'message' => 'Permission Denied. Only super admins allowed.',
                'errors' => [],
            ], 400);
        }
        try{
            $validator = Validator::make($request->all(), [
                'tution_fee' => 'required|decimal:2',
                'form' => 'required|string',
                'name' => 'required|string',
                'pass_mark' => 'required|string',
            ]);
            if( $validator->fails() ){
                return response([
                    'status' => 400,
                    'message' => 'Error: Invalid field(s) detected',
                    'errors' => $validator->errors()->all(),
                ], 400);
            }
            $input = $request->all();
            $input['label'] = ucwords(explode('form', strtolower($input['name']))[0]) . ' Form ' . $input['form'];
            Subject::find($id)->update($input);
            return response([
                'status' => 200,
                'message' => 'Success. Information updated',
                'data' => $this->find_subjects_data(),
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

        /**
     * @OA\Post(
     *     path="/pci/api/v1/subjects/drop/{id}",
     *     tags={"Subjects"},
     *     summary="Drop subject",
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function drop($id)
    {
        // Subject::find($id)->delete();
        return response([
            'status' => 400,
            'message' => "Subject deletion error. Permission denied",
            'errors' => [],
        ], 400);
    }

    /**
     * @OA\Post(
     *     path="/pci/api/v1/subjects/unenroll/all/{id}",
     *     tags={"Subjects"},
     *     summary="Unenroll all enrollments for this subject",
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function unenroll_all($id)
    {
        if(!Enrollment::where('subject', $id)->where('status', 'enrolled')->count())
        {
            return response([
                'status' => 400,
                'message' => "No enrollments found for the selected subject",
                'data' => [],
            ], 400);
        }
        Enrollment::where('subject', $id)->where('status', 'enrolled')->delete();
        return response([
            'status' => 200,
            'message' => "All enrollments have been dropped",
            'data' => [],
        ], 200);
    }

     /**
     * @OA\Get(
     *     path="/pci/api/v1/subjects/findall",
     *     tags={"Subjects"},
     *     summary="List all subjects",
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function findall()
    {
        return response([
            'status' => 200,
            'message' => "Done successfully",
            'data' => $this->find_subjects_data(),
        ], 200);
    }

      /**
     * @OA\Get(
     *     path="/pci/api/v1/subjects/by/student/{id}",
     *     tags={"Subjects"},
     *     summary="List all subjects for a student",
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function bystudent($student)
    {
        return response([
            'status' => 200,
            'message' => "Done successfully",
            'data' => $this->find_student_subjects_data($student),
        ], 200);
    }
    protected function find_student_subjects_data($student)
    {
        $smeta = Student::find($student);
        if(is_null($smeta)){ return []; }
        $subjects = Subject::where('form', $smeta->form)->orderBy('id', 'desc')->get();
        if(is_null($subjects))
        {
            return [];
        }
        return $subjects->toArray();
    }

        /**
     * @OA\Get(
     *     path="/pci/api/v1/subjects/find/{id}",
     *     tags={"Subjects"},
     *     summary="Find a single subject",
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function find($id)
    {
        $data = Subject::find($id);
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
    protected function find_subjects_data()
    {
        $d = Subject::where('id', '!=', 0)->orderBy('id', 'desc')->get();
        if(is_null($d))
        {
            return [];
        }
        return $this->format_subject_data($d->toArray());
    }
    protected function format_subject_data($data)
    {
        $rtn = [];
        foreach( $data as $_data ):
            $frm_meta = Form::find($_data['form']);
            if(!is_null($frm_meta))
            {
                $_data['flabel'] = $frm_meta->name;
            }
            array_push($rtn, $_data);
        endforeach;
        return $rtn;
    }
}
