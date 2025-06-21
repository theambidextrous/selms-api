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

use App\Models\User;
use App\Models\Setup;
use App\Models\Timetable;
use App\Models\Subject;
/** mail */
use Illuminate\Support\Facades\Mail;
use App\Mail\Welcome;
use App\Mail\Code;

class TeacherController extends Controller
{
    /**
     * @OA\Post(
     *     path="/pci/api/v1/teachers/add",
     *     tags={"Teachers"},
     *     summary="Add school teacher",
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
                'fname' => 'required|string',
                'lname' => 'required|string',
                'address' => 'required|string',
                'city' => 'required|string',
                'county' => 'required|string',
                'zip' => 'required|string',
                'email' => 'required|email',
                'phone' => 'required|string',
                'password' => 'required|string',
                'c_password' => 'required|string|same:password',
            ]);
            if( $validator->fails() ){
                return response([
                    'status' => 400,
                    'message' => 'A required field was not found',
                    'errors' => $validator->errors()->all(),
                ], 400);
            }
            $input = $request->all();
            $input['is_super'] = false;
            $input['is_admin'] = false;
            $input['is_lib'] = false;
            $input['is_fin'] = false;
            $input['is_teacher'] = true;
            $input['is_parent'] = false;
            if( User::where('email', $input['email'])->count() )
            {
                return response([
                    'status' => 400,
                    'message' => "Email address already used",
                    'errors' => [],
                ], 400);
            }
            $input['password'] = Hash::make($input['password']);
            $input['phone'] = $this->format_phone($input['phone']);
            if( User::where('phone', $input['phone'])->count() )
            {
                return response([
                    'status' => 400,
                    'message' => "Phone number already used",
                    'errors' => [],
                ], 400);
            }
            User::create($input);
            return response([
                'status' => 200,
                'message' => 'Success. Account created',
                'data' => $this->find_teacher_data(),
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
     *     path="/pci/api/v1/teachers/edit/{id}",
     *     tags={"Teachers"},
     *     summary="Edit school teacher",
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
                'fname' => 'required|string',
                'lname' => 'required|string',
                'address' => 'required|string',
                'city' => 'required|string',
                'county' => 'required|string',
                'zip' => 'required|string',
                'email' => 'required|email',
                'phone' => 'required|string',
            ]);
            if( $validator->fails() ){
                return response([
                    'status' => 400,
                    'message' => 'A required field was not found',
                    'errors' => $validator->errors()->all(),
                ], 400);
            }
            $input = $request->all();
            $input['is_super'] = false;
            $input['is_admin'] = false;
            $input['is_lib'] = false;
            $input['is_fin'] = false;
            $input['is_teacher'] = true;
            $input['is_parent'] = false;
            $input['phone'] = $this->format_phone($input['phone']);
            if(!strlen($input['password']))
            {
                unset($input['password']);
            }
            else
            {
                $input['password'] = Hash::make($input['password']);
            }
            User::find($id)->update($input);
            return response([
                'status' => 200,
                'message' => 'Success. Information updated',
                'data' => $this->find_teacher_data(),
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
     *     path="/pci/api/v1/teachers/drop/{id}",
     *     tags={"Teachers"},
     *     summary="Drop school teacher",
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function drop($id)
    {
        User::find($id)->update([ 'is_active' => false ]);
        return response([
            'status' => 200,
            'message' => "Done successfully",
            'errors' => [],
        ], 200);
    }
    
      /**
     * @OA\Get(
     *     path="/pci/api/v1/teachers/findall",
     *     tags={"Teachers"},
     *     summary="List all school teachers",
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function findall(Request $request)
    {
        return response([
            'status' => 200,
            'message' => "Done successfully",
            'data' => $this->find_teacher_data(),
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/pci/api/v1/teachers/find/{id}",
     *     tags={"Teachers"},
     *     summary="Fetch single s`chool teacher entity",
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function find($id)
    {
        $data = User::find($id);
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

        /**
     * @OA\Post(
     *     path="/pci/api/v1/teachers/teacher/subjects",
     *     tags={"Teachers"},
     *     summary="List all teacher subjects",
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function t_subject(Request $request)
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
                'teacher' => 'required|string|not_in:nn',
            ]);
            if( $validator->fails() ){
                return response([
                    'status' => 400,
                    'message' => 'Select a teacher.',
                    'errors' => $validator->errors()->all(),
                ], 400);
            }
            $teacher = $request->get('teacher');            
            $data_ = Timetable::select('subject')->where('teacher', $teacher)->get();
            if(is_null($data_))
            {
                $data_ = [];
            }else{
                $data_ = $data_->toArray();
            }
            if( count( $data_ ) )
            {
                $subjects_t = Subject::whereIn('id', $data_)->get();
                if(is_null($subjects_t))
                {
                    $subjects_t = [];
                }
                else
                {
                    $subjects_t = $subjects_t->toArray();
                }
                return response([
                    'status' => 200,
                    'message' => 'Success. Information updated',
                    'data' => $subjects_t,
                ], 200);
            }
            return response([
                'status' => 200,
                'message' => 'No records found',
                'data' => [],
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
    protected function format_phone($phone)
    {
        return '254' . substr($phone, -9);
    }
    protected function find_teacher_data()
    {
        $d = User::where('is_teacher', true)->where('is_active', true)
        ->orderBy('id', 'desc')->get();

        if(is_null($d))
        {
            return [];
        }
        return $d->toArray();
    }
}
