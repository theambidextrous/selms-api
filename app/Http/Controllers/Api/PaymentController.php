<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Validator;
use Storage;
use Config;
use Carbon\Carbon;

use App\Models\Payment;
use App\Models\Fee;
use App\Models\Student;
use App\Models\Term;
/** mail */
use Illuminate\Support\Facades\Mail;
use App\Mail\Welcome;
use App\Mail\Code;

class PaymentController extends Controller
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
            DB::beginTransaction();
            $validator = Validator::make($request->all(), [
                'student' => 'required|string',
                'amount' => 'required|string',
                'remarks' => 'string',
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
            $input['amount'] = intval(abs($input['amount']));
            $stud_metadata = Student::find($input['student']);
            if(is_null($stud_metadata))
            {
                return response([
                    'status' => 400,
                    'message' => 'No student was found with the following admission number',
                    'errors' => [],
                ], 400); 
            }
            /** apply payment to fees for this kid */
            $input['received_by'] = Auth::user()->id;
            $feeItems = Fee::where('student', $input['student'])
                ->where('cleared', 0)
                ->get();
            $iter = floatval($input['amount']);
            foreach( $feeItems as $item ):
                if( $iter <= 0){
                    break;
                }
                $item_bal = floatval($item->fee) - floatval($item->paid_amount);
                $new_paid_amount = ( floatval($item->paid_amount) + $item_bal );
                $is_paid_fully = true;
                if( $iter < $item_bal){
                    $new_paid_amount = ( floatval($item->paid_amount) + $iter );
                    $is_paid_fully = false;
                }
                Fee::find($item->id)->update([
                    'paid_amount' => $new_paid_amount,
                    'cleared' => $is_paid_fully
                ]);

                $iter = $iter - $item_bal;

            endforeach;
            Payment::create($input);
            $response = $this->find_payment_data();
            DB::commit();
            return response([
                'status' => 200,
                'message' => 'Success. Done',
                'data' => $response,
            ], 200);

        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            return response([
                'status' => 400,
                'message' => "Server error. Invalid data",
                'errors' => $e->getMessage(),
            ], 400);
        } catch (PDOException $e) {
            DB::rollBack();
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
                'student' => 'required|string',
                'amount' => 'required|string',
                'remarks' => 'string',
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
            $input['amount'] = intval(abs($input['amount']));
            $stud_metadata = Student::find($input['student']);
            if(is_null($stud_metadata))
            {
                return response([
                    'status' => 400,
                    'message' => 'No student was found with the following admission number',
                    'errors' => [],
                ], 400); 
            }
            /** apply payment to fees for this kid */
            Payment::find($id)->update(['remarks' => $input['remarks']]);
            return response([
                'status' => 200,
                'message' => 'Success. Information updated',
                'data' => $this->find_payment_data(),
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
        Payment::find($id)->delete();
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
            'data' => $this->find_payment_data(),
        ], 200);
    }
    public function find($id)
    {
        $data = Payment::find($id);
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
    protected function find_payment_data()
    {
        $d = Payment::where('amount', '>', 0)->orderBy('id', 'desc')->get();
        if(is_null($d))
        {
            return [];
        }
        return $this->format_payment_data($d->toArray());
    }
    protected function format_payment_data($data)
    {
        $rtn = [];
        foreach( $data as $_data ):
            $stud_meta = Student::find($_data['student']);
            if(!is_null( $stud_meta ))
            {
                $_data['slabel'] = $stud_meta->fname . ' ' . $stud_meta->lname;
                $_data['admlabel'] = $stud_meta->admission;
            }
            //$_data['bal'] = $this->find_acc_bal($_data['id'], $_data['student']);
            $_data['posted'] = date('m/d/Y', strtotime($_data['created_at']));
            array_push($rtn, $_data);
        endforeach;
        return $rtn;
    }
    protected function find_acc_bal($id, $stud)
    {
        $all_amount_bal = Payment::where('student', $stud)->where('amount', '<', 0)->sum('amount');
        $all_amount_bal = abs($all_amount_bal);
        $paid_so_far = Payment::where('student', $stud)->where('amount', '>', 0)->where('id', '<=', $id)->sum('amount');
        $paid_so_far = abs($paid_so_far);
        $bal = $all_amount_bal - $paid_so_far;
        return $bal;
    }
}
