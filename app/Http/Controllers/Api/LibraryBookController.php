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

use App\Models\Librarybook;
use App\Models\Term;
use App\Models\Librarycatalogue;
/** mail */
use Illuminate\Support\Facades\Mail;
use App\Mail\Welcome;
use App\Mail\Code;

$file_uuid = (string) Str::uuid();

class LibraryBookController extends Controller
{
    public function add(Request $request)
    {
        if( !Auth::user()->is_super && !Auth::user()->is_lib )
        {
            return response([
                'status' => 400,
                'message' => 'Permission Denied. Only super admins allowed.',
                'errors' => [],
            ], 400);
        }
        try{
            $validator = Validator::make($request->all(), [
                'number' => 'required|string',
                'catalogue' => 'required|string',
            ]);
            if( $validator->fails() ){
                return response([
                    'status' => 400,
                    'message' => 'Error: Invalid field(s) detected',
                    'errors' => $validator->errors()->all(),
                ], 400);
            }
            $input = $request->all();
            Librarybook::create($input);
            $lib_cat = Librarycatalogue::find($input['catalogue']);
            $lib_cat->available = $lib_cat->available + 1;
            $lib_cat->save();
            return response([
                'status' => 200,
                'message' => 'Success. Done',
                'data' => $this->find_lib_book_data(),
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
        if( !Auth::user()->is_super && !Auth::user()->is_lib )
        {
            return response([
                'status' => 400,
                'message' => 'Permission Denied. Only super admins allowed.',
                'errors' => [],
            ], 400);
        }
        try{
            $validator = Validator::make($request->all(), [
                'number' => 'required|string',
                'catalogue' => 'required|string',
            ]);
            if( $validator->fails() ){
                return response([
                    'status' => 400,
                    'message' => 'Error: Invalid field(s) detected',
                    'errors' => $validator->errors()->all(),
                ], 400);
            }
            $input = $request->all();
            Librarybook::find($id)->update($input);
            return response([
                'status' => 200,
                'message' => 'Success. updated updated',
                'data' => $this->find_lib_book_data(),
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
    public function blend(Request $request)
    {
        if( !Auth::user()->is_super && !Auth::user()->is_lib )
        {
            return response([
                'status' => 400,
                'message' => 'Permission Denied. Only super admins allowed.',
                'errors' => $validator->errors()->all(),
            ], 400);
        }
        try{
            $validator = Validator::make($request->all(), [
                'book' => 'required|string|not_in:nn',
                'user' => 'required|string',
                'lent_from' => 'required|string',
                'lent_until' => 'required|string',
            ]);
            if( $validator->fails() ){
                return response([
                    'status' => 400,
                    'message' => 'Error: Invalid field(s) detected',
                    'errors' => $validator->errors()->all(),
                ], 400);
            }
            $input = $request->all();
            $this->is_marked_out($input);
            return response([
                'status' => 200,
                'message' => 'Success. updated updated',
                'data' => $this->find_lib_book_data(),
            ], 200);
        } catch (\Illuminate\Database\QueryException $e) {
            return response([
                'status' => 400,
                'message' => "Server error. Invalid data" . $e->getMessage(),
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
    public function breturn(Request $request)
    {
        if( !Auth::user()->is_super && !Auth::user()->is_lib )
        {
            return response([
                'status' => 400,
                'message' => 'Permission Denied. Only super admins allowed.',
                'errors' => $validator->errors()->all(),
            ], 400);
        }
        try{
            $validator = Validator::make($request->all(), [
                'book' => 'required|string|not_in:nn',
            ]);
            if( $validator->fails() ){
                return response([
                    'status' => 400,
                    'message' => 'Error: Invalid field(s) detected',
                    'errors' => $validator->errors()->all(),
                ], 400);
            }
            $input = $request->all();
            $this->is_marked_in($input);
            return response([
                'status' => 200,
                'message' => 'Success. updated updated',
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
    public function blost(Request $request)
    {
        if( !Auth::user()->is_super && !Auth::user()->is_lib )
        {
            return response([
                'status' => 400,
                'message' => 'Permission Denied. Only super admins allowed.',
                'errors' => $validator->errors()->all(),
            ], 400);
        }
        try{
            $validator = Validator::make($request->all(), [
                'book' => 'required|string|not_in:nn',
            ]);
            if( $validator->fails() ){
                return response([
                    'status' => 400,
                    'message' => 'Error: Invalid field(s) detected',
                    'errors' => $validator->errors()->all(),
                ], 400);
            }
            $input = $request->all();
            $this->is_marked_lost($input);
            return response([
                'status' => 200,
                'message' => 'Success. updated updated',
                'data' => $this->find_lib_book_data(),
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
        Librarybook::find($id)->delete();
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
            'data' => $this->find_lib_book_data(),
        ], 200);
    }
    public function find($id)
    {
        $data = Librarybook::find($id);
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
    protected function is_marked_out($data)
    {
        $book_meta = Librarybook::find($data['book']);
        if($book_meta->status != 'In')
        {
            throw new \Exception('Lending closed. This Book is out of shelf');
        }
        $cata_meta = Librarycatalogue::find($book_meta->catalogue);
        if(is_null($cata_meta))
        {
            throw new \Exception('Lending closed. No catalogue found');
        }
        if( $cata_meta->available < 1)
        {
            throw new \Exception('Lending closed. Books out of shelf');
        }
        $cata_meta->available = $cata_meta->available - 1;
        $cata_meta->lent = $cata_meta->lent + 1;
        $cata_meta->save();
        /** */
        $book_meta->status = 'Out';
        $book_meta->lent_to = $data['user'];
        $book_meta->lent_from = $data['lent_from'];
        $book_meta->lent_until = $data['lent_until'];
        $book_meta->save();
        return;
    }
    protected function is_marked_in($data)
    {
        $book_meta = Librarybook::find($data['book']);
        if( $book_meta->status != 'Out' )
        {
            throw new \Exception('Check In closed. Books status is in-shelf');
        }
        $cata_meta = Librarycatalogue::find($book_meta->catalogue);
        if(is_null($cata_meta))
        {
            throw new \Exception('Check In closed. No catalogue found');
        }
        $cata_meta->available = $cata_meta->available + 1;
        $cata_meta->lent = $cata_meta->lent - 1;
        $cata_meta->save();
        //
        $book_meta->status = 'In';
        $book_meta->lent_to = null;
        $book_meta->lent_from = null;
        $book_meta->lent_until = null;
        $book_meta->save();
        return;
    }
    protected function is_marked_lost($data)
    {
        $book_meta = Librarybook::find($data['book']);
        if( $book_meta->status != 'Out' )
        {
            throw new \Exception('Declaration rejected. Books status is ' . $book_meta->status);
        }
        $cata_meta = Librarycatalogue::find($book_meta->catalogue);
        if(is_null($cata_meta))
        {
            throw new \Exception('Declaration rejected. No catalogue found');
        }
        $cata_meta->lost = $cata_meta->lost + 1;
        $cata_meta->lent = $cata_meta->lent - 1;
        $cata_meta->save();
        //
        $book_meta->status = 'Lost';
        $book_meta->save();
        return;
    }
    protected function find_lib_book_data()
    {
        $d = Librarybook::where('id', '!=', 0)->orderBy('id', 'desc')->get();
        if(is_null($d))
        {
            return [];
        }
        return $this->format_lib_book_data($d->toArray());
    }
    protected function format_lib_book_data($data)
    {
        $rtn = [];
        foreach( $data as $_data ):
            $cata_meta = Librarycatalogue::find($_data['catalogue']);
            if(!is_null($cata_meta))
            {
                $_data['clabel'] = $cata_meta->title;
            }
            array_push($rtn, $_data);
        endforeach;

        return $rtn;
    }
}
