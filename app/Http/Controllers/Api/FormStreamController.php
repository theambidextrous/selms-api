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

use App\Models\Formstream;
use App\Models\Form;
use App\Models\User;
use App\Models\TSubject;
use App\Models\Subject;
/** mail */
use Illuminate\Support\Facades\Mail;
use App\Mail\Welcome;
use App\Mail\Code;

class FormStreamController extends Controller
{
      /**
     * @OA\Post(
     *     path="/pci/api/v1/forms-streams/add",
     *     tags={"Form streams"},
     *     summary="Add form stream",
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
                'form' => 'required|string|not_in:nn',
                'name' => 'required|string',
                'class_teacher' => 'required|string|not_in:nn',
                'label' => 'required|string',
            ]);
            if( $validator->fails() ){
                return response([
                    'status' => 400,
                    'message' => 'Error: Invalid field(s) detected',
                    'errors' => $validator->errors()->all(),
                ], 400);
            }
            $input = $request->all();
            $input['name'] = trim(strtoupper(str_replace(' ','_', $input['name'])));
            $isValid = Form::where('id', $input['form'])->exists();
            if(!$isValid){
                return response([
                    'status' => 400,
                    'message' => 'Invalid value ' . $input['form'],
                    'errors' => null,
                ], 400);
            }
            Formstream::create($input);
            return response([
                'status' => 200,
                'message' => 'Success. Done',
                'data' => $this->find_formstreams_data(),
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
     *     path="/pci/api/v1/forms-streams/edit/{id}",
     *     tags={"Form streams"},
     *     summary="Edit form stream",
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
                'form' => 'required|string|not_in:nn',
                'name' => 'required|string',
                'class_teacher' => 'required|string|not_in:nn',
                'label' => 'required|string',
            ]);
            if( $validator->fails() ){
                return response([
                    'status' => 400,
                    'message' => 'Error: Invalid field(s) detected',
                    'errors' => $validator->errors()->all(),
                ], 400);
            }
            $input = $request->all();
            Formstream::find($id)->update($input);
            return response([
                'status' => 200,
                'message' => 'Success. Information updated',
                'data' => $this->find_formstreams_data(),
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
     *     path="/pci/api/v1/forms-streams/drop/{id}",
     *     tags={"Form streams"},
     *     summary="Drop form stream",
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function drop($id)
    {
        Formstream::find($id)->delete();
        return response([
            'status' => 200,
            'message' => "Done successfully",
            'errors' => [],
        ], 200);
    }
    
    /**
     * @OA\Get(
     *     path="/pci/api/v1/forms-streams/findall",
     *     tags={"Form streams"},
     *     summary="Find all form streams",
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function findall(Request $request)
    {
        return response([
            'status' => 200,
            'message' => "Done successfully",
            'data' => $this->find_formstreams_data(),
        ], 200);
    }

     /**
     * @OA\Get(
     *     path="/pci/api/v1/forms-streams/findall/{teacher}",
     *     tags={"Form streams"},
     *     summary="Find all form streams",
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function findallByTeacher($teacher)
    {
        $t_subjects = TSubject::where("teacher", $teacher)
            ->select('subject')
            ->get();
        $t_forms = Subject::whereIn('id', $t_subjects)
            ->select('form')
            ->get();

        $d = Formstream::whereIn('form', $t_forms)->get();
        if(is_null($d))
        {
            return [];
        }
        return response([
            'status' => 200,
            'message' => "Done successfully",
            'data' => $this->format_streams_data($d->toArray()),
        ], 200);
    }


    /**
     * @OA\Get(
     *     path="/pci/api/v1/forms-streams/find/{id}",
     *     tags={"Form streams"},
     *     summary="Find form stream",
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function find($id)
    {
        $data = Formstream::find($id);
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
    protected function find_formstreams_data()
    {
        $d = Formstream::where('id', '!=', 0)->orderBy('id', 'desc')->get();
        if(is_null($d))
        {
            return [];
        }
        return $this->format_streams_data($d->toArray());
    }
    protected function format_streams_data($data)
    {
        $rtn = [];
        foreach( $data as $_data ):
            $p_meta = User::find($_data['class_teacher']);
            $frm_meta = Form::find($_data['form']);
            if(!is_null($p_meta))
            {
                $_data['tlabel'] = $p_meta->fname . ' ' . $p_meta->lname;
            }
            if(!is_null($frm_meta))
            {
                $_data['flabel'] = $frm_meta->name;
            }
            array_push($rtn, $_data);
        endforeach;
        return $rtn;
    }
}
