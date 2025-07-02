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

use App\Models\Translation;


class TranslationsController extends Controller
{
     /**
     * @OA\Post(
     *     path="/pci/api/v1/translations/add",
     *     tags={"Translations"},
     *     summary="Add language translation",
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function add(Request $request)
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
                'en' => 'required|string',
                'ar' => 'required|string',
            ]);
            if( $validator->fails() ){
                return response([
                    'status' => 400,
                    'message' => 'Error: Invalid field(s) detected',
                    'errors' => $validator->errors()->all(),
                ], 400);
            }
            $input = $request->all();
            $wordExists = Translation::where('en', $input['en'])->exists();
            $created = new \StdClass();
            if($wordExists){
                $created = Translation::where('en', $input['en'])->update($input);
            }else{
                $created = Translation::create($input);
            }
            return response([
                'status' => 200,
                'message' => 'Success. Done',
                'data' => $created,
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
     *     path="/pci/api/v1/translations/edit/{id}",
     *     tags={"Translations"},
     *     summary="Edit language translation",
     *     @OA\Response(response=200, description="Success")
     * )
     */
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
                'en' => 'required|string',
                'ar' => 'required|string',
            ]);
            if( $validator->fails() ){
                return response([
                    'status' => 400,
                    'message' => 'Error: Invalid field(s) detected',
                    'errors' => $validator->errors()->all(),
                ], 400);
            }
            $input = $request->all();
            $updated = Translation::find($id)->update($input);
            return response([
                'status' => 200,
                'message' => 'Success. Information updated',
                'data' => $updated,
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
     * @OA\Get(
     *     path="/pci/api/v1/translations/findall",
     *     tags={"Translations"},
     *     summary="List language translations",
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function findall()
    {
        $data = Translation::select('id', 'en', 'ar')->get()->toArray();
        // $mapped = array_map([$this, 'formtTranslation'], $data);
        return response([
            'status' => 200,
            'message' => "Done successfully",
            'data' => $data,
        ], 200);
    }

    // protected function formtTranslation($data){
    //     return $data;
    // }

    /**
     * @OA\Get(
     *     path="/pci/api/v1/translations/find/{edit}",
     *     tags={"Translations"},
     *     summary="Fetch language translation",
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function find($id)
    {
        $data = Translation::find($id);
        if( is_null($data) )
        {
            return response([
                'status' => 200,
                'message' => "Done successfully",
                'data' => (object)[],
            ], 200);
        }
        return response([
            'status' => 200,
            'message' => "Done successfully",
            'data' => $data,
        ], 200);
    }
}
