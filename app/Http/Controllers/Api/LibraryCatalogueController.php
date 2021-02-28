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

use App\Models\Librarycatalogue;
use App\Models\Term;
use App\Models\Setup;
/** mail */
use Illuminate\Support\Facades\Mail;
use App\Mail\Welcome;
use App\Mail\Code;

$file_uuid = (string) Str::uuid();

class LibraryCatalogueController extends Controller
{
    public function add(Request $request)
    {
        if( !Auth::user()->is_super && !Auth::user()->is_lib )
        {
            return response([
                'status' => 201,
                'message' => 'Permission Denied. Only super admins allowed.',
                'errors' => [],
            ], 403);
        }
        try{
            $validator = Validator::make($request->all(), [
                'title' => 'required|string',
                'author' => 'required|string',
                'publisher' => 'required|string',
                // 'available' => 'required|string|not_in:nn',
                // 'lent' => 'required|string',
                // 'lost' => 'required|string',
            ]);
            if( $validator->fails() ){
                return response([
                    'status' => 201,
                    'message' => 'A required field was not found',
                    'errors' => $validator->errors()->all(),
                ], 403);
            }
            $input = $request->all();
            $input['available'] = 0;
            $input['lent'] = 0;
            $input['lost'] = 0;
            Librarycatalogue::create($input);
            return response([
                'status' => 200,
                'message' => 'Success. Done',
                'data' => $this->find_libcat_data(),
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
        if( !Auth::user()->is_super && !Auth::user()->is_lib )
        {
            return response([
                'status' => 201,
                'message' => 'Permission Denied. Only super admins allowed.',
                'errors' => [],
            ], 403);
        }
        try{
            $validator = Validator::make($request->all(), [
                'title' => 'required|string',
                'author' => 'required|string',
                'publisher' => 'required|string',
                // 'available' => 'required|string|not_in:nn',
                // 'lent' => 'required|string',
                // 'lost' => 'required|string',
            ]);
            if( $validator->fails() ){
                return response([
                    'status' => 201,
                    'message' => 'A required field was not found',
                    'errors' => $validator->errors()->all(),
                ], 403);
            }
            $input = $request->all();
            Librarycatalogue::find($id)->update($input);
            return response([
                'status' => 200,
                'message' => 'Success. updated updated',
                'data' => $this->find_libcat_data(),
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
        // Librarycatalogue::find($id)->delete();
        return response([
            'status' => 200,
            'message' => "Error. Deletion not allowed",
            'errors' => [],
        ], 200);
    }
    
    public function findall(Request $request)
    {
        return response([
            'status' => 200,
            'message' => "Done successfully",
            'data' => $this->find_libcat_data(),
        ], 200);
    }
    public function find($id)
    {
        $data = Librarycatalogue::find($id);
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
    protected function find_libcat_data()
    {
        $d = Librarycatalogue::where('id', '!=', 0)->orderBy('id', 'desc')->get();
        if(is_null($d))
        {
            return [];
        }
        return $this->format_lib_Cat_data($d->toArray());
    }
    protected function format_lib_Cat_data($data)
    {
        return $data;
    }
}
