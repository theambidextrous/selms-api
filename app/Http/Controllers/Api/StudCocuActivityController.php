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

use App\Models\Studentsport;
use App\Models\User;
use App\Models\Student;
use App\Models\Sport;
/** mail */
use Illuminate\Support\Facades\Mail;
use App\Mail\Welcome;
use App\Mail\Code;


class StudCocuActivityController extends Controller
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
                'student' => 'required|string',
                'sport' => 'required|string|not_in:nn',
                'achievement' => 'required|string',
            ]);
            if( $validator->fails() ){
                return response([
                    'status' => 400,
                    'message' => 'Error: Invalid field(s) detected',
                    'errors' => $validator->errors()->all(),
                ], 400);
            }
            $input = $request->all();
            $student_meta = Student::where('admission', $input['student'])->first();
            if(is_null($student_meta))
            {
                return response([
                    'status' => 400,
                    'message' => 'No student found for admission number below',
                    'errors' => [],
                ], 400);
            }
            $input['student'] = $student_meta->id;
            Studentsport::create($input);
            return response([
                'status' => 200,
                'message' => 'Success. Item created',
                'data' => $this->find_stud_activ_data(),
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
                // 'student' => 'required|string|not_in:nn',
                'sport' => 'required|string|not_in:nn',
                'achievement' => 'required|string',
            ]);
            if( $validator->fails() ){
                return response([
                    'status' => 400,
                    'message' => 'Error: Invalid field(s) detected',
                    'errors' => $validator->errors()->all(),
                ], 400);
            }
            $input = $request->all();
            Studentsport::find($id)->update($input);
            return response([
                'status' => 200,
                'message' => 'Success. Information updated',
                'data' => $this->find_stud_activ_data(),
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
        Studentsport::find($id)->delete();
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
            'data' => $this->find_stud_activ_data(),
        ], 200);
    }
    public function find($id)
    {
        $data = Studentsport::find($id);
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
    protected function find_stud_activ_data()
    {
        $d = Studentsport::where('id', '!=', 0)->orderBy('id', 'desc')->get();
        if(is_null($d))
        {
            return [];
        }
        return $this->format_stud_activ_data($d->toArray());
    }
    protected function format_stud_activ_data($data)
    {
        $rtn = [];
        foreach( $data as $_data):
            $sport_meta = Sport::find($_data['sport']);
            if(!is_null($sport_meta))
            {
                $_data['alabel'] = $sport_meta->name;
            }
            $student_meta = Student::find($_data['student']);
            if(!is_null($student_meta))
            {
                $_data['slabel'] = $student_meta->fname . ' ' . $student_meta->lname . '('.$student_meta->admission . ')';
            }
            array_push($rtn, $_data);
        endforeach;
        return $rtn;
    }
}
