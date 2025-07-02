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

use App\Models\Scale;
use App\Models\Form;
use App\Models\Student;
/** mail */
use Illuminate\Support\Facades\Mail;
use App\Mail\Welcome;
use App\Mail\Code;

class ScaleController extends Controller
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
                'min_mark' => 'required|integer',
                'max_mark' => 'required|integer',
                'grade' => 'required|string',
                'form' => 'required|string|not_in:nn',
            ]);
            if( $validator->fails() ){
                return response([
                    'status' => 400,
                    'message' => 'Error: Invalid field(s) detected',
                    'errors' => $validator->errors()->all(),
                ], 400);
            }
            $input = $request->all();
            if($input['max_mark'] < $input['min_mark'] ){
                return response([
                    'status' => 400,
                    'message' => 'Upper limit must be greater than lower limit',
                    'errors' => $validator->errors()->all(),
                ], 400);
            }
            $input['grade'] = strtoupper($input['grade']);
            Scale::create($input);
            return response([
                'status' => 200,
                'message' => 'Success. Done',
                'data' => $this->find_sclaes_data(),
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
        if( !Auth::user()->is_super && !Auth::user()->is_fin )
        {
            return response([
                'status' => 400,
                'message' => 'Permission Denied. Only super admins allowed.',
                'errors' => [],
            ], 400);
        }
        try{
            $validator = Validator::make($request->all(), [
                'min_mark' => 'required|integer',
                'max_mark' => 'required|integer',
                'grade' => 'required|string',
                'form' => 'required|string|not_in:nn',
            ]);
            if( $validator->fails() ){
                return response([
                    'status' => 400,
                    'message' => 'Error: Invalid field(s) detected',
                    'errors' => $validator->errors()->all(),
                ], 400);
            }
            $input = $request->all();
            if($input['max_mark'] < $input['min_mark'] ){
                return response([
                    'status' => 400,
                    'message' => 'Upper limit must be greater than lower limit',
                    'errors' => $validator->errors()->all(),
                ], 400);
            }
            $input['grade'] = strtoupper($input['grade']);
            Scale::find($id)->update($input);
            return response([
                'status' => 200,
                'message' => 'Success. Information updated',
                'data' => $this->find_sclaes_data(),
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
        Scale::find($id)->delete();
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
            'data' => $this->find_sclaes_data(),
        ], 200);
    }
    public function find($id)
    {
        $data = Scale::find($id);
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
    protected function find_sclaes_data()
    {
        $d = Scale::where('id', '!=', 0)->orderBy('id', 'desc')->get();
        if(is_null($d))
        {
            return [];
        }
        return $this->format_scales_data($d->toArray());
    }
    protected function format_scales_data($data)
    {
        $rtn = [];
        foreach( $data as $_data ):
            $form_meta = Form::find($_data['form']);
            if(!is_null( $form_meta ))
            {
                $_data['flabel'] = $form_meta->name;
            }
            array_push($rtn, $_data);
        endforeach;
        return $rtn;
    }
}
