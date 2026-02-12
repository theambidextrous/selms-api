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

use App\Models\Student;
use App\Models\Enrollment;
use App\Models\Subject;
use App\Models\Term;
use App\Models\Fee;
use App\Models\User;
use App\Models\Form;
use App\Models\Formstream;
use App\Http\Requests\PageableRequest;
/** mail */
use Illuminate\Support\Facades\Mail;
use App\Mail\Welcome;
use App\Mail\Code;


class StudentController extends Controller
{
    
    /**
     * @OA\Post(
     *     path="/pci/api/v1/students/add",
     *     tags={"Students"},
     *     summary="Add student",
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function add(Request $request)
    {
        $file_uuid = (string) Str::uuid();
        if( !Auth::user()->is_super && !Auth::user()->is_admin )
        {
            return response([
                'status' => 400,
                'message' => 'Permission Denied. Only super admins allowed.',
                'errors' => [],
            ], 400);
        }
        try{
            $validator = Validator::make($request->all(), [
                'fname' => 'required|string',
                'lname' => 'required|string',
                'address' => 'required|string',
                'city' => 'required|string',
                'county' => 'required|string',
                'zip' => 'required|string',
                'parent' => 'string',
                'form' => 'required|string|not_in:nn',
                'stream' => 'required|string|not_in:nn',
                'expected_grad' => 'required|string',
                'gender' => 'required|string|not_in:nn',
                'dob' => 'required|string',
                'birth_cert' => 'required|string',
                'kcpe' => '',
                'huduma_no' => '',
                'nemis_no' => '',
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
            if( $request->hasfile('pic') )
            {
                $file_content = $request->file('pic');
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
                $input['pic'] = $file_content_name;
            }else
            {
                unset($input['pic']);
            }
            $input['admission'] = date("Ymd");
            $input['date_of_admission'] = date("Y-m-d");
            $user = Student::create($input)->id;
            $admission = $input['admission'] . '00' . $user;
            Student::find($user)->update(['admission' => $admission]);
            $this->enroll_default_subjects($user, $input['current_term'],  $input['form']);
            return response([
                'status' => 200,
                'message' => 'Success. Account created',
                'data' => $this->find_stud_data(),
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
     *     path="/pci/api/v1/students/edit/{id}",
     *     tags={"Students"},
     *     summary="Edit student",
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function edit(Request $request, $id)
    {
        $file_uuid = (string) Str::uuid();
        if( !Auth::user()->is_super && !Auth::user()->is_admin )
        {
            return response([
                'status' => 400,
                'message' => 'Permission Denied. Only super admins allowed.',
                'errors' => [],
            ], 400);
        }
        try{
            $validator = Validator::make($request->all(), [
                'fname' => 'required|string',
                'lname' => 'required|string',
                'address' => 'required|string',
                'city' => 'required|string',
                'county' => 'required|string',
                'zip' => 'required|string',
                'parent' => 'string',
                'form' => 'required|string|not_in:nn',
                'stream' => 'required|string|not_in:nn',
                'expected_grad' => 'required|string',
                'gender' => 'required|string|not_in:nn',
                'dob' => 'required|string',
                'birth_cert' => 'required|string',
                'kcpe' => '',
                'huduma_no' => '',
                'nemis_no' => '',
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
            if( $request->hasfile('pic') )
            {
                $file_content = $request->file('pic');
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
                $input['pic'] = $file_content_name;
            }
            if(!strlen($input['parent']))
            {
                unset($input['parent']);
            }
            Student::find($id)->update($input);
            return response([
                'status' => 200,
                'message' => 'Success. Information updated',
                'data' => $this->find_stud_data(),
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
     *     path="/pci/api/v1/students/drop/{id}",
     *     tags={"Students"},
     *     summary="Drop student",
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function drop($id)
    {
        Student::find($id)->update([ 'is_active' => false ]);
        return response([
            'status' => 200,
            'message' => "Done successfully",
            'errors' => [],
        ], 200);
    }

     /**
     * @OA\Get(
     *     path="/pci/api/v1/students/findall",
     *     tags={"Students"},
     *     summary="List students",
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function findall(PageableRequest $request) {
        $collection = Student::query()
            ->where('is_active', true) 
            ->orderBy('created_at', 'desc');

        $pageable = $request->defaults();
        $data = $collection->paginate( $pageable['size'], ['*'], 'page', $pageable['page']);
        return response([
            'status' => 200,
            'message' => "Done successfully",
            'data' => $this->format_stud_data($data->items()),
            'pagination' => [
                'current_page' => $data->currentPage(),
                'per_page' => $data->perPage(),
                'total' => $data->total(),
                'last_page' => $data->lastPage(),
            ]
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/pci/api/v1/students/findall",
     *     tags={"Students"},
     *     summary="List students",
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function findallByStream($stream, PageableRequest $request) {
        $collection = Student::query()
            ->where('is_active', true) 
            ->where('stream', $stream)
            ->orderBy('created_at', 'desc');

        $pageable = $request->defaults();
        $data = $collection->paginate( $pageable['size'], ['*'], 'page', $pageable['page']);
        return response([
            'status' => 200,
            'message' => "Done successfully",
            'data' => $this->format_stud_data($data->items()),
            'pagination' => [
                'current_page' => $data->currentPage(),
                'per_page' => $data->perPage(),
                'total' => $data->total(),
                'last_page' => $data->lastPage(),
            ]
        ], 200);
    }

       /**
     * @OA\Get(
     *     path="/pci/api/v1/students/searchall",
     *     tags={"Students"},
     *     summary="Search all students",
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function searchall()
    {
        return response([
            'status' => 200,
            'message' => "Search results...",
            'subjects' => [],
            'performance' => [],
            'fees' => [],
            'hasbooks' => [],
            'lostbooks' => [],
            'displinery' => [],
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/pci/api/v1/students/find/{id}",
     *     tags={"Students"},
     *     summary="Find one student",
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function find($id)
    {
        $data = Student::find($id);
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

    /**
     * @OA\Post(
     *     path="/pci/api/v1/students/rollover/adm",
     *     tags={"Students"},
     *     summary="Roll the student over by admission",
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function rolloveradm(Request $request)
    {
        if( !Auth::user()->is_super && !Auth::user()->is_admin )
        {
            return response([
                'status' => 400,
                'message' => 'Permission Denied. Only super admins allowed.',
                'errors' => [],
            ], 400);
        }
        $validator = Validator::make($request->all(), [
            'adm' => 'required|string',
        ]);
        if( $validator->fails() ){
            return response([
                'status' => 400,
                'message' => 'Admission number is required',
                'errors' => $validator->errors()->all(),
            ], 400);
        }
        $input = $request->all();
        return response([
            'status' => 200,
            'message' => 'Process completed ...',
            'data' => [],
        ], 200);
    }

      /**
     * @OA\Post(
     *     path="/pci/api/v1/students/rollover/form",
     *     tags={"Students"},
     *     summary="Roll the student over by form",
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function rolloverform(Request $request)
    {
        if( !Auth::user()->is_super && !Auth::user()->is_admin )
        {
            return response([
                'status' => 400,
                'message' => 'Permission Denied. Only super admins allowed.',
                'errors' => [],
            ], 400);
        }
        $validator = Validator::make($request->all(), [
            'form' => 'required|string',
        ]);
        if( $validator->fails() ){
            return response([
                'status' => 400,
                'message' => 'Form field is required',
                'errors' => $validator->errors()->all(),
            ], 400);
        }
        $input = $request->all();
        return response([
            'status' => 200,
            'message' => 'Process completed ...',
            'data' => [],
        ], 200);
    }
    protected function has_current_trm()
    {
        return Term::where('is_current', true)->count() > 0;
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
    protected function enroll_default_subjects($stud, $term, $form)
    {
        $d = Term::find($term);
        $year = $d->year;
        $subjects = $this->find_form_subjects($form);
        foreach( $subjects as $subject ):
            Enrollment::create([
                'year' => $year,
                'subject' => $subject['id'],
                'student' => $stud,
            ]);
            $fee_meta = [
                'term' => $term,
                'narration' => 'Tution fees for ' . $subject['name'], 
                'student' => $stud,
                'fee' => $subject['tution_fee'],
                'subject' => $subject['id'],
            ];
            Fee::create($fee_meta);
        endforeach;
    }
    protected function find_form_subjects($form)
    {
        $d = Subject::where('form', $form)->get();
        if( is_null($d) ) { return []; }
        return $d->toArray();
    }
    protected function extract_fee($d, $form)
    {
        if(intval($form) == 1) { return $d->f1_fee; }
        if(intval($form) == 2) { return $d->f2_fee; }
        if(intval($form) == 3) { return $d->f3_fee; }
        if(intval($form) == 4) { return $d->f4_fee; }

        return 0;
    }
    protected function find_stud_data()
    {
        $d = Student::where('is_active', true)->orderBy('id', 'desc')->get();
        if(is_null($d))
        {
            return [];
        }
        return $this->format_stud_data($d->toArray());
    }
    protected function format_stud_data($data)
    {
        $rtn = [];
        foreach( $data as $_data ):
            $p_meta = User::find($_data['parent']);
            if(!is_null( $p_meta ))
            {
                $_data['plabel'] = $p_meta->fname . ' ' . $p_meta->lname;
            }
            $f_meta = Form::find($_data['form']);
            if(!is_null( $f_meta ))
            {
                $_data['flabel'] = $f_meta->name;
            }
            $s_meta = Formstream::find($_data['stream']);
            if(!is_null( $s_meta ))
            {
                $_data['slabel'] = $s_meta->form.$s_meta->name;
            }
            if(!is_null($_data['pic']))
            {
                $_data['pic'] = route('stream', ['file' => $_data['pic']]);
            }
            array_push($rtn, $_data);
        endforeach;
        return $rtn;
    }
}
