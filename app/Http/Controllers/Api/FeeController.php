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

use App\Models\Fee;
use App\Models\Term;
use App\Models\Student;
/** mail */
use Illuminate\Support\Facades\Mail;
use App\Mail\Welcome;
use App\Mail\Code;

class FeeController extends Controller
{
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
                'narration' => 'required|string',
                'student' => 'required|string',
                'fee' => 'required|string',
                'type' => 'required|string',
            ]);
            if( $validator->fails() ){
                return response([
                    'status' => 400,
                    'message' => 'Error: Invalid field(s) detected',
                    'errors' => $validator->errors()->all(),
                ], 400);
            }
            $input = $request->all();
            if( !$this->has_current_trm() )
            {
                return response([
                    'status' => 400,
                    'message' => 'Current term not set',
                    'data' => [],
                ], 400);
            }
            $input['fee'] = intval(abs($input['fee']));
            $stud_metadata = Student::where('admission', $input['student'])->first();
            if(is_null($stud_metadata))
            {
                return response([
                    'status' => 400,
                    'message' => 'No student was found with the following admission number',
                    'errors' => [],
                ], 400); 
            }
            if($input['type'] == 'Tution' && !strlen($input['subject'])){
                return response([
                    'status' => 400,
                    'message' => 'Must provide subject if fee type is "Tution"',
                    'errors' => [],
                ], 400); 
            }
            $input['student'] = $stud_metadata->id;
            $input['term'] = $this->find_current_trm();
            Fee::create($input);
            return response([
                'status' => 200,
                'message' => 'Success. Done',
                'data' => $this->find_fees_data(),
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
                'narration' => 'required|string',
                'student' => 'required|string',
                'fee' => 'required|string',
                'type' => 'required|string',
            ]);
            if( $validator->fails() ){
                return response([
                    'status' => 400,
                    'message' => 'Error: Invalid field(s) detected',
                    'errors' => $validator->errors()->all(),
                ], 400);
            }
            $input = $request->all();
            if( !$this->has_current_trm() )
            {
                return response([
                    'status' => 400,
                    'message' => 'Current term not set',
                    'data' => [],
                ], 400);
            }
            if($input['type'] == 'Tution' && !strlen($input['subject'])){
                return response([
                    'status' => 400,
                    'message' => 'Must provide subject if fee type is "Tution"',
                    'errors' => [],
                ], 400); 
            }
            $input['fee'] = intval(abs($input['fee']));
            $input['term'] = $this->find_current_trm();
            Fee::find($id)->update($input);
            return response([
                'status' => 200,
                'message' => 'Success. Information updated',
                'data' => $this->find_fees_data(),
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
        Fee::find($id)->delete();
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
            'data' => $this->find_fees_data(),
        ], 200);
    }
    public function find($id)
    {
        $data = Fee::find($id);
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
    protected function has_current_trm()
    {
        $d = Term::where('is_current', true)->count();
        if( $d )
        {
            return true;
        }
        return false;
    }
    protected function find_current_trm()
    {
        $d = Term::where('is_current', true)->first();
        if( is_null($d) )
        {
            return 0;
        }
        return $d->id;
    }
    protected function find_fees_data()
    {
        $d = Fee::where('cleared', 0)->orderBy('id', 'desc')->get();
        if(is_null($d))
        {
            return [];
        }
        return $this->format_fees_data($d->toArray());
    }
    protected function format_fees_data($data)
    {
        $rtn = [];
        foreach( $data as $_data ):
            $term_meta = Term::find($_data['term']);
            if(!is_null( $term_meta ))
            {
                $_data['ylabel'] = $term_meta->year . ' ' . $term_meta->label;
            }
            $stud_meta = Student::find($_data['student']);
            if(!is_null( $stud_meta ))
            {
                $_data['slabel'] = $stud_meta->fname . ' ' . $stud_meta->lname;
                $_data['admlabel'] = $stud_meta->admission;
                $_data['student'] = $stud_meta->admission;
            }
            $_data['posted'] = date('m/d/Y', strtotime($_data['created_at']));
            array_push($rtn, $_data);
        endforeach;
        return $rtn;
    }
}
