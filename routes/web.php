<?php

use Illuminate\Support\Facades\Route;
// use App\Http\Controllers\MainController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/service-calls', 'MainController@serviceCalls');


Route::get('/assessment-calls', 'MainController@assessmentCalls');

Route::get('/calls/{servicesCount}/{allCallCount}/{id}/{number}/{startDate}/{endDate}/{count}', 'MainController@assessmentCalls');

Route::get('/get-assessment/{id}', 'MainController@getAssessmentById');

Route::get('/get-audio-window/{id}/{assessmentId}', 'MainController@mediasenseAudioWindow');

Route::get('/get-audio-loader/{id}/{assessmentId}', 'MainController@mediasenseAudio');

Route::get('/assest-calls/{id}', 'MainController@assestCalls');



Route::get('/', 'HomeController@index');

Route::get("/report", 'HomeController@report');

Route::post('/new-assessment', 'HomeController@updateAssessment');

Route::post('/update-call', 'HomeController@updateCall');

Route::post('/update-critery', 'HomeController@updateCritery');

Route::post('/reasonable', 'HomeController@reasonable');

Route::post('/package-assessment', 'HomeController@packageAssessment');

Route::get('/calls/{id}/{startDate}/{endDate}', 'HomeController@callList');

Route::post('/add-time', 'HomeController@addTime');

// Route::get('/calls/{servicesCount}/{allCallCount}/{id}/{startDate}/{endDate}/{count}', 'HomeController@assessmentCalls');

Route::get('/assessment-detail/{id}', 'HomeController@packageDetailAssessment');

Route::get('/assessment/{id}', 'HomeController@assessment');

Route::get('/new-complaints', 'HomeController@newComplaints'); 

Route::get('/finished-assessments', 'HomeController@finishedAssessments');

Route::get('/unfinished-assessments', 'HomeController@unFinishedAssessments');

// Route::get('/assest-calls/{id}', 'HomeController@assestCalls');

Route::get('/renuw-calls/{id}', 'HomeController@renuwCalls');

Route::get('/closed-calls/{id}', 'HomeController@closedCalls');

Route::get('/research-call/{id}/{status}', 'HomeController@callResearch');

// Route::get('/get-assessment/{id}', 'HomeController@getAssessmentById');

Route::get('/packages', 'HomeController@packageList');

Route::get('/get-package/{id}', 'HomeController@getPackageById');

Route::get('/get-audio-checker/{id}/{beginDate}/{endDate}/{assessmentId}', 'HomeController@mediasenseAudioChecker');

Route::get('/get-mediasense/{id}/{beginDate}/{endDate}/{assessmentId}', 'HomeController@mediasenseAudio');

Route::get('/user-report', 'HomeController@userReport');

Route::get('/assest-statistics', 'HomeController@assestStatistics');

Route::post('/user-comment', 'HomeController@userComment');

Route::post('/call-close', 'HomeController@closeCall');

Route::get('/daily-job', 'HomeController@dailyJob');

Route::get('/call-transfer', 'HomeController@callTransfer');

Route::get('/critery', 'HomeController@criteryList');

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');



// Only Operators

Route::get('/op-assessment/{number}', 'OperatorController@assessment');

Route::get('/op-assest-calls/{id}', 'MainController@assestCalls');

Route::post('/op-insert-complaint', 'OperatorController@insertComplaint');

Route::post('/accept-assessment/{id}', 'OperatorController@acceptAssessment');

// Route::post('login', 'AuthController@login');

Route::get('/call-report', 'HomeController@callReport');
Route::get('/complaint-report', 'HomeController@complaintReport');