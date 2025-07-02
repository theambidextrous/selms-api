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

use App\Models\Position;
use App\Models\User;
use App\Models\Setup;
/** mail */
use Illuminate\Support\Facades\Mail;
use App\Mail\Welcome;
use App\Mail\Code;


class PositionController extends Controller
{
    /**
     * @OA\Post(
     *     path="/pci/api/v1/positions/add",
     *     tags={"Positions"},
     *     summary="Add position",
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function add(Request $request)
    {
        $file_uuid = (string) Str::uuid();
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
                'person' => 'required|string',
                'title' => 'required|string',
            ]);
            if( $validator->fails() ){
                return response([
                    'status' => 400,
                    'message' => 'Error: Invalid field(s) detected',
                    'errors' => $validator->errors()->all(),
                ], 400);
            }
            $input = $request->all();
            if( $request->hasfile('signature') )
            {
                $file_content = $request->file('signature');
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
                $input['signature'] = $file_content_name;
            }
            Position::create($input);
            return response([
                'status' => 200,
                'message' => 'Success. Account created',
                'data' => $this->find_positions_data(),
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
     *     path="/pci/api/v1/positions/edit/{id}",
     *     tags={"Positions"},
     *     summary="Edit position",
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function edit(Request $request, $id)
    {
        $file_uuid = (string) Str::uuid();
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
                'person' => 'required|string',
                'title' => 'required|string',
            ]);
            if( $validator->fails() ){
                return response([
                    'status' => 400,
                    'message' => 'Error: Invalid field(s) detected',
                    'errors' => $validator->errors()->all(),
                ], 400);
            }
            $input = $request->all();
            if( $request->hasfile('signature') )
            {
                $file_content = $request->file('signature');
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
                $input['signature'] = $file_content_name;
            }
            Position::find($id)->update($input);
            return response([
                'status' => 200,
                'message' => 'Success. Information updated',
                'data' => $this->find_positions_data(),
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
     *     path="/pci/api/v1/positions/drop/{id}",
     *     tags={"Positions"},
     *     summary="Drop position",
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function drop($id)
    {
        Position::find($id)->delete();
        return response([
            'status' => 200,
            'message' => "Done successfully",
            'errors' => [],
        ], 200);
    }
    
       /**
     * @OA\Get(
     *     path="/pci/api/v1/positions/findall",
     *     tags={"Positions"},
     *     summary="List positions",
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function findall(Request $request)
    {
        return response([
            'status' => 200,
            'message' => "Done successfully",
            'data' => $this->find_positions_data(),
        ], 200);
    }
       /**
     * @OA\Get(
     *     path="/pci/api/v1/positions/find/{id}",
     *     tags={"Positions"},
     *     summary="Find one position",
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function find($id)
    {
        $data = Position::find($id);
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
    protected function find_positions_data()
    {
        $d = Position::where('id', '!=', 0)->orderBy('id', 'desc')->get();
        if(is_null($d))
        {
            return [];
        }
        return $this->format_positions_data($d->toArray());
    }
    protected function format_positions_data($data)
    {
        $rtn = [];
        foreach( $data as $_data ):
            $p_meta = User::find($_data['person']);
            if(!is_null($p_meta))
            {
                $_data['plabel'] = $p_meta->fname . ' ' . $p_meta->lname;
            }
            $_data['signature'] = route('stream', ['file' => $_data['signature']]);
            array_push($rtn, $_data);
        endforeach;
        return $rtn;
    }
}
