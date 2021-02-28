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

use App\Models\Term;
use App\Models\Student;
use App\Models\Setup;
/** mail */
use Illuminate\Support\Facades\Mail;
use App\Mail\Welcome;
use App\Mail\Code;

class TermController extends Controller
{
    public function add(Request $request)
    {
        if( !Auth::user()->is_super )
        {
            return response([
                'status' => 201,
                'message' => 'Permission Denied. Only super admins allowed.',
                'errors' => $validator->errors()->all(),
            ], 403);
        }
        try{
            $validator = Validator::make($request->all(), [
                'year' => 'required|string',
                'label' => 'required|string',
                'start' => 'required|string',
                'end' => 'required|string',
                'is_current' => 'required',
                'f1_fee' => 'required|string',
                'f2_fee' => 'required|string',
                'f3_fee' => 'required|string',
                'f4_fee' => 'required|string',
            ]);
            if( $validator->fails() ){
                return response([
                    'status' => 201,
                    'message' => 'A required field was not found',
                    'errors' => $validator->errors()->all(),
                ], 403);
            }
            $input = $request->all();
            $input['label'] = trim(strtoupper(str_replace(' ', '', $input['label'])));
            $this->sudo_rm_current_term($input);
            $trm = Term::create($input)->id;
            $this->sudo_update_stud_term($input, $trm);
            return response([
                'status' => 200,
                'message' => 'Success. Done',
                'data' => $this->find_terms_data(),
            ], 200);
        } catch (\Illuminate\Database\QueryException $e) {
            return response([
                'status' => 201,
                'message' => "Server error. Invalid data" . $e->getMessage(),
                'errors' => $e->getMessage(),
            ], 403);
        } catch (PDOException $e) {
            return response([
                'status' => 201,
                'message' => "Db error. Invalid data",
                'errors' => $e->getMessage(),
            ], 403);
        }
    }
    public function edit(Request $request, $id)
    {
        if( !Auth::user()->is_super )
        {
            return response([
                'status' => 201,
                'message' => 'Permission Denied. Only super admins allowed.',
                'errors' => $validator->errors()->all(),
            ], 403);
        }
        try{
            $validator = Validator::make($request->all(), [
                'year' => 'required|string',
                'label' => 'required|string',
                'start' => 'required|string',
                'end' => 'required|string',
                'is_current' => 'required',
                'f1_fee' => 'required|string',
                'f2_fee' => 'required|string',
                'f3_fee' => 'required|string',
                'f4_fee' => 'required|string',
            ]);
            if( $validator->fails() ){
                return response([
                    'status' => 201,
                    'message' => 'A required field was not found',
                    'errors' => $validator->errors()->all(),
                ], 403);
            }
            $input = $request->all();
            $input['label'] = trim(strtoupper(str_replace(' ', '', $input['label'])));
            $this->sudo_rm_current_term($input);
            Term::find($id)->update($input);
            $this->sudo_update_stud_term($input, $id);
            return response([
                'status' => 200,
                'message' => 'Success. Information updated',
                'data' => $this->find_terms_data(),
            ], 200);
        } catch (\Illuminate\Database\QueryException $e) {
            return response([
                'status' => 201,
                'message' => "Server error. Invalid data",
                'errors' => [],
            ], 403);
        } catch (PDOException $e) {
            return response([
                'status' => 201,
                'message' => "Db error. Invalid data",
                'errors' => [],
            ], 403);
        }
    }
    public function drop($id)
    {
        Term::find($id)->delete();
        return response([
            'status' => 200,
            'message' => "Done successfully",
            'errors' => [],
        ], 200);
    }
    
    public function findall(Request $request)
    {
        return response([
            'status' => 200,
            'message' => "Done successfully",
            'data' => $this->find_terms_data(),
        ], 200);
    }
    public function find($id)
    {
        $data = Term::find($id);
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
    protected function find_terms_data()
    {
        $d = Term::where('id', '!=', 0)->orderBy('id', 'desc')->get();
        if(is_null($d))
        {
            return [];
        }
        return $d->toArray();
    }
    protected function sudo_rm_current_term($data)
    {
        if( intval($data['is_current']) == 1 )
        {
            Term::where('id', '!=', 0)->update([
                'is_current' => false,
            ]);
            return;
        }
    }
    protected function sudo_update_stud_term($data, $trm)
    {
        if( intval($data['is_current']) == 1 )
        {
            Student::where('is_active', true)->update([
                'current_term' => $trm,
            ]);
        }
        return;
    }
}
