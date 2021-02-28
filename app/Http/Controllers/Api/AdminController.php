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

use App\Models\User;
use App\Models\Pcode;
use App\Models\Term;
use App\Models\Setup;
/** mail */
use Illuminate\Support\Facades\Mail;
use App\Mail\Welcome;
use App\Mail\Code;

class AdminController extends Controller
{
    public function signup(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'fname' => 'required|string',
                'lname' => 'required|string',
                'address' => 'required|string',
                'city' => 'required|string',
                'county' => 'required|string',
                'zip' => 'required|string',
                'email' => 'required|email',
                'phone' => 'required|string',
                'password' => 'required|string',
                'c_password' => 'required|string|same:password',
            ]);
            if( $validator->fails() ){
                return response([
                    'status' => 201,
                    'message' => 'A required field was not found',
                    'errors' => $validator->errors()->all(),
                ], 403);
            }
            $input = $request->all();
            $input['is_super'] = true;
            $input['is_admin'] = false;
            $input['is_lib'] = false;
            $input['is_fin'] = false;
            $input['is_teacher'] = false;
            $input['is_parent'] = false;
            if( User::where('email', $input['email'])->count() )
            {
                return response([
                    'status' => 201,
                    'message' => "Email address already used",
                    'errors' => [],
                ], 403);
            }
            $input['password'] = Hash::make($input['password']);
            $input['phone'] = $this->format_phone($input['phone']);
            $user = User::create($input);
            $access_token = $user->createToken('authToken')->accessToken;
            $user['token'] = $access_token;
            $user['has_setup'] = Setup::count();
            // Mail::to($input['email'])->send(new NewSignUp($input));
            return response([
                'status' => 200,
                'message' => 'Success. Account created',
                'data' => $user,
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
    public function update_info(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'fname' => 'required|string',
                'lname' => 'required|string',
                'address' => 'required|string',
                'city' => 'required|string',
                'county' => 'required|string',
                'zip' => 'required|string',
                'email' => 'required|email',
                'phone' => 'required|string',
            ]);
            if( $validator->fails() ){
                return response([
                    'status' => 201,
                    'message' => 'A required field was not found',
                    'errors' => $validator->errors()->all(),
                ], 403);
            }
            $input = $request->all();
            $input['is_super'] = true;
            $input['is_admin'] = false;
            $input['is_lib'] = false;
            $input['is_fin'] = false;
            $input['is_teacher'] = false;
            $input['is_parent'] = false;
            $input['phone'] = $this->format_phone($input['phone']);
            $instance = User::find(Auth::user()->id)->update($input);
            $user = User::find(Auth::user()->id);
            $access_token = $user->createToken('authToken')->accessToken;
            $user['token'] = $access_token;
            $user['has_setup'] = Setup::count();
            return response([
                'status' => 200,
                'message' => 'Success. Information updated',
                'data' => $user,
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
    public function update_pwd(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'password' => 'required|string',
                'c_password' => 'required|same:password',
            ]);
            if( $validator->fails() ){
                return response([
                    'status' => 201,
                    'message' => 'Passwords do not match',
                    'errors' => $validator->errors()->all(),
                ], 403);
            }
            $input = $request->all();
            $input['password'] = Hash::make($input['password']);
            $user = User::find(Auth::user()->id);
            $user->password = $input['password'];
            $user->save();
            return response([
                'status' => 200,
                'message' => 'Success. Password changed',
                'data' => $user,
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
    public function stream($file)
    {
        $filename = ('app/cls'.$file);
        return response()->download(storage_path($filename), null, [], null);
    }
    public function signin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string'
        ]);
        if( $validator->fails() ){
            return response([
                'status' => 201,
                'message' => "Invalid Email or password",
                'errors' => $validator->errors()->all(),
            ], 403);
        }
        $login = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string'
        ]);
        if( !Auth::attempt( $login ) )
        {
            return response([
                'status' => 201,
                'message' => "Invalid username or password. Try again",
                'errors' => [],
            ], 403);
        }
        $accessToken = Auth::user()->createToken('authToken')->accessToken;
        $user = Auth::user();
        $user['token'] = $accessToken;
        $user['has_setup'] = 1;
        if( Auth::user()->is_super )
        {
            $user['has_setup'] = Setup::count();
        }
        return response([
            'status' => 200,
            'message' => 'Success. logged in',
            'data' => $user,
        ], 200);
    }
    public function reqreset($email)
    {
        try{
            $user = User::where('email', $email)->count();
            if(!$user){
                return response([
                    'status' => 201,
                    'message' => "There is no user with that email. Try again or create account",
                    'errors' => [],
                ], 403); 
            }
            Pcode::where('email', $email)->update(['used' => true]);
            $code = $this->createCode(6,1);
            $data = ['email' => $email, 'code' => $code ];
            if( Pcode::create($data) )
            {
                $msg = "Hi, use Verification code " . $code . " to validate your account.";
                $data['msg'] = $msg;
                Mail::to($data['email'])->send(new Code($data));
                return response([
                    'status' => 200,
                    'message' => "A verification code has been sent to your email address.",
                    'errors' => [],
                ], 200); 
            }
            return response([
                'status' => 201,
                'message' => "Error sending email",
                'errors' => [],
            ], 403); 
            
        }catch( Exception $e){
            return response([
                'status' => 201,
                'message' => $e->getMessage(),
                'errors' => [],
            ], 403); 
        }
    }
    public function verifyreset($code, $email)
    {
        try{
            if( $this->isExpired($code) )
            {
                return response([
                    'status' => 201,
                    'message' => "Expired verification code",
                    'errors' => [],
                ], 403); 
            }
            $data = ['email' => $email, 'code' => $code ];
            $isValid = Pcode::where('email', $email)
                ->where('code', $code)
                ->where('used', false)
                ->orderBy('created_at', 'desc')
                ->first();
            if( !is_null($isValid) )
            {
                $isValid->used = true;
                User::where('email', $email)->update([ 
                    'email_verified_at' => date('Y-m-d H:i:s') 
                ]);
                $isValid->save();
                return response([
                    'status' => 200,
                    'message' => "Code verified!",
                    'data' => $data,
                ], 200); 
            }
            return response([
                'status' => 201,
                'message' => "Enter a valid verification code",
            ], 403); 
        }catch( Exception $e){
            return response([
                'status' => 201,
                'message' => "Invalid Access. No data",
            ], 403); 
        }
    }
    public function finishreset(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string',
            'password' => 'required|string',
            'c_password' => 'required|same:password'
        ]);
        if( $validator->fails() ){
            return response([
                'status' => 201,
                'message' => 'Passwords do no match',
                'errors' => $validator->errors()
            ], 403);
        }
        $email = $request->get('email');
        $user = User::where('email', $email)->first();
        if(!is_null($user)){
            $user->password = Hash::make($request->get('password'));
            $user->save();
            return response([
                'status' => 200,
                'message' => 'Password was reset, Login now',
                'data' => $user->toArray(),
            ], 200);
        }
        return response([
            'status' => 201,
            'message' => 'Data error. We could not updte password',
            'errors' => []
        ], 403);
    }
    protected function createCode($length = 20, $t = 0) {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        if( $t > 0 ){
            $characters = '0123456789';
        }
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
    protected function isExpired($code)
    {
        $cnt = Pcode::where('code', $code)
        ->where('created_at', '<=', Carbon::now()
        ->subMinutes(5)->toDateTimeString())
        ->count();
        if( $cnt > 0 )
        {
            return true;
        }
        return false;
    }
    protected function format_phone($phone)
    {
        return '254' . substr($phone, -9);
    }
    protected function aut_update_current_trm()
    {
        $now = date('Y-m-d');
        $cr = Term::where('is_current', true)->first();
        if( is_null($cr) )
        {
            $term = Term::where('start', '<=', $now)
                ->where('is_current', false)
                ->orderBy('id', 'desc')->first();
            $term->is_current = true;
            $term->save();
        }
        else
        {
            $ccurent_trm_id = $cr->id;
            $ccurent_trm_end = date('Y-m-d', strtotime($cr->end));
            if( $ccurent_trm_end < $now )
            {
                Term::find($ccurent_trm_id + 1)->update([
                    'is_current' => true,
                ]);
            }
        }
        return;
    }
}
