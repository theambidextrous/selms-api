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

use App\Models\Setup;
use App\Models\User;

class SetupController extends Controller
{
    public function set(Request $req)
    {
        $file_uuid = (string) Str::uuid();
        $validator = Validator::make($req->all(), [
            'school' => 'required|string',
            'address' => 'required|string',
            'city' => 'required|string',
            'county' => 'required|string|not_in:nn',
            'zip' => 'required|string',
            'email' => 'required|email',
            'phone' => 'required|string',
            'website' => 'required|string',
            'motto' => 'required|string',
        ]);
        if( $validator->fails() ){
            return response([
                'status' => 400,
                'message' => 'Error: Invalid field(s) detected',
                'errors' => $validator->errors()->all(),
            ], 400);
        }
        $input = $req->all();
        $input['phone'] = $this->format_phone($input['phone']);
        if( $req->hasfile('logo') )
        {
            $file_content = $req->file('logo');
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
            $input['logo'] = $file_content_name;
        }
        /** user meta */
        $accessToken = Auth::user()->createToken('authToken')->accessToken;
        $user = Auth::user();
        $user['token'] = $accessToken;
        $user['has_setup'] = Setup::count();
        /** end meta */
        if( Setup::count() > 0 )
        {
            $s = Setup::where('id', '!=', 0)->first();
            $s->school = $input['school'];
            $s->address = $input['address'];
            $s->city = $input['city'];
            $s->county = $input['county'];
            $s->zip = $input['zip'];
            $s->email = $input['email'];
            $s->phone = $input['phone'];
            $s->website = $input['website'];
            $s->motto = $input['motto'];
            $s->logo = $input['logo'];
            $s->save();
            return response([
                'status' => 200,
                'message' => 'Setup info updated successfully',
                'id' => $s->id,
                'udata' => $user,
            ], 200);
        }
        else
        {
            $created = Setup::create($input)->id;
            return response([
                'status' => 200,
                'message' => 'Setup info updated successfully',
                'id' => $created,
                'udata' => $user,
            ], 200);
        }
    }
    public function find()
    {
        $data = [
            'school' => '',
            'address' => '',
            'city' => '',
            'county' => 'nn',
            'zip' => '',
            'email' => '',
            'phone' => '',
            'website' => '',
            'motto' => '',
            'logo' => '',
        ];
        $d = Setup::where('id', '!=', 0)->first();
        if(!is_null($d))
        {
            $data = $this->format_setup_data($d->toArray());
        }
        return response([
            'status' => 200,
            'message' => 'Setup info found',
            'data' => $data,
        ], 200);
    }

    public function refresh()
    {
        $accessToken = Auth::user()->createToken('authToken')->accessToken;
        $user = Auth::user();
        $user['token'] = $accessToken;
        $user['has_setup'] = Setup::count();
        return response([
            'status' => 200,
            'message' => 'Success. new data',
            'data' => $user,
        ], 200);
    }
    protected function format_setup_data($data)
    {
        $data['logo'] = route('stream', ['file' => $data['logo']]);
        return $data;
    }
    protected function format_phone($phone)
    {
        return '254' . substr($phone, -9);
    }
}
