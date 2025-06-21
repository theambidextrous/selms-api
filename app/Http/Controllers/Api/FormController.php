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

use App\Models\Form;
use App\Models\Pcode;
use App\Models\Setup;
/** mail */
use Illuminate\Support\Facades\Mail;
use App\Mail\Welcome;
use App\Mail\Code;

$file_uuid = (string) Str::uuid();

class FormController extends Controller
{
    
    /**
     * @OA\Post(
     *     path="/pci/api/v1/forms/add",
     *     tags={"Form"},
     *     summary="Add form",
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
                'errors' => $validator->errors()->all(),
            ], 400);
        }
        try{
            $validator = Validator::make($request->all(), [
                'name' => 'required|string',
                'description' => 'required|string',
            ]);
            if( $validator->fails() ){
                return response([
                    'status' => 400,
                    'message' => 'A required field was not found',
                    'errors' => $validator->errors()->all(),
                ], 400);
            }
            $input = $request->all();
            $input['name'] = trim(strtoupper(str_replace(' ','', $input['name'])));
            Form::create($input);
            return response([
                'status' => 200,
                'message' => 'Success. Done',
                'data' => $this->find_forms_data(),
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
     *     path="/pci/api/v1/forms/edit/{id}",
     *     tags={"Form"},
     *     summary="Edit form",
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
                'errors' => $validator->errors()->all(),
            ], 400);
        }
        try{
            $validator = Validator::make($request->all(), [
                'name' => 'required|string',
                'description' => 'required|string',
            ]);
            if( $validator->fails() ){
                return response([
                    'status' => 400,
                    'message' => 'A required field was not found',
                    'errors' => $validator->errors()->all(),
                ], 400);
            }
            $input = $request->all();
            $input['name'] = trim(strtoupper(str_replace(' ','', $input['name'])));
            Form::find($id)->update($input);
            return response([
                'status' => 200,
                'message' => 'Success. Information updated',
                'data' => $this->find_forms_data(),
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
     *     path="/pci/api/v1/forms/drop/{id}",
     *     tags={"Form"},
     *     summary="Drop form",
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function drop($id)
    {
        Form::find($id)->delete();
        return response([
            'status' => 200,
            'message' => "Done successfully",
            'errors' => [],
        ], 200);
    }
    
      /**
     * @OA\Get(
     *     path="/pci/api/v1/forms/findall",
     *     tags={"Form"},
     *     summary="List forms",
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function findall(Request $request)
    {
        return response([
            'status' => 200,
            'message' => "Done successfully",
            'data' => $this->find_forms_data(),
        ], 200);
    }

      /**
     * @OA\Post(
     *     path="/pci/api/v1/forms/find/{id}",
     *     tags={"Form"},
     *     summary="Find one form",
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function find($id)
    {
        $data = Form::find($id);
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
    protected function find_forms_data()
    {
        $d = Form::where('id', '!=', 0)->orderBy('id', 'desc')->get();
        if(is_null($d))
        {
            return [];
        }
        return $d->toArray();
    }
}
