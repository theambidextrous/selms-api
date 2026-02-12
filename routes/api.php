<?php

// header('Access-Control-Allow-Origin: *');
// header('Access-Control-Allow-Methods:  POST, GET, OPTIONS, PUT, DELETE');
// header('Access-Control-Allow-Headers:  Content-Type, X-Auth-Token, Origin, Authorization');

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\SetupController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\StatController;
use App\Http\Controllers\Api\LibrarianController;
use App\Http\Controllers\Api\AdministratorController;
use App\Http\Controllers\Api\AppMessagingController;
use App\Http\Controllers\Api\FinanceController;
use App\Http\Controllers\Api\TeacherController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\ParentController;
use App\Http\Controllers\Api\FormController;
use App\Http\Controllers\Api\FormStreamController;
use App\Http\Controllers\Api\PositionController;
use App\Http\Controllers\Api\SubjectController;
use App\Http\Controllers\Api\TranslationsController;
use App\Http\Controllers\Api\TermController;
use App\Http\Controllers\Api\TimeTableController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\EnrollmentController;
use App\Http\Controllers\Api\FeeController;
use App\Http\Controllers\Api\ScaleController;
use App\Http\Controllers\Api\ExpenseController;
use App\Http\Controllers\Api\TsubjectController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\AssessmentGroupController;
use App\Http\Controllers\Api\PerformanceController;
use App\Http\Controllers\Api\PerfByStreamController;
use App\Http\Controllers\Api\PerfByFormController;
use App\Http\Controllers\Api\LibraryCatalogueController;
use App\Http\Controllers\Api\LibraryBookController;
use App\Http\Controllers\Api\CocuActivityController;
use App\Http\Controllers\Api\StudCocuActivityController;


/** stream */
Route::prefix('/downloads')->group( function() {
    Route::get('/get/rpt/file/{file}', [ReportController::class, 'stream'])->name('stream');
});
/** users */
Route::prefix('/users')->group( function() {
    Route::post('/signup', [AdminController::class, 'signup']);
    Route::post('/signin', [AdminController::class, 'signin']);
    Route::post('/request/reset/{email}', [AdminController::class, 'reqreset']);
    Route::post('/verify/{code}/reset/{email}', [AdminController::class, 'verifyreset']);
    Route::post('/finish/reset', [AdminController::class, 'finishreset']);
    Route::middleware('auth:api')->group( function(){
        Route::post('/update/info', [AdminController::class, 'update_info']);
        Route::post('/update/pwd', [AdminController::class, 'update_pwd']);
        Route::post('/update/pic', [AdminController::class, 'update_pic']);
    });
});
/** Administrators */
Route::prefix('/administrators')->group( function() {
    Route::middleware('auth:api')->group( function(){
        Route::post('/add', [AdministratorController::class, 'add']);
        Route::post('/edit/{id}', [AdministratorController::class, 'edit']);
        Route::post('/drop/{id}', [AdministratorController::class, 'drop']);
        Route::get('/find/{id}', [AdministratorController::class, 'find']);
        Route::get('/findall', [AdministratorController::class, 'findall']);
        Route::get('/all', [AdministratorController::class, 'allUsers']);
    });
});

/** App Messages */
Route::prefix('/messages')->group( function() {
    Route::middleware('auth:api')->group( function(){
        Route::post('/approve', [AppMessagingController::class, 'approveAndSend']);
        Route::get('/findall', [AppMessagingController::class, 'findall']);
    });
});

/** Librarians */
Route::prefix('/librarians')->group( function() {
    Route::middleware('auth:api')->group( function(){
        Route::post('/add', [LibrarianController::class, 'add']);
        Route::post('/edit/{id}', [LibrarianController::class, 'edit']);
        Route::post('/drop/{id}', [LibrarianController::class, 'drop']);
        Route::get('/find/{id}', [LibrarianController::class, 'find']);
        Route::get('/findall', [LibrarianController::class, 'findall']);
    });
});
/** Finance */
Route::prefix('/finances')->group( function() {
    Route::middleware('auth:api')->group( function(){
        Route::post('/add', [FinanceController::class, 'add']);
        Route::post('/edit/{id}', [FinanceController::class, 'edit']);
        Route::post('/drop/{id}', [FinanceController::class, 'drop']);
        Route::get('/find/{id}', [FinanceController::class, 'find']);
        Route::get('/findall', [FinanceController::class, 'findall']);
    });
});
/** Teacher */
Route::prefix('/teachers')->group( function() {
    Route::middleware('auth:api')->group( function(){
        Route::post('/add', [TeacherController::class, 'add']);
        Route::post('/edit/{id}', [TeacherController::class, 'edit']);
        Route::post('/drop/{id}', [TeacherController::class, 'drop']);
        Route::get('/find/{id}', [TeacherController::class, 'find']);
        Route::get('/findall', [TeacherController::class, 'findall']);

        Route::post('/teacher/subjects', [TeacherController::class, 't_subject']);
    });
});
/** Parents */
Route::prefix('/parents')->group( function() {
    Route::middleware('auth:api')->group( function(){
        Route::post('/add', [ParentController::class, 'add']);
        Route::post('/edit/{id}', [ParentController::class, 'edit']);
        Route::post('/drop/{id}', [ParentController::class, 'drop']);
        Route::get('/find/{id}', [ParentController::class, 'find']);
        Route::get('/findall', [ParentController::class, 'findall']);
    });
});
/** Students */
Route::prefix('/students')->group( function() {
    Route::middleware('auth:api')->group( function(){
        Route::post('/add', [StudentController::class, 'add']);
        Route::post('/edit/{id}', [StudentController::class, 'edit']);
        Route::post('/drop/{id}', [StudentController::class, 'drop']);
        Route::post('/rollover/adm', [StudentController::class, 'rolloveradm']);
        Route::post('/rollover/form', [StudentController::class, 'rolloverform']);
        Route::get('/find/{id}', [StudentController::class, 'find']);
        Route::get('/findall', [StudentController::class, 'findall']);
        Route::get('/findall/{stream}', [StudentController::class, 'findallByStream']);
        Route::post('/searchall', [StudentController::class, 'searchall']);
    });
});
/** Forms */
Route::prefix('/forms')->group( function() {
    Route::middleware('auth:api')->group( function(){
        Route::post('/add', [FormController::class, 'add']);
        Route::post('/edit/{id}', [FormController::class, 'edit']);
        Route::post('/drop/{id}', [FormController::class, 'drop']);
        Route::get('/find/{id}', [FormController::class, 'find']);
        Route::get('/findall', [FormController::class, 'findall']);
    });
});
/** Forms streams*/
Route::prefix('/forms-streams')->group( function() {
    Route::middleware('auth:api')->group( function(){
        Route::post('/add', [FormStreamController::class, 'add']);
        Route::post('/edit/{id}', [FormStreamController::class, 'edit']);
        Route::post('/drop/{id}', [FormStreamController::class, 'drop']);
        Route::get('/find/{id}', [FormStreamController::class, 'find']);
        Route::get('/findall', [FormStreamController::class, 'findall']);
        Route::get('/findall/{teacher}', [FormStreamController::class, 'findallByTeacher']);
    });
});
/** Positions*/
Route::prefix('/positions')->group( function() {
    Route::middleware('auth:api')->group( function(){
        Route::post('/add', [PositionController::class, 'add']);
        Route::post('/edit/{id}', [PositionController::class, 'edit']);
        Route::post('/drop/{id}', [PositionController::class, 'drop']);
        Route::get('/find/{id}', [PositionController::class, 'find']);
        Route::get('/findall', [PositionController::class, 'findall']);
    });
});
/** Subjects */
Route::prefix('/subjects')->group( function() {
    Route::middleware('auth:api')->group( function(){
        Route::post('/add', [SubjectController::class, 'add']);
        Route::post('/edit/{id}', [SubjectController::class, 'edit']);
        Route::post('/drop/{id}', [SubjectController::class, 'drop']);
        Route::post('/unenroll/all/{id}', [SubjectController::class, 'unenroll_all']);
        Route::get('/find/{id}', [SubjectController::class, 'find']);
        Route::get('/findall', [SubjectController::class, 'findall']);
        Route::get('/by/student/{id}', [SubjectController::class, 'bystudent']);
    });
});
/** Translations */
Route::prefix('/translations')->group( function() {
    Route::get('/findall', [TranslationsController::class, 'findall']);
    Route::middleware('auth:api')->group( function(){
        Route::post('/add', [TranslationsController::class, 'add']);
        Route::post('/edit/{id}', [TranslationsController::class, 'edit']);
        Route::get('/find/{id}', [TranslationsController::class, 'find']);
    });
});
/** Terms */
Route::prefix('/terms')->group( function() {
    Route::middleware('auth:api')->group( function(){
        Route::post('/add', [TermController::class, 'add']);
        Route::post('/edit/{id}', [TermController::class, 'edit']);
        Route::post('/drop/{id}', [TermController::class, 'drop']);
        Route::get('/find/{id}', [TermController::class, 'find']);
        Route::get('/findall', [TermController::class, 'findall']);
    });
});
/** Timetable/Lessons */
Route::prefix('/timetables')->group( function() {
    Route::middleware('auth:api')->group( function(){
        Route::post('/add', [TimeTableController::class, 'add']);
        Route::post('/generate', [TimeTableController::class, 'generate']);
        Route::post('/download', [TimeTableController::class, 'download']);
        Route::post('/edit/{id}', [TimeTableController::class, 'edit']);
        Route::post('/drop/{id}', [TimeTableController::class, 'drop']);
        Route::get('/find/{id}', [TimeTableController::class, 'find']);
        Route::get('/findall', [TimeTableController::class, 'findall']);
        Route::get('/findall/{teacher}', [TimeTableController::class, 'findallByTeacher']);
    });
});
/** Attendance */
Route::prefix('/attendance')->group( function() {
    Route::middleware('auth:api')->group( function(){
        Route::post('/add', [AttendanceController::class, 'add']);
        Route::post('/edit/{id}', [AttendanceController::class, 'edit']);
        Route::post('/drop/{id}', [AttendanceController::class, 'drop']);
        Route::get('/find/{id}', [AttendanceController::class, 'find']);
        Route::get('/findall', [AttendanceController::class, 'findall']);
        Route::get('/findall-by-stream/{stream}', [AttendanceController::class, 'findallByStream']);
    });
});
/** Enrollments - student subject */
Route::prefix('/enrollments')->group( function() {
    Route::middleware('auth:api')->group( function(){
        Route::post('/add', [EnrollmentController::class, 'add']);
        Route::post('/unenroll', [EnrollmentController::class, 'unenroll']);
        Route::post('/edit/{id}', [EnrollmentController::class, 'edit']);
        Route::post('/drop/{id}', [EnrollmentController::class, 'drop']);
        Route::get('/find/{id}', [EnrollmentController::class, 'find']);
        Route::get('/findall', [EnrollmentController::class, 'findall']);
        Route::post('/searchall', [EnrollmentController::class, 'searchall']);
    });
});
/** Fees - student fees */
Route::prefix('/fees')->group( function() {
    Route::middleware('auth:api')->group( function(){
        Route::post('/add', [FeeController::class, 'add']);
        Route::post('/edit/{id}', [FeeController::class, 'edit']);
        Route::post('/drop/{id}', [FeeController::class, 'drop']);
        Route::get('/find/{id}', [FeeController::class, 'find']);
        Route::get('/findall', [FeeController::class, 'findall']);
    });
});
/** Scales - form grading */
Route::prefix('/scales')->group( function() {
    Route::middleware('auth:api')->group( function(){
        Route::post('/add', [ScaleController::class, 'add']);
        Route::post('/edit/{id}', [ScaleController::class, 'edit']);
        Route::post('/drop/{id}', [ScaleController::class, 'drop']);
        Route::get('/find/{id}', [ScaleController::class, 'find']);
        Route::get('/findall', [ScaleController::class, 'findall']);
    });
});
/** Expenses */
Route::prefix('/expenses')->group( function() {
    Route::middleware('auth:api')->group( function(){
        Route::post('/add', [ExpenseController::class, 'add']);
        Route::post('/edit/{id}', [ExpenseController::class, 'edit']);
        Route::post('/drop/{id}', [ExpenseController::class, 'drop']);
        Route::get('/find/{id}', [ExpenseController::class, 'find']);
        Route::get('/findall', [ExpenseController::class, 'findall']);
    });
});
/** Teacher subjects */
Route::prefix('/tsubjects')->group( function() {
    Route::middleware('auth:api')->group( function(){
        Route::post('/add', [TsubjectController::class, 'add']);
        Route::post('/edit/{id}', [TsubjectController::class, 'edit']);
        Route::post('/drop/{id}', [TsubjectController::class, 'drop']);
        Route::get('/find/{id}', [TsubjectController::class, 'find']);
        Route::get('/findall', [TsubjectController::class, 'findall']);
        Route::get('/findall/{teacherId}', [TsubjectController::class, 'findallByTeacher']);
    });
});
/** Payments */
Route::prefix('/payments')->group( function() {
    Route::middleware('auth:api')->group( function(){
        Route::post('/add', [PaymentController::class, 'add']);
        Route::post('/edit/{id}', [PaymentController::class, 'edit']);
        Route::post('/drop/{id}', [PaymentController::class, 'drop']);
        Route::get('/find/{id}', [PaymentController::class, 'find']);
        Route::get('/findall', [PaymentController::class, 'findall']);
    });
});
/** Assessmet groups */
Route::prefix('/assessments')->group( function() {
    Route::middleware('auth:api')->group( function(){
        Route::post('/add', [AssessmentGroupController::class, 'add']);
        Route::post('/edit/{id}', [AssessmentGroupController::class, 'edit']);
        Route::post('/drop/{id}', [AssessmentGroupController::class, 'drop']);
        Route::get('/find/{id}', [AssessmentGroupController::class, 'find']);
        Route::get('/findall', [AssessmentGroupController::class, 'findall']);
    });
});
/** Performance */
Route::prefix('/performances')->group( function() {
    Route::middleware('auth:api')->group( function(){
        Route::post('/add', [PerformanceController::class, 'add']);
        Route::post('/edit/{id}', [PerformanceController::class, 'edit']);
        Route::post('/drop/{id}', [PerformanceController::class, 'drop']);
        Route::get('/find/{id}', [PerformanceController::class, 'find']);
        Route::get('/findall', [PerformanceController::class, 'findall']);
        Route::post('/findby/student', [PerformanceController::class, 'findbystudent']);
        Route::post('/downloadby/student', [PerformanceController::class, 'downloadbystudent']);
        
        Route::post('/findby/stream', [PerfByStreamController::class, 'findbystream']);
        Route::post('/download/stream/list', [PerfByStreamController::class, 'downloadlist']);
        Route::post('/download/stream/reports', [PerfByStreamController::class, 'downloadreports']);

        Route::post('/findby/form', [PerfByFormController::class, 'findbyform']);
        Route::post('/download/form/list', [PerfByFormController::class, 'downloadlist']);
        Route::post('/download/form/reports', [PerfByFormController::class, 'downloadreports']);
    });
});
/** Library - Catalogue */
Route::prefix('/library/catalogues')->group( function() {
    Route::middleware('auth:api')->group( function(){
        Route::post('/add', [LibraryCatalogueController::class, 'add']);
        Route::post('/edit/{id}', [LibraryCatalogueController::class, 'edit']);
        Route::post('/drop/{id}', [LibraryCatalogueController::class, 'drop']);
        Route::get('/find/{id}', [LibraryCatalogueController::class, 'find']);
        Route::get('/findall', [LibraryCatalogueController::class, 'findall']);
    });
});
/** Library - Books*/
Route::prefix('/library/books')->group( function() {
    Route::middleware('auth:api')->group( function(){
        Route::post('/add', [LibraryBookController::class, 'add']);
        Route::post('/edit/{id}', [LibraryBookController::class, 'edit']);
        Route::post('/drop/{id}', [LibraryBookController::class, 'drop']);
        Route::get('/find/{id}', [LibraryBookController::class, 'find']);
        Route::get('/findall', [LibraryBookController::class, 'findall']);
        Route::post('/lend', [LibraryBookController::class, 'blend']);
        Route::post('/return', [LibraryBookController::class, 'breturn']);
        Route::post('/lost', [LibraryBookController::class, 'blost']);
    });
});

/** Activities - Cocurricular*/
Route::prefix('/activities')->group( function() {
    Route::middleware('auth:api')->group( function(){
        Route::post('/add', [CocuActivityController::class, 'add']);
        Route::post('/edit/{id}', [CocuActivityController::class, 'edit']);
        Route::post('/drop/{id}', [CocuActivityController::class, 'drop']);
        Route::get('/find/{id}', [CocuActivityController::class, 'find']);
        Route::get('/findall', [CocuActivityController::class, 'findall']);
        Route::prefix('/student')->group( function() {
            Route::post('/add', [StudCocuActivityController::class, 'add']);
            Route::post('/edit/{id}', [StudCocuActivityController::class, 'edit']);
            Route::post('/drop/{id}', [StudCocuActivityController::class, 'drop']);
            Route::get('/findall', [StudCocuActivityController::class, 'findall']);
        });
    });
});

/** Setup */
Route::middleware(['auth:api'])->group( function(){
    Route::prefix('/setups')->group( function() {
        Route::post('/set', [SetupController::class, 'set']);
        Route::get('/find', [SetupController::class, 'find']);
        Route::get('/refresh', [SetupController::class, 'refresh']);
    });
});

/** statistics */
Route::middleware(['auth:api'])->group( function(){
    Route::prefix('/statistics')->group( function() {
        Route::get('/dashboard', [StatController::class, 'dashboard']);
    });
});

/** fallback */
Route::fallback(function () {
    return response()->json(['status' => 404,'softbct_error' => 'Not Found!'], 404);
});
Route::get('/', function (Request $request) {
    return response(['status' => 499, 'message' => 'point of no return'], 403);
});
Route::fallback(function () {
    return response(['status'=> 499, 'message' => 'oops! Congrats! you\'ve reached point of no return'], 403);
});