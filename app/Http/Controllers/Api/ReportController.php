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
use PDF;

use App\Models\Member;
use App\Models\Board;
use App\Models\Setup;
use App\Models\Loan;
use App\Models\Repayment;
use App\Models\Contribution;

class ReportController extends Controller
{
    public function repayment(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'loan' => 'required|string',
            'from_date' => 'string',
            'to_date' => 'string',
        ]);
        if( $validator->fails() ){
            return response([
                'status' => 201,
                'message' => 'Invalid statement data. Please enter loan number and dates correctly',
                'errors' => $validator->errors()->all(),
            ], 403);
        }
        $from_date = date('Y-m-d', strtotime($req->get('from_date')));
        $to_date = date('Y-m-d', strtotime($req->get('to_date')));
        $rdata = [];
        $summations = [
            'a' => '0.00',
            'b' => '0.00',
            'c' => '0.00',
        ];
        if( $to_date < $from_date )
        {
            return response([
                'status' => 201,
                'message' => '"Start date" cannot be greater than "End date"',
                'errors' => [],
            ], 403);
        }
        if(!strlen($req->get('from_date')) || !strlen($req->get('to_date')))
        {
            return response([
                'status' => 201,
                'message' => 'Invalid statement data. Please select valid dates.',
                'errors' => [],
            ], 403);
        }
        $input = $req->all();
        $p = Repayment::where('loan', $input['loan'])
            ->where('pay_date', '>=', $from_date)
            ->where('pay_date', '<=', $to_date)
            ->orderBy('pay_date', 'asc')
            ->get();
        if(!is_null($p)){ $rdata = $p->toArray();}
        $rdata_meta = $this->format_rdata($rdata);
        $summations = [
            'a' => number_format($rdata_meta[1], 2),
            'b' => number_format(0, 2),
            'c' => number_format($rdata_meta[2], 2),
        ];
        return response([
            'status' => 200,
            'message' => 'data found with ',
            'rdata' => $rdata_meta[0],
            'summations' => $summations,
        ], 200);
    }

    public function download_repayment(Request $req)
    {
        $uuid_string = (string)Str::uuid() . '.pdf';
        $validator = Validator::make($req->all(), [
            'loan' => 'required|string',
            'from_date' => 'string',
            'to_date' => 'string',
        ]);
        if( $validator->fails() ){
            return response([
                'status' => 201,
                'message' => 'Invalid statement data. Please enter loan number and dates correctly',
                'errors' => $validator->errors()->all(),
            ], 403);
        }
        $from_date = date('Y-m-d', strtotime($req->get('from_date')));
        $to_date = date('Y-m-d', strtotime($req->get('to_date')));
        $rdata = [];
        $summations = [
            'a' => '0.00',
            'b' => '0.00',
            'c' => '0.00',
        ];
        if( $to_date < $from_date )
        {
            return response([
                'status' => 201,
                'message' => '"Start date" cannot be greater than "End date"',
                'errors' => [],
            ], 403);
        }
        if(!strlen($req->get('from_date')) || !strlen($req->get('to_date')))
        {
            return response([
                'status' => 201,
                'message' => 'Invalid statement data. Please select valid dates.',
                'errors' => [],
            ], 403);
        }
        $input = $req->all();
        $p = Repayment::where('loan', $input['loan'])
            ->where('pay_date', '>=', $from_date)
            ->where('pay_date', '<=', $to_date)
            ->orderBy('pay_date', 'asc')
            ->get();
        if(!is_null($p)){ $rdata = $p->toArray();}
        $rdata_meta = $this->format_rdata($rdata);
        $this_loan_meta = Loan::find($input['loan']);
        $summations = [
            'a' => number_format($rdata_meta[1], 2),
            'b' => number_format(0, 2),
            'c' => number_format($rdata_meta[2], 2),
        ];
        $pdf_data = [
            'rdata' => $rdata_meta[0],
            'summations' => $summations,
            'setup' => $this->find_setup(),
            'owner' => $this->find_loan_owner($input['loan']),
            'info' => $this->find_loan_info($this_loan_meta),
        ];
        $filename = ('app/cls/' . $uuid_string);
        PDF::loadView('reports.repayment', $pdf_data)->save(storage_path($filename));
        return response([
            'status' => 200,
            'message' => 'Report generated',
            'fileurl' => route('stream', ['file' => $uuid_string]),
            'errors' => [],
        ], 200);
    }


    public function contribution(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'member' => 'required|string',
            'from_date' => 'string',
            'to_date' => 'string',
        ]);
        if( $validator->fails() ){
            return response([
                'status' => 201,
                'message' => 'Invalid statement data. Please enter member number and dates correctly',
                'errors' => $validator->errors()->all(),
            ], 403);
        }
        $from_date = date('Y-m-d', strtotime($req->get('from_date')));
        $to_date = date('Y-m-d', strtotime($req->get('to_date')));
        $rdata = [];
        $summations = [
            'a' => '0.00',
        ];
        if( $to_date < $from_date )
        {
            return response([
                'status' => 201,
                'message' => '"Start date" cannot be greater than "End date"',
                'errors' => [],
            ], 403);
        }
        if(!strlen($req->get('from_date')) || !strlen($req->get('to_date')))
        {
            return response([
                'status' => 201,
                'message' => 'Invalid statement data. Please select valid dates.',
                'errors' => [],
            ], 403);
        }
        $input = $req->all();
        $p = Contribution::where('member', $input['member'])
            ->where('created_at', '>=', $from_date)
            ->where('created_at', '<=', $to_date)
            ->orderBy('month_of', 'asc')
            ->get();
        if(!is_null($p)){ $rdata = $p->toArray();}
        $rdata_meta = $this->format_rdata_contr($rdata);
        $summations = [
            'a' => number_format($rdata_meta[1], 2),
        ];
        return response([
            'status' => 200,
            'message' => 'data found with ',
            'rdata' => $rdata_meta[0],
            'summations' => $summations,
        ], 200);
    }

    public function download_contribution(Request $req)
    {
        $uuid_string = (string)Str::uuid() . '.pdf';
        $validator = Validator::make($req->all(), [
            'member' => 'required|string',
            'from_date' => 'string',
            'to_date' => 'string',
        ]);
        if( $validator->fails() ){
            return response([
                'status' => 201,
                'message' => 'Invalid statement data. Please enter member number and dates correctly',
                'errors' => $validator->errors()->all(),
            ], 403);
        }
        $from_date = date('Y-m-d', strtotime($req->get('from_date')));
        $to_date = date('Y-m-d', strtotime($req->get('to_date')));
        $rdata = [];
        $summations = [
            'a' => '0.00',
        ];
        if( $to_date < $from_date )
        {
            return response([
                'status' => 201,
                'message' => '"Start date" cannot be greater than "End date"',
                'errors' => [],
            ], 403);
        }
        if(!strlen($req->get('from_date')) || !strlen($req->get('to_date')))
        {
            return response([
                'status' => 201,
                'message' => 'Invalid statement data. Please select valid dates.',
                'errors' => [],
            ], 403);
        }
        $input = $req->all();
        $p = Contribution::where('member', $input['member'])
            ->where('created_at', '>=', $from_date)
            ->where('created_at', '<=', $to_date)
            ->orderBy('month_of', 'asc')
            ->get();
        if(!is_null($p)){ $rdata = $p->toArray();}
        $rdata_meta = $this->format_rdata_contr($rdata);
        $summations = [
            'a' => number_format($rdata_meta[1], 2),
        ];
        $pdf_data = [
            'rdata' => $rdata_meta[0],
            'summations' => $summations,
            'setup' => $this->find_setup(),
            'owner' => $this->find_contrib_owner($input['member']),
        ];
        $filename = ('app/cls/' . $uuid_string);
        PDF::loadView('reports.contribution', $pdf_data)->save(storage_path($filename));
        return response([
            'status' => 200,
            'message' => 'Report generated',
            'fileurl' => route('stream', ['file' => $uuid_string]),
            'errors' => [],
        ], 200);
    }

    public function stream($file)
    {
        $filename = ('app/cls/trt/content/' . $file);
        return response()->download(storage_path($filename), null, [], null); 
    }

    
    protected function find_loan_owner($loan)
    {
        $p = Loan::find($loan);
        if( is_null($p) )
        {
            return [
                'fname' => null,
                'lname' => null,
                'address' => null,
                'city' => null,
                'county' => null,
                'zip' => null,
                'email' => null,
                'phone' => null,
            ];
        }
        $owner = Member::find($p->member);
        if( is_null($owner) )
        {
            return [
                'fname' => null,
                'lname' => null,
                'address' => null,
                'city' => null,
                'county' => null,
                'zip' => null,
                'email' => null,
                'phone' => null,
            ];
        }
        return $owner->toArray();
    }
    protected function find_contrib_owner($member)
    {
        $owner = Member::find($member);
        if( is_null($owner) )
        {
            return [
                'fname' => null,
                'lname' => null,
                'address' => null,
                'city' => null,
                'county' => null,
                'zip' => null,
                'email' => null,
                'phone' => null,
            ];
        }
        return $owner->toArray();
    }
    protected function find_setup()
    {
        $s = Setup::where('id', '!=', 0)->first();
        if(!is_null($s))
        {
            return $s->toArray();
        }
        return [
            'company' => null,
            'address' => null,
            'city' => null,
            'state' => null,
            'zip' => null,
            'email' => null,
            'phone' => null,
        ];
    }
    protected function clean_n($n)
    {
        return str_replace(',', '', $n);
    }
    protected function format_rdata($data)
    {
        $rtn = [];
        $last_run_bal = 0;
        $paid_sum = [];
        foreach($data as $_data):
            $running_bal = $this->find_run_bal($_data);
            $_data['interest'] = number_format(0, 2);
            $_data['rbal'] = number_format($running_bal, 2);
            $_data['approver'] = Board::find($_data['received_by'])->position;
            $last_run_bal = $running_bal;
            array_push($paid_sum, $_data['paid']);
            $_data['paid'] = number_format($_data['paid'], 2);
            $_data['pay_date'] = date('m/d/Y', strtotime($_data['paid']));
            array_push($rtn, $_data);
        endforeach;
        return [ $rtn, array_sum($paid_sum), $last_run_bal ];
    }
    protected function format_rdata_contr($data)
    {
        $rtn = [];
        $contrib_sum = [];
        foreach($data as $_data):
            $_data['created_at'] = date('m/d/Y', strtotime($_data['created_at']));
            $_data['receiver'] = Board::find($_data['received_by'])->position;
            array_push($contrib_sum, $_data['amount']);
            $_data['amount'] = number_format($_data['amount'], 2);
            $_data['month'] = date('M Y', strtotime($_data['month_of']));
            array_push($rtn, $_data);
        endforeach;
        return [ $rtn, array_sum($contrib_sum) ];
    }
    protected function find_run_bal($_data)
    {
        $loan_meta = Loan::find($_data['loan']);
        $loan_amt = $loan_meta->amount;
        $paid_sf = $this->find_paid_sofar($_data['loan'], $_data['id']);
        return ($loan_amt - $paid_sf);
    }
    protected function find_paid_sofar($loan, $id)
    {
        $p = Repayment::where('loan', $loan)
            ->where('id', '<=', $id)
            ->sum('paid');
        return $p;
    }
    protected function find_loan_info($info)
    {
        $rtn = [
            'amount' => null,
            'mlabel' => null,
            'interest' => null,
            'glabel' => null,
            'blabel' => null,
            'rfrom' => null,
            'rto' => null,
        ];
        if(!is_null($info))
        {
            $me = Member::find($info->member);
            $be = Board::find($info->approved_by);
            $rtn = [
                'amount' => 'KES.' . number_format($info->amount, 2),
                'mlabel' => $me->fname . ' ' . $me->lname,
                'interest' => '0.0%',
                'glabel' => $this->guarantor_names(explode(',', $info->guarantors)),
                'blabel' => $be->position,
                'rfrom' => $info->repayment_start,
                'rto' => $info->repayment_end,
            ];
        }
        return $rtn;
    }
    protected function guarantor_names($arr)
    {
        $rtn = [];
        foreach( $arr as $id ):
            $m_meta = Member::find($id);
            $label = $m_meta->fname . ' ' . $m_meta->lname;
            array_push($rtn, $label);
        endforeach;
        return implode(', ', $rtn);
    }
}
