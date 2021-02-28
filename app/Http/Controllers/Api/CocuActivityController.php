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

use App\Models\Sport;
use App\Models\User;
/** mail */
use Illuminate\Support\Facades\Mail;
use App\Mail\Welcome;
use App\Mail\Code;


class CocuActivityController extends Controller
{
    public function add(Request $request)
    {
        $file_uuid = (string) Str::uuid();
        if( !Auth::user()->is_super && !Auth::user()->is_admin )
        {
            return response([
                'status' => 201,
                'message' => 'Permission Denied. Only super admins allowed.',
                'errors' => [],
            ], 403);
        }
        try{
            $validator = Validator::make($request->all(), [
                'name' => 'required|string',
                'description' => 'required|string',
            ]);
            if( $validator->fails() ){
                return response([
                    'status' => 201,
                    'message' => 'A required field was not found',
                    'errors' => $validator->errors()->all(),
                ], 403);
            }
            $input = $request->all();
            Sport::create($input);
            return response([
                'status' => 200,
                'message' => 'Success. Item created',
                'data' => $this->find_activ_data(),
            ], 200);
        } catch (\Illuminate\Database\QueryException $e) {
            return response([
                'status' => 201,
                'message' => "Server error. Invalid data",
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
        $file_uuid = (string) Str::uuid();
        if( !Auth::user()->is_super && !Auth::user()->is_admin )
        {
            return response([
                'status' => 201,
                'message' => 'Permission Denied. Only super admins allowed.',
                'errors' => [],
            ], 403);
        }
        try{
            $validator = Validator::make($request->all(), [
                'name' => 'required|string',
                'description' => 'required|string',
            ]);
            if( $validator->fails() ){
                return response([
                    'status' => 201,
                    'message' => 'A required field was not found',
                    'errors' => $validator->errors()->all(),
                ], 403);
            }
            $input = $request->all();
            Sport::find($id)->update($input);
            return response([
                'status' => 200,
                'message' => 'Success. Information updated',
                'data' => $this->find_activ_data(),
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
        Sport::find($id)->delete();
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
            'data' => $this->find_activ_data(),
        ], 200);
    }
    public function find($id)
    {
        $data = Sport::find($id);
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
    protected function find_activ_data()
    {
        $d = Sport::where('id', '!=', 0)->orderBy('id', 'desc')->get();
        if(is_null($d))
        {
            return [];
        }
        return $this->format_activ_data($d->toArray());
    }
    protected function format_activ_data($data)
    {
        return $data;
    }
}
