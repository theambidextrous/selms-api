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

use App\Models\Timetable;
use App\Models\Term;
use App\Models\Formstream;
use App\Models\User;
use App\Models\Subject;
use App\Models\Setup;
/** mail */
use Illuminate\Support\Facades\Mail;
use App\Mail\Welcome;
use App\Mail\Code;

class TimeTableController extends Controller
{
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
                // 'current_term' => 'required|string',
                'day' => 'required|string|not_in:nn',
                'date' => 'required|string',
                'time' => 'required|string',
                'stream' => 'required|string|not_in:nn',
                'teacher' => 'required|string|not_in:nn',
                'subject' => 'required|string|not_in:nn',
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
            $input['current_term'] = $this->find_current_trm();
            $this->validate_stream_subject($input);
            $this->validate_stream_class_clash($input);
            if( strtoupper($input['day']) != strtoupper(date('l', strtotime($input['date']))) )
            {
                return response([
                    'status' => 400,
                    'message' => 'Error. ' . $input['date'] . ' is not on a ' . $input['day'],
                    'errors' => [],
                ], 400);
            }
            $input['datetime'] = $this->generate_datetime($input);
            Timetable::create($input);
            return response([
                'status' => 200,
                'message' => 'Success. Done',
                'data' => $this->find_ttable_data(),
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
        }catch (Exception $e) {
            return response([
                'status' => 400,
                'message' => $e->getMessage(),
                'errors' => [],
            ], 400);
        }
    }
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
                'day' => 'required|string|not_in:nn',
                'date' => 'required|string',
                'time' => 'required|string',
                'stream' => 'required|string|not_in:nn',
                'teacher' => 'required|string|not_in:nn',
                'subject' => 'required|string|not_in:nn',
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
            $input['current_term'] = $this->find_current_trm();
            $this->validate_stream_subject($input);
            $this->validate_stream_class_clash($input);
            if( strtoupper($input['day']) != strtoupper(date('l', strtotime($input['date']))) )
            {
                return response([
                    'status' => 400,
                    'message' => 'Error. ' . $input['date'] . ' is not on a ' . $input['day'],
                    'errors' => [],
                ], 400);
            }
            $input['datetime'] = $this->generate_datetime($input);
            Timetable::find($id)->update($input);
            return response([
                'status' => 200,
                'message' => 'Success. Information updated',
                'data' => $this->find_ttable_data(),
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
        }catch (Exception $e) {
            return response([
                'status' => 400,
                'message' => $e->getMessage(),
                'errors' => [],
            ], 400);
        }
    }
    public function drop($id)
    {
        Timetable::find($id)->delete();
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
            'data' => $this->find_ttable_data(),
        ], 200);
    }
    public function find($id)
    {
        $data = Timetable::find($id);
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
    public function generate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'stream' => 'required|string|not_in:nn',
        ]);
        if( $validator->fails() ){
            return response([
                'status' => 400,
                'message' => 'Select stream.',
                'errors' => $validator->errors()->all(),
            ], 400);
        }
        $input = $request->all();
        $input['term'] = $this->find_current_trm();
        return response([
            'status' => 200,
            'message' => 'Timetable results...',
            'data' => $this->find_str_lessons($input),
        ], 200);
    }
    public function download(Request $request)
    {
        $uuid_string = (string)Str::uuid() . '.pdf';
        $validator = Validator::make($request->all(), [
            'stream' => 'required|string|not_in:nn',
        ]);
        if( $validator->fails() ){
            return response([
                'status' => 400,
                'message' => 'Select stream.',
                'errors' => $validator->errors()->all(),
            ], 400);
        }
        $input = $request->all();
        $input['term'] = $this->find_current_trm();
        $stream_meta = Formstream::find($input['stream']);
        $teacher_meta = User::find($stream_meta->class_teacher);
        $pdf_data = [
            'timetable' => $this->find_str_lessons($input),
            'setup' => $this->find_setup(),
            'stream' => 'Form ' . $stream_meta->form . ' ' . $stream_meta->name,
            'teacher' => $this->format_tch_name($teacher_meta),
        ];
        $filename = ('app/cls/trt/content/' . $uuid_string);
        // PDF::loadView('reports.streamtimetable', $pdf_data)->save(storage_path($filename));
        PDF::loadView('reports.streamtimetable', $pdf_data, [], ['orientation' => 'L'])->save(storage_path($filename));
        return response([
            'status' => 200,
            'message' => 'timetable generated',
            'fileurl' => route('stream', ['file' => $uuid_string]),
            'errors' => [],
        ], 200);
    }
    protected function format_tch_name($teacher_meta)
    {
        $fn = strtoupper(substr($teacher_meta->fname, 0, 1)) . '. ';
        $ln = ucwords(strtolower(explode(' ', $teacher_meta->lname)[0]));
        return $fn.$ln;
    }
    protected function find_str_lessons($input)
    {
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        $rtn = [];
        foreach( $days as $_day ):
            $entry = [
                'day' => $_day,
                'lessons' => []
            ];
            $timedata = Timetable::where('stream', $input['stream'])
                ->where('current_term', $input['term'])
                ->where('day', $_day)->orderBy('time', 'asc')->get();
            if(!is_null($timedata))
            {
                $entry['lessons'] = $this->format_ttable_data($timedata->toArray());
            }
            array_push($rtn, $entry);
        endforeach;
        return $rtn;
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
    protected function find_ttable_data()
    {
        $d = Timetable::where('id', '!=', 0)->orderBy('date', 'desc')->get();
        if(is_null($d))
        {
            return [];
        }
        return $this->format_ttable_data($d->toArray());
    }
    protected function format_ttable_data($data)
    {
        $rtn = [];
        foreach( $data as $_data ):
            $t_meta = User::find($_data['teacher']);
            if(!is_null( $t_meta ))
            {
                $_data['tlabel'] = $t_meta->fname . ' ' . $t_meta->lname;
            }
            $sub_meta = Subject::find($_data['subject']);
            if(!is_null( $sub_meta ))
            {
                $_data['sublabel'] = $sub_meta->name;
            }
            $s_meta = Formstream::find($_data['stream']);
            if(!is_null( $s_meta ))
            {
                $_data['slabel'] = $s_meta->form.$s_meta->name;
            }
            array_push($rtn, $_data);
        endforeach;
        return $rtn;
    }
    protected function validate_stream_subject($data)
    {
        $subject = Subject::find($data['subject']);
        $sub_form = intval($subject->form);

        $stream = Formstream::find($data['stream']);
        $str_form = intval($stream->form);

        if($str_form != $sub_form)
        {
            throw new \Exception('The subject you selected does not belong to Form ' . $str_form.$stream->name);
        }
        return;
    }
    protected function validate_stream_class_clash($data)
    {
        $count = Timetable::where('day', $data['day'])
            ->where('time', $data['time'])
            ->where('stream', $data['stream'])->count();
        if( $count )
        {
            $stream = Formstream::find($data['stream']);
            throw new \Exception('Lessons Clash. Form ' . $stream->form.$stream->name . ' has another class at this time on ' . $data['day'] . 's.');
        }
        return;
    }
    protected function generate_datetime($data)
    {
        $str = date('YmdHi', strtotime($data['date'].' ' . $data['time']));
        return $str;
    }
    protected function find_setup()
    {
        $s = Setup::where('id', '!=', 0)->first();
        if(!is_null($s))
        {
            return $s->toArray();
        }
        return [
            'school' => null,
            'address' => null,
            'city' => null,
            'county' => null,
            'zip' => null,
            'email' => null,
            'phone' => null,
            'website' => null,
            'motto' => null,
            'logo' => null,
        ];
    }
}
