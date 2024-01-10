<?php

namespace App\Http\Controllers;

use Symfony\Component\HttpFoundation\StreamedResponse;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\DB;

use Illuminate\Support\Str;
use App\User;

use Illuminate\Support\Facades\Cookie;

use Exception;

use App\UserReport;

use App\Assessment;

use App\PackageAssessment;

use App\AssessmentCriterias;

use DateTime;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
         
        // $this->selected = [40, 45, 46, 47, 51, 59, 66, 90, 94, 127, 128, 129, 130, 131, 132, 134, 136];
        $this->selected = [38, 39, 40, 41, 45, 46, 47, 48, 49, 51, 57, 58, 59, 66, 67, 70, 74, 75, 76, 78, 79, 80, 88, 89, 90, 91 ,92, 94, 95,
        121, 122, 123, 124, 125, 127, 128, 129, 130, 131, 132, 133, 134, 135, 136, 138, 139, 140, 143, 155, 53, 163, 165, 166, 167, 168, 202, 236,
        237, 238, 239, 241, 242, 243, 244, 245, 246, 250, 254, 255, 258, 259, 261, 262, 270, 272, 275, 276, 305, 306, 337, 338, 339, 340, 341, 346, 
        353, 355, 356, 357, 358, 359, 360, 362, 368, 369, 371, 372, 373, 378, 379, 381, 382, 384, 387, 392, 393, 395, 396, 397, 400, 403, 405, 406, 418, 424,
        426, 428, 431, 438, 439, 441, 443, 447, 449, 456, 460, 470, 471, 474, 492, 496, 497, 498, 499, 500, 501, 506];

        // $this->checkCookie();
    }

    public function checkCookie() { 

        if(Auth::user() !==null && Auth::user()->role===0){
            $user = User::find(Auth::user()->id); 
            if(!(Cookie::get('checked') !== null && Cookie::get('checked')==$user->remember_token)){
                Auth::logout();
                return redirect('/login');
            }
        } 

    }

     
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    { 

        $this->middleware('auth'); 

        date_default_timezone_set("Asia/Baku");

        $currentDateTime = date("Y-m-d H:i:s"); 
        
        $operators = DB::select('select o.id, o.full_name, o.user_name, o.phone_number from operators o where o.asan_id = 1 and o.type =?', [isset(Auth::user()->supervisor_id) ? Auth::user()->supervisor_id : 0]);
        
        $callTypes = DB::select('select * from call_types where status = 1');

        $criterias = DB::select('select c.id, c.name, c.score, c.max_score as maxScore from criterias c');
       
        foreach($operators as $operator) {

            $assessed = DB::select("SELECT DISTINCT id FROM assessment  
                WHERE supervisor_id = ".(Auth::user() ? Auth::user()->id : 0)." and user_id=$operator->id and DATE_FORMAT(appraiser_date, '%Y %m') = DATE_FORMAT('$currentDateTime', '%Y %m') ORDER BY appraiser_date desc"); 

            $assessment = DB::select('select a.id, a.begin_date as beginDate, a.calls_count as callCount, a.supervisor_id as userId, a.end_date as endDate, a.is_active from assessment a where a.user_id='.$operator->id.' ORDER BY a.id DESC LIMIT 1');
            $operator->beginDate = count($assessment) > 0 ? $assessment[0]->beginDate : 'Qiymətləndirmə edilməyib'; 
            $operator->endDate = count($assessment) > 0 ? $assessment[0]->endDate : '';
            $operator->is_active = count($assessment) > 0 ? $assessment[0]->is_active : null; 
            $operator->assessed = count($assessed) === 0;
            
            $operator->assessmentId  = count($assessment) > 0 ? $assessment[0]->id : 0; 
  
            $operator->assestUser = count($assessment) > 0 ? $assessment[0]->userId : 0;
 
            $callCount = count($assessment) > 0 ? $assessment[0]->callCount : 0;
     
            $selectedCallCount = null;
            $complaintCount = 0;
           
            if(count($assessment) > 0) {

                $selectedCallCount = DB::select('select a.id from assessment_criterias a where a.is_assessment=1 and a.assessment_id = ?', [$assessment[0]->id]);
            
                // $checkComplaint = DB::select('select a.id from assessment_criterias a inner join complaint_criterias c on a.id = c.assessment_criterias_id where a.criterias is not null and a.assessment_id = ?', [$assessment[0]->id]);

                // $complaintCount = count($checkComplaint);
                $selectedCallCount = count($selectedCallCount);

            }  
            
            $operator->complaint = $complaintCount;
            $operator->callCount = $callCount;
            $operator->callAssest = $selectedCallCount;
        } 

        $data = ['operators' => $operators, 'criterias' => $criterias, 'menu' => $this->menuStatistics(), 'types'=>$callTypes]; 
         
        return view('index')->with('data',$data); 
    }

    public function menuStatistics() {
 
        $operators = DB::select('select o.id, o.full_name from operators o where o.asan_id = 1 and o.type =?',[isset(Auth::user()->supervisor_id) ? Auth::user()->supervisor_id : 0]);

        $finishedAssessments = 0;

        $unFinishedAssessments = 0;

        $complaintSum = 0;

        $sql = '';

        if(Auth::user() && Auth::user()->role==1) {
            $sql = ' c.curator_status = 1 and  ';
        } else if(Auth::user() && Auth::user()->role==2) {
            $sql = ' c.leading_status = 2 and  ';
        } else {
            $sql = ' c.curator_status = 1 and c.leading_status = 2 and ';
        }
        
        foreach($operators as $operator) { 
            $assessments = DB::select('select a.id, a.begin_date as beginDate, a.end_date as endDate, a.is_active from assessment a where a.user_id='.$operator->id.' and a.is_package = 0');
            $finishedAssessments += count(DB::select('select a.id, a.begin_date as beginDate, a.end_date as endDate, a.is_active from assessment a where a.user_id='.$operator->id.' and a.is_active = 1'));
            $unFinishedAssessments += count($assessments);
        
            if(count($assessments)>0) {
                foreach($assessments as $assessment) {
                    $checkComplaint = DB::select('select a.id from assessment_criterias a inner join complaint_criterias c on a.id = c.assessment_criterias_id where '.$sql.' a.criterias is not null and a.assessment_id = ?', [$assessment->id]);
                    $complaintSum += count($checkComplaint);
                }
            }
        
        }

        return ['finishedAssessments' => $finishedAssessments, 'unFinishedAssessments' => $unFinishedAssessments, 'complaint'=> $complaintSum];
    }

    public function newComplaints() {
        $this->checkCookie();
        $this->middleware('auth');

        $operators = DB::select('select o.id, o.full_name from operators o where o.asan_id = 1');
                 
        $complaints = [];
        $complaintSum = 0;

        $finishedComplaints = [];
        $finishedComplaintSum = 0;

        $unFinishedComplaints = [];
        $unFinishedComplaintSum = 0;
        
        foreach($operators as $operator) {

            $assessment = DB::select('select a.id, a.begin_date as beginDate, a.end_date as endDate, a.is_active from assessment a where a.is_package = 0 and a.user_id='.$operator->id.' ORDER BY a.id DESC LIMIT 5');
            

            foreach($assessment as $key => $userComplaint) {

                $operator->beginDate = count($assessment) > 0 ? $assessment[$key]->beginDate : 'Qiymətləndirmə edilməyib'; 
            
                if(isset($assessment[$key]->beginDate)) {

                    $operator->endDate = count($assessment) > 0 ? $assessment[$key]->endDate : '';
                    $operator->is_active = count($assessment) > 0 ? $assessment[$key]->is_active : null; 
                    $operator->assessmentId  = count($assessment) > 0 ? $assessment[$key]->id : 0; 
                    $callCount = null;
                    $selectedCallCount = null;
                    $complaintCount = 0;
                    $finishedComplaintCount = 0;
                    $unFinishedComplaintCount = 0;

                    if(count($assessment) > 0) { 
                        
                        $callCount = DB::select('select a.id from assessment_criterias a where a.assessment_id = ?', [$assessment[$key]->id]);
                        $selectedCallCount = DB::select('select a.id from assessment_criterias a where a.is_assessment=1 and a.assessment_id = ?', [$assessment[$key]->id]);                    
                        $selectedCallCount = count($selectedCallCount);
                        
                        $callCount = count($callCount); 

                        $complaintCount = count(DB::select('select a.id from assessment_criterias a inner join complaint_criterias c on a.id = c.assessment_criterias_id where a.criterias is not null and a.assessment_id = ?', [$assessment[$key]->id]));
                        $complaintSum += $complaintCount; 

                        $finishedComplaintCount = count(DB::select('select a.id from assessment_criterias a inner join complaint_criterias c on a.id = c.assessment_criterias_id where a.criterias is not null and a.assessment_id = ? and (c.curator_status = 2 or c.leading_status = 3)', [$assessment[$key]->id]));
                        $finishedComplaintSum += $finishedComplaintCount; 

                        $unFinishedComplaintCount = count(DB::select('select a.id from assessment_criterias a inner join complaint_criterias c on a.id = c.assessment_criterias_id where a.criterias is not null and a.assessment_id = ? and (c.curator_status = 1 or c.leading_status = 2)', [$assessment[$key]->id]));
                        $unFinishedComplaintSum += $unFinishedComplaintCount; 

                    }  
                    
                    $operator->complaint = $complaintCount;
                    // $operator->finishedComplaint = $finishedComplaints;
                    $operator->callCount = $callCount;
                    $operator->callAssest = $selectedCallCount;

                    if($complaintCount>0) {
                        array_push($complaints, $operator);
                    } 

                    if($finishedComplaintCount>0) {
                        array_push($finishedComplaints, $operator);
                    } 

                    if($unFinishedComplaintCount > 0) {
                        array_push($unFinishedComplaints, $operator);
                    }

                }
            }
        }

        $data = [
            'allComplaints' => $complaints, 
            'complaintSum'=>$complaintSum, 

            'finishedComplaints' => $finishedComplaints, 
            'finishedComplaintSum'=>$finishedComplaintSum,

            'unFinishedComplaints' => $unFinishedComplaints, 
            'unFinishedComplaintSum'=>$unFinishedComplaintSum,

            'menu' => $this->menuStatistics()
        ]; 
 
        return view('newComplaints')->with('data', $data);
    }

    public function finishedAssessments() {
        $this->middleware('auth'); 
        $operators = DB::select('select o.id, o.full_name from operators o where o.asan_id = 1 and o.type =?',[isset(Auth::user()->supervisor_id) ? Auth::user()->supervisor_id : 0]);

        $finishedAssessments = [];

       
        foreach($operators as $operator) { 
            
            $selectedAssessments = DB::select('select a.id, a.begin_date as beginDate, a.score_count, a.end_date as endDate, a.is_active from assessment a where a.user_id='.$operator->id.' and a.is_active = 1 order by id desc LIMIT 1');
            
            if(count($selectedAssessments)>0) { 
                $selectedAssessments[0]->full_name = $operator->full_name;
                array_push($finishedAssessments, $selectedAssessments[0]);
            } 

        }

        $data = [
            'assessments' => $finishedAssessments,
            'finished' => true,
            'menu' => $this->menuStatistics()
        ];
         
        return view('finishedAssessment')->with('data', $data);

    }

    public function renuwCalls($id) {
        $this->checkCookie();
        $assessmentCritery = DB::select("select renew_calls, closed_calls, assessment_id as assessmentId from assessment_criterias where id=".$id);

        $renew = json_decode($assessmentCritery[0]->renew_calls);

        $closed = json_decode($assessmentCritery[0]->closed_calls);

        $assessmentId = json_decode($assessmentCritery[0]->assessmentId);

        $archives_renew = [];
         
        if($renew !== null)
        foreach($renew as $call) {
            $item = DB::select("select m_c.id as id, m_c.citizen_number, m_c.start_date, m_c.end_date, o.name  as organ, s.service as service
            from monthly_calls m_c inner join monthly_applications m_a on m_c.id=m_a.call_id 
            inner join organizations o on m_a.organ_id = o.id inner join organization_services s on m_a.service_id = s.id 
            where m_c.id=".$call)[0];
            array_push($archives_renew, $item); 
        }

        $data = [
            'renew' => $archives_renew, 
            'menu' => $this->menuStatistics(),
            'assessmentId' => $id,
            'status' => true
        ];
 
        return view('archiveCalls')->with('data', $data);
    }

    public function closedCalls($id) {
        $this->checkCookie();
        $assessmentCritery = DB::select("select renew_calls, closed_calls, assessment_id as assessmentId from assessment_criterias where id=".$id);

        $renew = json_decode($assessmentCritery[0]->renew_calls);

        $closed = json_decode($assessmentCritery[0]->closed_calls);

        $assessmentId = json_decode($assessmentCritery[0]->assessmentId);
 
        $archives_closed = [];

        if($closed !== null)
        foreach($closed as $call) {
            $item = DB::select("select m_c.id as id, m_c.citizen_number, m_c.start_date, m_c.end_date, o.name  as organ, s.service as service
            from monthly_calls m_c inner join monthly_applications m_a on m_c.id=m_a.call_id 
            inner join organizations o on m_a.organ_id = o.id inner join organization_services s on m_a.service_id = s.id 
            where m_c.id=".$call)[0];
            array_push($archives_closed, $item); 
        }

        $data = [
            'closed' => $archives_closed,
            'menu' => $this->menuStatistics(),
            'assessmentId' => $id,
            'status' => false
        ];
 
        return view('archiveCalls')->with('data', $data);
    }

    public function unFinishedAssessments() {

        $this->middleware('auth');
        $this->checkCookie();
        $operators = DB::select('select o.id, o.full_name from operators o where o.asan_id = 1 and o.type =?',[isset(Auth::user()->supervisor_id) ? Auth::user()->supervisor_id : 0]);

        $unFinishedAssessments = [];

        foreach($operators as $operator) { 
            
            $selectedAssessments = DB::select('select a.id, a.begin_date as beginDate, a.end_date as endDate, a.is_active, a.score_count from assessment a where a.user_id='.$operator->id.' and a.is_package = 0 order by id desc LIMIT 1');
            
            if(count($selectedAssessments)>0) { 
                $selectedAssessments[0]->full_name = $operator->full_name;
                $selectedAssessments[0]->operatorId = $operator->id; 
                
                array_push($unFinishedAssessments, $selectedAssessments[0]);
            } 

        }

        $data = [
            'assessments' => $unFinishedAssessments,
            'finished' => false, 
            'menu' => $this->menuStatistics()
        ]; 
        return view('finishedAssessment')->with('data', $data);
    }

    public function assestCalls($id) {

        $this->middleware('auth');
        $this->checkCookie();
        $calls = $this->getAssessmentById($id);

        $criterias = DB::select('select c.id, c.name, c.score from criterias c');

        $callTypeList = DB::select('select * from call_types');

        $operator = DB::select('SELECT o.full_name FROM assessment a INNER JOIN operators o on a.user_id=o.id WHERE a.id=?',[$id]);
        $date = DB::select('SELECT a.begin_date as beginDate, a.is_accept as accept, a.end_date as endDate FROM assessment a WHERE a.id=?',[$id]);

        $assessmentTimeSum = 0;
        $playTimeSum = 0;
        $notListenTime = 0;
        $specialTimeSum = 0;
        $audioAllTimes = 0; 

        $renuwCount = 0;
        $closedCount = 0;
 
        foreach($calls['calls'] as $item) {

            $callTypes = json_decode($item->wrongSelection);
            $type = ""; 
            if ($callTypes !== 0 && $callTypes !== 1 && $callTypes !== null) {
          
                foreach($callTypeList as $callType) { 
                    
                    if (
                        in_array(strval($callType->id), $callTypes) 
                    ) { 
                        $type .= $callType->name . "; ";   
                    } 
                } 

                $item->status = strlen($type) > 0
                        ? substr($type, 0, strlen($type) - 2)
                        : $type;
            } else {
                $item->status =  $item->is_assessment ==1 ? "Qiymətləndirilib" : "Qiymətləndirilməyib";
            }
 
            $renuwCount += isset($item->renew_calls) ? count(json_decode($item->renew_calls)) : 0;
            
            $closedCount += isset($item->closed_calls) ? count(json_decode($item->closed_calls)) : 0;

            $assessmentTimeSum += $item->assessmentTime ? $item->assessmentTime : 0;
            $playTimeSum += $item->playTime ? $item->playTime : 0;
            $notListenTime += $item->unPlayTime ? $item->unPlayTime : 0;
            $specialTimeSum += $item->specialTime ? $item->specialTime : 0;
            $audioAllTimes += $item->audioTime ? $item->audioTime : 0;
            $item->complaint = count(DB::select('SELECT c.id FROM complaint_criterias c WHERE c.assessment_criterias_id=?',[$item->assessmentId]));     
        }

        $times = [
            'assessmentTimeSum'=>$this->setHour($assessmentTimeSum), 
            'playTimeSum' => $this->setHour($playTimeSum), 
            'notListenTime' => $this->setHour($notListenTime), 
            'specialTimeSum' => $this->setHour($specialTimeSum), 
            'audioAllTimes' => $this->setHour($audioAllTimes)
        ];
 
        $data = [
            'assessment'=> $calls, 
            'id'=> $id,
            'renuwCount' => $renuwCount,
            'closedCount' => $closedCount,
            'criterias' => $criterias, 
            'fullName' => $operator[0]->full_name, 
            'dateBetween' => $date[0], 
            'accept' => $date[0]->accept,
            'times' => $times,
            'menu' => $this->menuStatistics(),
            'types' => $callTypeList
        ];
         
        return  view('assest')->with('data',$data); 
    }

    public function callList($id, $startDate, $endDate) {

        $this->middleware('auth');
        // $this->checkCookie();

        $services = DB::select("SELECT s.id, s.service as name from organization_services s");

        $calls = DB::select("SELECT c.id, c.citizen_number, c.start_date, a.service_id as service, DATEDIFF(c.end_date, c.start_date) as diffDate, c.start_date as callStart, c.end_date as callEnd, s.service as serviceName, st.name as organName, c.end_date as endDate  
        
        from monthly_calls c inner join monthly_applications a on c.id = a.call_id inner join organizations st on a.organ_id = st.id inner join organization_services s on a.service_id = s.id WHERE LENGTH(c.citizen_number)>4 and c.blob_exist=1 and c.operator_id=".$id."  AND NOT(a.muracietin_novu = 'Şikayət vərəqəsi')
        
        AND c.start_date BETWEEN '".date('Y-m-d', strtotime($startDate))."' AND '".date('Y-m-d', strtotime($endDate))."' ORDER BY c.start_date ASC");
 
        $callServices = [];
        foreach($services as $service) { 
            $service->call = [];
            
            foreach($calls as $call){ 
                if($service->id===$call->service) {  

                    $second = $this->diffSecond($call->callEnd, $call->callStart);

                    if($second == 0 || ($second >= 20 && $second<=600)) {
                    // if($second == 0 || $second >= 20) {
                        array_push($service->call, $call);
                    }
                    
                }
            } 

            if(count($service->call)>0){
                array_push($callServices, $service);
            }
        } 

        $exist = DB::select("SELECT DISTINCT DATE(start_date) FROM `monthly_calls` WHERE blob_exist = 1 and `start_date` BETWEEN '".date('Y-m-d', strtotime($startDate))."' AND '".date('Y-m-d', strtotime($endDate))."'");
 
        $isnotExist = (int)date_diff(date_create($startDate), date_create($endDate))->format('%a') - count($exist);

        $worked = (int)date_diff(date_create($startDate), date_create($endDate))->format('%a');
 
        return ['services'=> $callServices, 'notWork' => $isnotExist, 'work' => $worked];

    }

    public function assessment($id) {

        $this->middleware('auth');
        $this->checkCookie();

        $assessment = DB::table('assessment')->where('user_id', $id)->where('is_package', 0)->get();
        
        $package = DB::table('package_assessment')->where('user_id', $id)->get();

        $operatorList = DB::table('operators')->where('id', $id)->get();   

        foreach($assessment as $item) {

            $critery = $this->assessmentCriterias($item->id);
            $criterySum = 0;
            $isAssessment = 0;
            $newComplaintSum = 0; 
            $reasonableComplaintSum = 0;
            $unreasonableComplaintSum = 0;
            foreach($critery as $criteryItem) {
                if($criteryItem->count) { 
                    
                    $newComplaintSum += count(DB::table('complaint_criterias')->where('assessment_criterias_id', $criteryItem->id)->get());  
                    
                    $reasonableComplaintSum += count(DB::table('complaint_criterias')->where('assessment_criterias_id', $criteryItem->id)->where('curator_status', '2')->orWhere('leading_status', '3')->get());  
                    
                    $unreasonableComplaintSum += count(DB::table('complaint_criterias')->where('assessment_criterias_id', $criteryItem->id)->where('curator_status', '3')->orWhere('leading_status', '4')->get());  
                    
                    $criterySum +=  $criteryItem->count;

                    $isAssessment += 1;

                } 
            } 
            $user = DB::table('operators')->where('id', $item->user_id)->get();  
            $item->fullName = $user[0]->full_name;
            $item->newComplaintSum = $newComplaintSum;  
            $item->reasonableComplaintSum = $reasonableComplaintSum;  
            $item->unreasonableComplaintSum = $unreasonableComplaintSum;  
            $item->operatorId = $id;
            // $item->score_count = $isAssessment>0 ? ceil($criterySum/$isAssessment) : 0;
            $item->criterias = $critery;

        }

        $package = DB::table('package_assessment')->where('user_id', $id)->get();

        foreach($package as $item) { 
            $user = DB::table('operators')->where('id', $item->user_id)->get();  
            $item->fullName = $user[0]->full_name;
            $item->operatorId = $id;
            $item->score_count = $item->score; 
        }

        $criterias = DB::select('select c.id, c.name, c.score from criterias c');

        $data = [
            'assessment'=>$assessment, 
            'criterias' => $criterias, 
            'package' => $package, 
            'fullName' => $operatorList[0]->full_name,
            'menu' => $this->menuStatistics()
        ];  
        return view('operatorAssessment')->with('data', $data); 
    }

    public function packageList() {

        $this->middleware('auth');
        $this->checkCookie();

        $package = DB::select('select p.id, o.full_name, p.date, p.score, p.percent  
                                from package_assessment p inner join operators o on p.user_id = o.id');
 
        $criterias = DB::select('select c.id, c.name, c.score from criterias c');

        $data = [
            'criterias' => $criterias, 
            'package' => $package,  
            'menu' => $this->menuStatistics()
        ]; 
 
        return view('packages')->with('data', $data);
    }

    public function callReport(Request $request) {
        
        $operators = DB::select('select o.id, o.full_name from operators o where o.asan_id = 1 and o.type =?',[isset(Auth::user()->supervisor_id) ? Auth::user()->supervisor_id : 0]);

        $data = [];    
         

        if(isset($request->startDate) && isset($request->endDate)) {
            foreach ($operators as $key => $operator) { 

                $commonCalls = DB::select("SELECT a.id FROM `assessment` a  inner join assessment_criterias a_c on a.id=a_c.assessment_id WHERE a.appraiser_date BETWEEN '$request->startDate' AND '$request->endDate' and a.user_id=?", [$operator->id]);
    
                $assessedCount = DB::select("SELECT a_c.id FROM `assessment` a inner join assessment_criterias a_c on a.id=a_c.assessment_id WHERE a.appraiser_date BETWEEN '$request->startDate' AND '$request->endDate' and (a_c.evaluator_comment is not null or a_c.criterias is not null) and a.user_id=?", [$operator->id]);
        
                $notAssessedCount = DB::select("SELECT a_c.id FROM `assessment` a inner join assessment_criterias a_c on a.id=a_c.assessment_id WHERE a.appraiser_date BETWEEN '$request->startDate' AND '$request->endDate' and a_c.evaluator_comment is null and a_c.criterias is null and a_c.play_time is not null and a.user_id=?", [$operator->id]);
            
                array_push($data, [
                    "operator" => $operator->full_name,
                    "commonCalls" => count($commonCalls),
                    "assessedCount" => count($assessedCount),
                    "notAssessedCount" => count($notAssessedCount)
                ]);
            }
        }

        return view('callReport')->with([
            'data' => $data, 
            'startDate' => isset($request->startDate)? $request->startDate : "", 
            'endDate' => isset($request->endDate)? $request->endDate : ""]);  
    }

    public function complaintReport(Request $request) {
        
        $operators = DB::select('select o.id, o.full_name from operators o where o.asan_id = 1 and o.type =?',[isset(Auth::user()->supervisor_id) ? Auth::user()->supervisor_id : 0]);

        $data = [];    
         

        if(isset($request->startDate) && isset($request->endDate)) {
            foreach ($operators as $key => $operator) { 

                $commonComplaints = DB::select("SELECT a.id FROM `assessment` a  inner join assessment_criterias a_c on a.id=a_c.assessment_id inner join complaint_criterias c_c on a_c.id=c_c.assessment_criterias_id WHERE a.appraiser_date BETWEEN '$request->startDate' AND '$request->endDate' and a.user_id=?", [$operator->id]);
                
                $unreasonable = DB::select("SELECT a.id FROM `assessment` a  inner join assessment_criterias a_c on a.id=a_c.assessment_id inner join complaint_criterias c_c on a_c.id=c_c.assessment_criterias_id WHERE a.appraiser_date BETWEEN '$request->startDate' AND '$request->endDate' and c_c.curator_status=3 and a.user_id=?", [$operator->id]);
                
                $reasonable = DB::select("SELECT a.id FROM `assessment` a  inner join assessment_criterias a_c on a.id=a_c.assessment_id inner join complaint_criterias c_c on a_c.id=c_c.assessment_criterias_id WHERE a.appraiser_date BETWEEN '$request->startDate' AND '$request->endDate' and c_c.curator_status=2 and a.user_id=?", [$operator->id]);
                
                $notResponse = DB::select("SELECT a.id FROM `assessment` a  inner join assessment_criterias a_c on a.id=a_c.assessment_id inner join complaint_criterias c_c on a_c.id=c_c.assessment_criterias_id WHERE a.appraiser_date BETWEEN '$request->startDate' AND '$request->endDate' and c_c.curator_status=1 and a.user_id=?", [$operator->id]);
                
                
                
                array_push($data, [
                    "operator" => $operator->full_name,
                    "commonComplaints" => count($commonComplaints),
                    "unreasonable" => count($unreasonable),
                    "reasonable" => count($reasonable),
                    "notResponse" => count($notResponse),
                ]);
            }
        }

        return view('complaintReport')->with([
            'data' => $data, 
            'startDate' => isset($request->startDate)? $request->startDate : "", 
            'endDate' => isset($request->endDate)? $request->endDate : ""]);  
    }

    public function packageDetailAssessment($id) {

        $this->middleware('auth');
        $this->checkCookie();

        $assessmentPackage = DB::table('package_assessment')->where('id', $id)->get();
        $operatorName = '';
        $operatorId = 0;
        $operatorNumber = 0;
        $assessmentList = [];
        foreach(json_decode($assessmentPackage[0]->assessment) as $assessmentId) {
            $assessment = DB::table('assessment')->where('id', $assessmentId)->get();

            foreach($assessment as $item) {
                $critery = $this->assessmentCriterias($item->id);
                $criterySum = 0;
                $criteryCount = 0;
                foreach($critery as $criteryItem) {
                    if($criteryItem->count>0){
                        $criterySum +=  $criteryItem->count;
                        $criteryCount += 1;
                    } 
                } 
            
                $user = DB::table('operators')->where('id', $item->user_id)->get(); 
                $item->fullName = $user[0]->full_name;
                $operatorName = $user[0]->full_name;
                $operatorId = $user[0]->id;
                $operatorNumber = $user[0]->phone_number;

                // $item->score_count = $criteryCount>0 ? ceil($criterySum/$criteryCount) : 0;
                $item->criterias = $this->assessmentCriterias($item->id);
            }
           
            array_push($assessmentList, $assessment[0]);
        }

        $criterias = DB::select('select c.id, c.name, c.score from criterias c');
        $data = [
            'assessment'=>$assessmentList, 
            'criterias' => $criterias, 
            'fullName' => $operatorName, 
            'operatorId' => $operatorId,
            'operatorNumber' => $operatorNumber,
            'menu' => $this->menuStatistics()
        ];  
         
        return view('operatorPackageAssessment')->with('data', $data); 
    }

    // public function packageDetailAssessment($id) {

    //     $this->middleware('auth');
    //     $this->checkCookie();

    //     $assessmentPackage = DB::table('package_assessment')->where('id', $id)->get();
    //     $operatorName = '';
    //     $operatorId = 0;
    //     $assessmentList = [];
    //     foreach(json_decode($assessmentPackage[0]->assessment) as $assessmentId) {
    //         $assessment = DB::table('assessment')->where('id', $assessmentId)->get();

    //         foreach($assessment as $item) {
    //             $critery = $this->assessmentCriterias($item->id);
    //             $criterySum = 0;
    //             $criteryCount = 0;
    //             foreach($critery as $criteryItem) {
    //                 if($criteryItem->count>0){
    //                     $criterySum +=  $criteryItem->count;
    //                     $criteryCount += 1;
    //                 } 
    //             } 
            
    //             $user = DB::table('operators')->where('id', $item->user_id)->get(); 
    //             $item->fullName = $user[0]->full_name;
    //             $operatorName = $user[0]->full_name;
    //             $operatorId = $user[0]->id;
    //             // $item->score_count = $criteryCount>0 ? ceil($criterySum/$criteryCount) : 0;
    //             $item->criterias = $this->assessmentCriterias($item->id);
    //         }
           
    //         array_push($assessmentList, $assessment[0]);
    //     }

    //     $criterias = DB::select('select c.id, c.name, c.score from criterias c');
    //     $data = [
    //         'assessment'=>$assessmentList, 
    //         'criterias' => $criterias, 
    //         'fullName' => $operatorName, 
    //         'operatorId' => $operatorId,
    //         'menu' => $this->menuStatistics()
    //     ];  
   
    //     return view('operatorPackageAssessment')->with('data', $data); 
    // }

    public function assessmentCriterias($id) {
        // $this->checkCookie();
        return DB::table('assessment_criterias')->where('assessment_id', $id)->get();
    }

    public function assessmentCalls($servicesCount, $allCallCount, $id, $startDate, $endDate, $count) {

        $this->middleware('auth');
        // $this->checkCookie();

        $count = (int)$count;

        $services = $this->callList($id, $startDate, $endDate);

        $all = $this->importantServices($services);

        $services = $all['services'];

        $calls = $all['calls'];

        $count = $count - count($calls);
  
        if(count($services['services'])>$count) {

            foreach($services['services'] as $service) {

                if(count($service->call)>0 && $count>0 && !isset($service->selected)) { 

                    $randomCount = rand(0, count($service->call)-1); 
                
                    $second = $this->diffSecond($service->call[$randomCount]->callEnd, $service->call[$randomCount]->callStart);

                    if($second == 0 || ($second >= 20 && $second<=600)) {
                    // if($second == 0 || $second >= 20) {
                        $count--;  
                        array_push($calls, $service->call[$randomCount]);
                        unset($service->call[$randomCount]); 
                    }
                }

            }

            if($count > 0) {
                foreach($services['services'] as $service) {

                    if(count($service->call)>0 && $count>0 && isset($service->selected)) { 
    
                        $randomCount = rand(0, count($service->call)-1); 
                    
                        $second = $this->diffSecond($service->call[$randomCount]->callEnd, $service->call[$randomCount]->callStart);
    
                        if($second == 0 || ($second >= 20 && $second<=600)) {
                        // if($second == 0 || $second >= 20) {
                            $count--;  
                            array_push($calls, $service->call[$randomCount]);
                            unset($service->call[$randomCount]); 
                        }
                    }
    
                }
            }

        } else {   
         
            while($count > 0) {
        
                foreach($services['services'] as $service) {
                    if(count($service->call)>0 && $count>0 && !isset($service->selected)) { 

                        $randomCount = rand(0, count($service->call)-1); 
                        $second = $this->diffSecond($service->call[$randomCount]->callEnd, $service->call[$randomCount]->callStart);
 
                        if(strlen($service->call[$randomCount]->citizen_number)>4) {
                            if($second == 0 || ($second >= 20 && $second<=600)) {
                            // if($second == 0 || $second >= 20) {
                                $count--;  
                                if(array_push($calls, $service->call[$randomCount])) { 
                                    unset($service->call[$randomCount]); 
                                    $service->call = array_values($service->call);
                                } 
                            }
                        }
                        
                    }
                } 

                if($count > 0) {
                    foreach($services['services'] as $service) {
                        if(count($service->call)>0 && $count>0 && isset($service->selected)) { 

                            $randomCount = rand(0, count($service->call)-1); 
                            $second = $this->diffSecond($service->call[$randomCount]->callEnd, $service->call[$randomCount]->callStart);
    
                            if(strlen($service->call[$randomCount]->citizen_number)>4) {
                                if($second == 0 || ($second >= 20 && $second<=600)) {
                                // if($second == 0 || $second >= 20) {
                                    $count--;  
                                    if(array_push($calls, $service->call[$randomCount])) { 
                                        unset($service->call[$randomCount]); 
                                        $service->call = array_values($service->call);
                                    } 
                                }
                            }
                            
                        }
                    } 
                }
            } 
        }
  

        //assessment  
        $assessment = new Assessment(); 
        $assessment->begin_date = date('Y-m-d', strtotime($startDate));
        $assessment->end_date = date('Y-m-d', strtotime($endDate));
        $assessment->appraiser_date = date('Y-m-d');
        $assessment->user_id = $id; 
        $assessment->services_count = $servicesCount; 
        $assessment->calls_count = $allCallCount; 
        $assessment->supervisor_id = Auth::user()->id;

        if($assessment->save()){

            $assessmentCriterias = [];

            foreach ($calls as $item)
            {     
 
                array_push($assessmentCriterias, [
                    'assessment_id' => $assessment->id,
                    'call_id' => $item->id,
                    'count' => 0,
                    'criterias' => null,
                    'is_active' => 1
                ]);
  
            }

            $inserted = DB::table('assessment_criterias')->insert($assessmentCriterias);
            
            if($inserted){
                return ['status' => 200, 'calls'=> $this->getAssessmentById($assessment->id)['calls'], 'id'=>$assessment->id];
            }
        } 
        
    }

    public function callResearch($AssessmentCriteryId, $status) {

        $this->checkCookie();
        
        $newCall = DB::select('select a.id as assessmentId, m_a.service_id as serviceId, a.*, a_c.*
        from assessment a inner join assessment_criterias a_c on a.id = a_c.assessment_id 
        inner join monthly_applications m_a on a_c.call_id = m_a.call_id  where a_c.id='.$AssessmentCriteryId);

        $startDate = explode(' ', $newCall[0]->begin_date)[0];
        $endDate = explode(' ', $newCall[0]->begin_date)[1];
        $id = $newCall[0]->user_id;

        $assessmentNewCallList = array_column(DB::select('select a.renew_calls from assessment_criterias a where renew_calls is not null and assessment_id='.$newCall[0]->assessmentId), 'renew_calls');
        $assessmentClosedCallList = array_column(DB::select('select a.closed_calls from assessment_criterias a where closed_calls is not null and assessment_id='.$newCall[0]->assessmentId), 'closed_calls');
 
        $callList = array_column(DB::select('select a.call_id from assessment_criterias a where assessment_id='.$newCall[0]->assessmentId), 'call_id');
     
        $count = 1;
     
        $renew_calls = [];
        $closed_calls =[];

        $renew_calls1 = [];
        $closed_calls1 =[];

        if($newCall[0]->renew_calls != null) {
            $renew_calls1 = json_decode($newCall[0]->renew_calls);
        }

        if($newCall[0]->closed_calls != null) {
            $closed_calls1 = json_decode($newCall[0]->closed_calls);
        }


        if(count($assessmentNewCallList) != 0) {

            foreach($assessmentNewCallList as $item) {
                $renew_calls = array_merge($renew_calls, json_decode($item)); 
            }
            
        }

        if(count($assessmentClosedCallList) != 0) {

            foreach($assessmentClosedCallList as $item) {
                $closed_calls = array_merge($closed_calls, json_decode($item)); 
            }
            
        } 

        $callList = array_merge($callList, $renew_calls, $closed_calls);

        $services = $this->callList($id, $startDate, $endDate);

        $all = $this->importantServices($services);

        $services = $all['services'];

        $calls = [];
            
        foreach($services['services'] as $service) {
 
            if(in_array($newCall[0]->serviceId, $this->selected) && isset($service->selected)) {
                if(count($service->call)>0 && $count>0) {
                    
                    $randomCount = rand(0, count($service->call)-1); 
                    if(!in_array($service->call[$randomCount]->id, $callList)) {
                        --$count;  
                        $service->call[$randomCount]->count = $count;
                        array_push($calls, $service->call[$randomCount]);
                        unset($service->call[$randomCount]); 
                        break;
                    }
    
                }
            }  

        }

        if($count>0) {
            foreach($services['services'] as $service) {

                if(count($service->call)>0 && $count>0) {
                    
                    $randomCount = rand(0, count($service->call)-1); 
                    
                        if(!in_array($service->call[$randomCount]->id, $callList)){
                            
                            --$count;  
                            $service->call[$randomCount]->count = $count;
                            array_push($calls, $service->call[$randomCount]);
                            unset($service->call[$randomCount]); 
                            break;
                        }
    
                }
             
            }
        }
       
        if(count($calls)>0) {

            if($status == 1) {
                array_push($renew_calls1, $newCall[0]->call_id);
                DB::select("update assessment_criterias set call_id =".$calls[0]->id." , renew_calls = '".json_encode($renew_calls1)."' where id=".$AssessmentCriteryId);
            } else {
                array_push($closed_calls1, $newCall[0]->call_id);
                DB::select("update assessment_criterias set call_id =".$calls[0]->id." , closed_calls = '".json_encode($closed_calls1)."' where id=".$AssessmentCriteryId); 
            }

            return [
                'status' => 200, 
                'message'=>'Zəng yenisi ilə əvəz olundu', 
                'startDate' => $calls[0]->start_date, 
                'endDate' => $calls[0]->endDate,
                'number' => $calls[0]->citizen_number,
                'organ' => $calls[0]->organName,
                'service' => $calls[0]->serviceName,
                'callId' => $calls[0]->id
            ];

        } else {

            return [
                'status' => 500, 
                'message'=>'Zəng tapılmadı'
            ];

        }
        
    }

    public function importantServices($services) {
         
        $calls = [];

        foreach($this->selected as $item) {
            foreach($services['services'] as $service) {
                if($item == $service->id) {
 
                    $service->selected = true;

                    $temprorary = [];
 
                    if(count($service->call) <= 1) {
                        
                        array_push($calls, $service->call[0]); 
                        unset($service->call[0]);  
                    
                    } else {

                        foreach($service->call as $callItem){

                            if($callItem->callEnd !== null) {

                                $diff = strtotime($callItem->callEnd) - strtotime($callItem->callStart);
                                $callItem->diffDate = $diff; 

                            } else {

                                $callItem->diffDate = 0;

                            }
                            
                        }

                        $selectedArr =  $this->max_attribute_in_array($service->call, 'diffDate');
                        
                         if(count($selectedArr)>0) {
                            array_push($calls, $service->call[$selectedArr['index']]); 
                            unset($service->call[$selectedArr['index']]); 
                            $service->call = array_values($service->call);
                        }
                        
                    }
                }
            }
        } 
        return ['calls'=>$calls, 'services'=>$services];
    }

    public function max_attribute_in_array($data_points, $value) {
        $max=0;
        $obj = [];
        foreach($data_points as $index => $point) {
            if($max < (float)$point->{$value}) {
            
                $obj = ['index' => $index,'result' => $point];

            }
        }
  
        return $obj;
    }

    public function updateCall(Request $request)
    {

        $this->middleware('auth');
        // $this->checkCookie();

        $criterias = DB::select('select * from criterias');
        
        $call_spend_time = $request->input('time');
        $call_time = $request->input('audioTime');
        $played_time = $request->input('playTime');
        $unplayed_time = $request->input('unPlayTime');
        $criteria_time = ($request->input('criterias') ? count($request->input('criterias')) : 0) * 5;
        $note_time = 30; 
         
        $allowed_play_time = ($call_time-$unplayed_time)*2; 
        
        if($allowed_play_time < $played_time) {
        
              $unallowed_play_time = $played_time - $allowed_play_time;
              $total_avoidance = $call_spend_time - ($criteria_time + $note_time 
              + $played_time - $unallowed_play_time);
        
        } else {
        
              $total_avoidance = $call_spend_time - ($criteria_time + $note_time + $played_time);
        
        }
        
        if($total_avoidance < 0){
            $total_avoidance = 0;
        } 

        $lastLogin = DB::select("select id, daily_work_time from user_report where user_id=".Auth::user()->id." order by id desc limit 1");

        $works = [];

        if($lastLogin[0]->daily_work_time !== null) {
            $works = json_decode($lastLogin[0]->daily_work_time);
        }
        
        //Assessment criter update
        $update = AssessmentCriterias::where('id', (int)$request->input('assessmentCriteryId'))->first();  

        $call_spend_time = $update->assessment_time==null ? $call_spend_time : ((int)$call_spend_time - (int)$update->assessment_time);
        $played_time = $update->play_time==null ? $call_spend_time : ((int)$played_time - (int)$update->play_time);

        array_push($works, [
            "call"=>(int)$request->input('call'), 
            'avoidance'=>$total_avoidance, 
            'callSpendTime'=>$call_spend_time,
            'playTime' => $played_time
            ]);

        $updateWork = UserReport::find($lastLogin[0]->id);

        $updateWork->daily_work_time = json_encode($works);

         //Assessment criter update
        // $update = AssessmentCriterias::where('id', (int)$request->input('assessmentCriteryId'))->first();  
        $update->count = $request->input('count');
        
        $update->evaluator_comment = $request->input('comment');
        $update->criterias = $request->input('criterias') ? json_encode($request->input('criterias')) : NULL;
        $update->wrong_selection = $request->input('wrongSelection');
        $update->assessment_time = $request->input('time');
        $update->play_time = $request->input('playTime');
        $update->unplay_time = $request->input('unPlayTime');
        $update->special_time = $request->input('specialTime');
        $update->audio_time = $request->input('audioTime');
        $update->is_assessment = $request->input('status');
        $update->is_active = 1;

        //Update Assessment score
        $updateAssessment = Assessment::where('id', $update->assessment_id)->first();
        $updateAssessment->score_count = $request->input('assessmentScore');
        $updateAssessment->score_percent = round((($request->input('assessmentScore') == null) ? 0 : $request->input('assessmentScore')) / (count($criterias) !== 0 ? 33 : 1)*100);
        if($update->save() && $updateWork->save() && $updateAssessment->save()) {
            return ['status' => 200, 'message' => 'Qiymətləndirmə uğurla başa çatdı']; 
        } else {
            return ['status' => 500, 'message' => 'Sistem xətası'];
        }
    }

    public function updateAssessment(Request $request) {
 
        $this->middleware('auth');
        // $this->checkCookie();

        return ['status' => 200, 'message'=> 'Qiymətləndirmə uğurla başa çatdı'];

        // $criterias = DB::select('select * from criterias');
        // $id = $request->input('id'); 
        // $commonAssessment = $request->input('commonAssessment');
        // $score = 0;
        // $isAssessment = 0;
        // try{ 
            
        //     $assessmentScore = DB::transaction(function () use($commonAssessment, $id, $score, $isAssessment ) {
                
        //         foreach ($commonAssessment as $item)
        //         {     
                    
                    
        //             $score += (int)$item['count']; 

        //             if((int)$item['count']>0){
        //                 $isAssessment += 1;
        //             }

        //             $update = AssessmentCriterias::where('call_id', (int)$item['call'])->first(); 
        //             $update->assessment_id = (int)$id;
        //             $update->count = (int)$item['count'];
        //             $update->evaluator_comment = $item['comment'];
        //             $update->criterias = json_encode($item['criterias']);
        //             $update->wrong_selection = $item['wrongSelection'];
        //             $update->assessment_time = $item['time']; 
        //             $update->play_time = $item['playTime'];
        //             $update->unplay_time = $item['unPlayTime'];
        //             $update->special_time = $item['specialTime'];
        //             $update->is_active = 1;
 
        //             $update->save();
        
        //             DB::commit(); 
 
        //         }  

        //         return $score.'.'.$isAssessment;
        //     });
  
        //     $assessment = Assessment::find((int)$id);  
        //     // $assessment->is_active = 1; 
        //     // $assessment->score_count = round(explode(".", $assessmentScore)[0]/explode(".", $assessmentScore)[1]);
        //     $assessment->score_count = $request->input('assessmentScore');
        //     $assessment->score_percent = round((($request->input('assessmentScore') == null) ? 0 : $request->input('assessmentScore')) / ((count($criterias) !== 0 ? count($criterias) : 1)*3)*100);
            
        //     if($assessment->save()){
        //         return ['status' => 200, 'message'=> 'Qiymətləndirmə uğurla başa çatdı'];
        //     }
            

        // } catch(\Exception $ex) {  
        //     return ['error'=>$ex];
        // }
   
    } 

    public function criteryList() {
 
        $criterias = DB::select('select * from criterias');

        $data = [
            'critery'=> $criterias,  
            'menu' => $this->menuStatistics() 
        ]; 
 
       return view('critery')->with('data',$data); 

    }

    public function packageAssessment(Request $request) {

        $this->middleware('auth'); 
        $assessmentRequest = $request->input('assesment'); 
        // $score = $request->input('score'); 
        
        $score = 0;
        $percent = 0;
        foreach(json_decode($assessmentRequest) as $assessmentId) {
            $assessment = Assessment::find((int)$assessmentId);  
            $assessment->is_package = 1; 
            $score += $assessment->score_count;
            $percent += $assessment->score_percent;
            $assessment->save();
        }
        
        $packageAssessment = new PackageAssessment();  
        $packageAssessment->assessment = $request->input('assesment'); 
        $packageAssessment->score = round($score/count(json_decode($assessmentRequest))); 
        $packageAssessment->percent = round($percent/count(json_decode($assessmentRequest))); 
        $packageAssessment->user_id = $request->input('userId'); 
        $packageAssessment->supervisor_id = Auth::user()->id;

        if($packageAssessment->save()){
            return ['status'=>200, 'response'=>'Yekun qiymətləndirmə uğurla tamamlandı'];
        }

    }

    public function login() {
        return view('home');
    }

    public function getAssessmentById($id) {

        $this->middleware('auth');
        // $this->checkCookie();

        $assessmentScore = DB::select('select score_count as score, score_percent as percent from assessment where id = '.$id); 

        $calls = DB::select("SELECT a.id, a.score_count as score, c.start_date as beginDate, c.end_date as endDate, s.service as serviceName, c.citizen_number, a_c.evaluator_comment as comment, a_c.id as assessmentId,
            a_c.play_time as playTime, a_c.unplay_time as unPlayTime, a_c.special_time as specialTime, a_c.assessment_time as assessmentTime, a_c.audio_time as audioTime, a_c.is_assessment, a_c.renew_calls, a_c.closed_calls,
            a_c.criterias as criterias, a_c.call_id as callId, a_c.wrong_selection as wrongSelection, c.start_date as callStart, o.name as organName, c.end_date as callEnd, a_c.count as count 
            from assessment a inner join assessment_criterias a_c on a.id = a_c.assessment_id inner join monthly_calls c on a_c.call_id = c.id 
            inner join monthly_applications app on c.id = app.call_id inner join organization_services s on app.service_id = s.id 
            inner join organizations o on app.organ_id = o.id where c.start_date >= a.begin_date and a.id=".$id.' order by a_c.id asc');
       
        return ["calls"=>$calls, "score" => $assessmentScore[0]->score, "percent" => $assessmentScore[0]->percent];

    }

    public function getPackageById($id) {

        $this->middleware('auth');
        // $this->checkCookie();
        $package = DB::select("select p.id as id, p.score as completedScore, p.percent as completedPercent, p.assessment as assessment, o.full_name as userName, p.date as date,
                             u.name as supervisorName, p.supervisor_id supervisorId from package_assessment p left join operators o on p.user_id = o.id 
                             left join users u on p.supervisor_id = u.id   where p.id=?", [$id])[0];
    
        $list = [];
        foreach(json_decode($package->assessment) as $assessment){

            $wrong_selection = DB::select("select a.id as id, a_c.wrong_selection as wrongSelection from assessment a inner join assessment_criterias a_c on a.id=a_c.assessment_id 
                                where a.id=? and a_c.wrong_selection=?", [$assessment, 1]);

            $timer = DB::select("select a.id as id, a_c.assessment_time as time from assessment a inner join assessment_criterias a_c on a.id=a_c.assessment_id 
                                where a.id=? ", [$assessment]);
            
            $second = 0;
            foreach($timer as $item){ 
                $second += $item->time;
            }

            $isAssessment = DB::select("select a.id as id, a_c.criterias as wrongSelection from assessment a inner join assessment_criterias a_c on a.id=a_c.assessment_id 
                                          where a.id=? and a_c.criterias is not null", [$assessment]);
           
            $item = DB::select("select a_c.assessment_id, a.begin_date, a.end_date, a_c.assessment_time, a_c.call_id, a.calls_count, a_c.evaluator_comment, a_c.count, a_c.criterias, a.score_percent,
            a.score_count, a.services_count, a.supervisor_id as supervisorId, a.user_id as userId from assessment a left join assessment_criterias a_c on a.id=a_c.assessment_id where a.id=?", [$assessment]);
            
            if(count($item)>0) {
                $item[0]->wrong_selection = $wrong_selection !==null  ? count($wrong_selection): null;
                $item[0]->assessmentTime = $this->setHour($second);
                $item[0]->isAssessment = count($isAssessment);
                array_push($list, $item[0]);
            }

        }

        $package->assessment = $list; 
 
        return ["package"=>$package];

    }

    public function mediasenseSign() {
        $url_path = 'https://cagri-rec01.asan.local:8440/ora/authenticationService/authentication/signIn'; 

        $data = array("requestParameters"=> array('username' => 'cmreport', 'password' => 'Asan1234'));
        $postdata = json_encode($data);

        $ch = curl_init($url_path);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookie.txt');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        $result = curl_exec($ch);
        curl_close($ch);
        // var_dump(json_decode($result));
    }

    public function mediasenseAudioChecker($number, $beginDate, $endDate, $assessmentId) {
        // $this->checkCookie();
        $url_path = 'https://cagri-rec01.asan.local:8440/ora/queryService/query/getSessions'; 
        
        $postdata = '{
            "requestParameters":[
                {"fieldName":"deviceRef","fieldConditions":[{"fieldOperator":"equals","fieldValues":["'.$number.'"]}], "paramConnector":"AND"},
                {"fieldName":"sessionDuration","fieldConditions":[{"fieldOperator":"greaterThan","fieldValues":[0],"fieldConnector":"AND"},
                {"fieldOperator":"lessThan","fieldValues":[1540000]}],"paramConnector":"AND"},
                {"fieldName":"sessionState","fieldConditions":[{"fieldOperator":"equals","fieldValues":["ACTIVE"],"fieldConnector":"OR"},
                {"fieldOperator":"equals","fieldValues":["CLOSED_NORMAL"],"fieldConnector":"OR"},
                {"fieldOperator":"equals","fieldValues":["CLOSED_ERROR"]}], "paramConnector":"AND"},
                {"fieldName":"sessionStartDate","fieldConditions":[{"fieldOperator":"between","fieldValues":['.$beginDate.', '.$endDate.']}]}
            ],
            "pageParameters":{"offset":0,"limit":100},
            "sortParameters":[{"byFieldName":"sessionStartDate","order":"DESC"}]
        }';

        $ch = curl_init($url_path);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); 
        curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookie.txt');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        $result = curl_exec($ch);
        curl_close($ch);
        if(json_decode($result)->responseCode === 2000) {
            if($assessmentId !=0){

                $assessmentCritery = DB::select("select a.id as id, a.criterias as criterias, a.wrong_selection as wrongSelection, 
                a.assessment_time as time, a.unplay_time as unplayTime, a.special_time as specialTime, a.audio_time as audioTime, a.play_time as playTime,
                a.evaluator_comment as comment, a.count as count, a.operator_comment as operatorComment, a.curator_comment as curatorComment,
                a.leader_comment as leaderComment from assessment_criterias a where a.id=?", [$assessmentId]);

                $selectedRole ='';
                if(Auth::user()){

                    if(Auth::user()->role !== null) {
                        $selectedRole = ' and curator_status !=0 or leading_status != 0 ';
                    } 
                    // else if(Auth::user()->role==1 || Auth::user()->role==2) {
                    //     $selectedRole = Auth::user()->role==1 ? ' and curator_status !=0' : ' and leading_status != 0';
                    // }
                }
                   
                $complaints = DB::select("select c.id as id, c.leading_status as leadingStatus, c.curator_status as curatorStatus, c.critery_id as critery from complaint_criterias c where c.assessment_criterias_id=?".$selectedRole, [$assessmentCritery[0]->id]);
 

                return ["code" => 200, "responseBody" => 'Success', "assessmentCritery" => $assessmentCritery[0], 'complaints' => $complaints, 'role' => (Auth::user() ? Auth::user()->role : null)];

            } else {
                return ["code" => 200, "responseBody" => 'Success'];
            }
            

        } if(json_decode($result)->responseCode === 2001) {

            return ["code" => json_decode($result)->responseCode, "responseMessage" => 'Audio tapılmadı'];

        } else {

            return ["code" => json_decode($result)->responseCode, "responseMessage" => json_decode($result)->responseMessage]; 

        } 
    }

    public function mediasenseAudio($number, $beginDate, $endDate) {
        // $this->checkCookie();
        $url_path = 'https://cagri-rec01.asan.local:8440/ora/queryService/query/getSessions'; 
         
        $postdata = '{
            "requestParameters":[
                {"fieldName":"deviceRef","fieldConditions":[{"fieldOperator":"equals","fieldValues":["'.$number.'"]}], "paramConnector":"AND"},
                {"fieldName":"sessionDuration","fieldConditions":[{"fieldOperator":"greaterThan","fieldValues":[0],"fieldConnector":"AND"},
                {"fieldOperator":"lessThan","fieldValues":[540000]}],"paramConnector":"AND"},
                {"fieldName":"sessionState","fieldConditions":[{"fieldOperator":"equals","fieldValues":["ACTIVE"],"fieldConnector":"OR"},
                {"fieldOperator":"equals","fieldValues":["CLOSED_NORMAL"],"fieldConnector":"OR"},
                {"fieldOperator":"equals","fieldValues":["CLOSED_ERROR"]}], "paramConnector":"AND"},
                {"fieldName":"sessionStartDate","fieldConditions":[{"fieldOperator":"between","fieldValues":['.$beginDate.', '.$endDate.']}]}
            ],
            "pageParameters":{"offset":0,"limit":100},
            "sortParameters":[{"byFieldName":"sessionStartDate","order":"DESC"}]
        }';

        $ch = curl_init($url_path);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); 
        curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookie.txt');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        $result = curl_exec($ch);
        curl_close($ch);
    
        if(json_decode($result)->responseCode === 2000) {

            $audio = json_decode($result)->responseBody->sessions[0]->urls->httpUrl; 
            $cURLConnection = curl_init(); 
            curl_setopt($cURLConnection, CURLOPT_URL, $audio );
            curl_setopt($cURLConnection, CURLOPT_FOLLOWLOCATION, 1); 
            curl_setopt($cURLConnection, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($cURLConnection, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($cURLConnection, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($cURLConnection, CURLOPT_USERPWD, 'cmreport' . ":" . 'Asan1234');  
            curl_setopt($cURLConnection, CURLOPT_COOKIEFILE, 'cookie.txt'); 
            curl_setopt($cURLConnection, CURLOPT_HTTPHEADER, array('Content-Type: video/MP4'));
            curl_setopt($cURLConnection, CURLOPT_HTTPHEADER, array('Response-Type: blob'));
            
            $phoneList = curl_exec($cURLConnection);
            curl_close($cURLConnection);  

            return $phoneList;

        } else {

            return response()->json(['message' => 'error message'], 500); 
            // return ["code" => json_decode($result)->responseCode, "responseMessage" => json_decode($result)->responseMessage]; 

        } 
    }

    public function closeCall(Request $request) {

        $assestmentId = $request->input('assestId');

        $playeTime = $request->input('playeTime');

        $time = $request->input('time'); 

        try {
            
            DB::select('update assessment_criterias set play_time= ?, assessment_time = ? where id=?', [$playeTime, $time, $assestmentId]);

            return ["code" => 200, "responseBody" => 'Success'];

        }  catch(\Exception $ex) {  

            return ["code" => 500, "responseBody" => 'Error'];
            
        }
    }

    public function reasonable(Request $request) {
        // $this->checkCookie();
        $role = $request->input('role');
        $assestmentId = $request->input('assestmentId');
        $reasonables = $request->input('reasonable'); 
        $curatorComment = $request->input('curatorComment'); 
        $leaderComment = $request->input('leaderComment'); 
        $checkRole = $request->input('checkRole');
        $comment = $role == 1 ? $curatorComment : $leaderComment;
        $criterias = DB::select('select * from criterias');
        $assessmentCritery = AssessmentCriterias::find($assestmentId);

        $score = 0;
 
        foreach($reasonables as $reasonable) {
            if($checkRole == 0) {

                DB::select('update complaint_criterias set curator_status= ? where id=?', [$reasonable['value'], $reasonable['id']]);
                if($reasonable['value'] == 2) { 

                    $arr = json_decode($assessmentCritery->criterias);

                    $arr[(int)$reasonable['index']]->count = "0";
                   
                    $assessmentCritery->criterias = json_encode($arr);

                    $assessmentCritery->save();

                    $score++;

                } else {
                    DB::select('update complaint_criterias set leading_status = 1 where id=?', [$reasonable['id']]);
                }

            } else {
                DB::select('update complaint_criterias set leading_status= ? where id=?', [$reasonable['value'], $reasonable['id']]);
                if($reasonable['value'] == 3) { 
                    $arr = json_decode($assessmentCritery->criterias);
                    // dd($arr);
                    // $arr[(int)$reasonable['index']]->count = "0";
                   
                    $assessmentCritery->criterias = json_encode($arr);

                    $assessmentCritery->save();

                    $score++;
                }
            }
        }

        if($score>0){
            
            $completedCriteryScore = $assessmentCritery['count'] + $score;

            if($checkRole == 0) {
                DB::select('update assessment_criterias set curator_comment= ?, count = ? where id=?', [$curatorComment, $completedCriteryScore, $assestmentId]);
            } else {
                DB::select('update assessment_criterias set leader_comment= ?, count = ? where id=?', [$curatorComment, $completedCriteryScore, $assestmentId]);
            }
  
            $scoreSum = 0;
            $scoreCount = 0;
            $assessments = DB::table('assessment_criterias')->where('assessment_id', $assessmentCritery['assessment_id'])->whereNotNull('criterias')->get();
            
            $completedScore = $this->calculateScore($assessments);
 
            $completedPercent = round((($completedScore == null) ? 0 : $completedScore) / ((count($criterias) !== 0 ? 11 : 1)*3)*100);
  
            $update = DB::select('update assessment set score_count = ?, score_percent = ? where id=?', [$completedScore, $completedPercent, $assessmentCritery['assessment_id']]);
            
            return ['status'=>'200','message'=> 'Məlumat qeyd olundu'];
                
        } else {
            return ['status'=>'200','message'=> 'Məlumat qeyd olundu'];
        }
        
    }

    public function calculateScore($assest) { 
       $criteryList = DB::select("select id, 0 as count, score, max_score from criterias");

       $count = 33;

       $allCriteryList = array_map(function($item) use($assest) {
 
            foreach($assest as $assessment) { 
                if (in_array($item->id, array_column(json_decode($assessment->criterias),"id")) && $item->count < $item->max_score){
                    $item->count = $item->count + floatval(json_decode($assessment->criterias)[array_search($item->id, array_column(json_decode($assessment->criterias),"id"))]->count);
                    $item->max = $item->max_score;
                }
                
            } 

            return $item;

        }, $criteryList);
 
        foreach($allCriteryList as $element) {

            $count -= $element->count > $element->max_score ? $element->max_score : $element->count;
            
        }
 
        return $count;

    }

    public function userReport(){
        date_default_timezone_set("Asia/Baku");
        // $this->checkCookie();
        $currentDateTime = date("Y-m-d H:i:s");

        $checkLogin = DB::select("SELECT DISTINCT date FROM user_report 
        WHERE DATE_FORMAT(date, '%Y %m') = DATE_FORMAT('$currentDateTime', '%Y %m') ORDER BY date desc"); 

        $lastLogin = DB::select("select id from user_report order by id desc limit 1");

        if(count($checkLogin) == 0) {
            $report = new UserReport();
            $report->user_id = Auth::user()->id;
            $report->daily_login = json_encode(array(['startDate'=>$currentDateTime, 'endDate'=>'']));
            $report->date = $currentDateTime;
            $report->save();
        } else {

            $update = UserReport::find($lastLogin[0]->id);

            if(round((strtotime('23:59') - strtotime()), 1) > 0) {

                $arr = json_decode($lastLogin[0]->daily_login); 

                if($arr[count($arr)-1]['endDate'] =='') {
                    $arr[count($arr)-1]['endDate'] = date("H:i"); 
                }  

                array_push($arr, ['startDate'=>$currentDateTime, 'endDate'=>'']);

                $update->daily_login = json_encode($arr);
            }
            
            $update->save();
            
        }
    }
    
    public function assestStatistics(Request $request) {

        $this->middleware('auth');
        $this->checkCookie();

        date_default_timezone_set("Asia/Baku");

        $loginTime = 0;

        $workTime = 0;

        $callSpendTime = 0;
        
        $avoidance = 0;

        $playTime = 0;

        $callCount = 0;
 
        $userId = $request->input('id') ? $request->input('id') : 0; 

        $currentDateTime = date("Y-m-d");
       
        if($request->input('date')) {
            $currentDateTime = date($request->input('date'));
        }

        if (Auth::user() && Auth::user()->role == 0){
            $userId = Auth::user()->id;
        }

        $notLogOut = $currentDateTime == date("Y-m-d") ? date("H:i") : "23:59";
 
        $lastLogin = DB::select("select * from user_report where user_id=$userId and date LIKE '%$currentDateTime%' order by id desc limit 1");

        $lastLoginDate = DB::select("select * from user_report where date LIKE '%$currentDateTime%' order by id desc limit 1");
 
        if(count($lastLogin)>0) {
            $lastLogin[0]->daily_login = json_decode($lastLogin[0]->daily_login);

            $lastLogin[0]->daily_work_time = json_decode($lastLogin[0]->daily_work_time);

            foreach($lastLogin[0]->daily_login as $time){

                $loginTime += strtotime($time->endDate !="" ? $time->endDate : $notLogOut) - strtotime($time->startTime);

            }
 
            if($lastLogin[0]->daily_work_time !=null) {

                foreach($lastLogin[0]->daily_work_time as $time) {

                    $avoidance += $time->avoidance;

                    $callCount++;

                    $callSpendTime += $time->callSpendTime;

                    $playTime += isset($time->playTime) ? $time->playTime : 0;

                }

            }
        }

        $users = DB::select("select id, name from users where role = 0 and centerId is null");
 
        $ownWorkTime = 0;
 

        if($loginTime > 14400 && $loginTime < 18000) {
            $ownWorkTime = 14400;
            $ownWorkTime = $ownWorkTime - ($ownWorkTime/3600)*0.25*3600;
        } else if($loginTime > 18000) {
            $ownWorkTime = $loginTime - 3600; 
            $ownWorkTime = $ownWorkTime - ($ownWorkTime/3600)*0.25*3600;
        } else if($loginTime < 14400) {
            $ownWorkTime = $loginTime>3600 ? ($loginTime - ($loginTime/3600)*0.25*3600) : $loginTime; 
        }
 
        $offsetTime = isset($lastLoginDate[0]->call_offset_time) ? $lastLoginDate[0]->call_offset_time : 0;

        $avoidancePlayTime = $playTime + $callCount*$offsetTime;

        $callAvoidanceSpendTime = $callSpendTime + $callCount*$offsetTime;
 
        $freeTimeAvoidance = $ownWorkTime - $callAvoidanceSpendTime;

        $callAvoidance = $callSpendTime - $avoidancePlayTime;
 
        $data = [
            'loginTime'=> $this->setHour($loginTime>$callSpendTime || $loginTime == 0 ? $loginTime : ($callSpendTime+300)), 
            'workTime'=> $this->setHour($ownWorkTime), 
            'callSpendTimes'=> $this->setHour($callSpendTime), 
            'freeTimeAvoidance' => $this->setHour($freeTimeAvoidance>0 ? $freeTimeAvoidance : 0), 
            'callAvoidance' => $this->setHour($callAvoidance>0 ? $callAvoidance : 0), 
            'playTime' => $this->setHour($playTime),
            'menu' => $this->menuStatistics(),
            'users' => $users,
            'additional' => $offsetTime,
            'date' => $currentDateTime,
            'assessmentCalls' => isset($lastLogin[0]->daily_work_time) ? count($lastLogin[0]->daily_work_time) : 0,
            'comment' => isset($lastLogin[0]->comment) ? $lastLogin[0]->comment : '',
        ];
  
        return view('assestStatistics')->with('data', $data); ; 
    }

    public function addTime(Request $request) {

        $userId = $request->input('id') ? $request->input('id') : 0; 

        $currentDateTime = date("Y-m-d");
       
        if($request->input('date')) {
            $currentDateTime = date($request->input('date'));
        }

        if (Auth::user() && Auth::user()->role == 0){
            $userId = Auth::user()->id;
        }
 
        // $lastLogin = DB::select("select * from user_report where user_id=$userId and date LIKE '%$currentDateTime%' order by id desc limit 1");

        DB::select("update user_report set call_offset_time='".$request->input('time')."' where date LIKE '%".date($request->input('date'))."%'");

        return ['message'=>'Sizin güzəşt vaxtınız əlavə olundu'];
 
    }

    public function userComment(Request $request) {

        $userId = $request->input('id') ? $request->input('id') : 0; 

        $currentDateTime = date("Y-m-d");
       
        if($request->input('date')) {
            $currentDateTime = date($request->input('date'));
        }

        if (Auth::user() && Auth::user()->role == 0) {
            $userId = Auth::user()->id;
        }
 
        $lastLogin = DB::select("select * from user_report where user_id=$userId and date LIKE '%$currentDateTime%' order by id desc limit 1");

        DB::select("update user_report set comment='".$request->input('comment')."' where id = ".$lastLogin[0]->id);

        return ['message'=>'Sizin rəyiniz əlavə olundu'];

    }

    public function updateCritery(Request $request) {

        $id = $request->input('id');

        $critery = $request->input('critery');

        $update = DB::select("update criterias set max_score=$critery where id = $id");
        
        return ['status' => 200, 'message' => 'Məlumat yenisi ilə əvəz olundu'];
    } 

    public function completeMonth() {
 
        $acceptDateTime = date("Y-m-d", time() - 40*86400); 

        DB::select("update assessment set is_accept=1 where is_active=1 and is_accept=0 and appraiser_date > '$acceptDateTime'");

        return redirect('/');

    }

    public function dailyJob() {

        $yesterdayDateTime = date("Y-m-d", time() - 86400);
        $acceptDateTime = date("Y-m-d", time() - 8*86400); 

        DB::select("update assessment set is_active=1 where supervisor_id= ".Auth::user()->id." and appraiser_date <= '$yesterdayDateTime'");
        DB::select("update assessment set is_accept=1 where appraiser_date = '$acceptDateTime'");

        echo '<h1> Proses başa çatdı! </h1>';

    }

    public function callTransfer() {
 
        date_default_timezone_set("Asia/Baku");

        $lastCalls = DB::select('select * from monthly_calls order by id desc limit 1');

        $date = $lastCalls[0]->start_date;

        $currentDateTime = date("Y-m-d");

        $Onetime = strtotime($currentDateTime);
        $Onefinal = date("Y-m-d", strtotime("-4 day", $Onetime));
        
        $calls = DB::select("select * from calls_backup where LENGTH(citizen_number)>4 and start_date > '$Onefinal' and start_date > '$date'");
        
        if(count($calls)>0) {

            DB::select("DELETE FROM `monthly_applications` ORDER BY id DESC LIMIT 1");

            // $groups = collect($calls)->split(10);

            // $groups->all();
  
            // if(count($groups[0])>0){
            //     $this->callTransferLoop($groups[0]);
            // }

            // if(count($groups[1])>0){
            //     $this->callTransferLoop($groups[1]);
            // }

            // if(count($groups[2])>0){
            //     $this->callTransferLoop($groups[2]);
            // }

            // if(count($groups[3])>0){
            //     $this->callTransferLoop($groups[3]);
            // }

            // if(count($groups[4])>0){
            //     $this->callTransferLoop($groups[4]);
            // }

            // if(count($groups[5])>0){
            //     $this->callTransferLoop($groups[5]);
            // }

            // if(count($groups[6])>0){
            //     $this->callTransferLoop($groups[6]);
            // }

            // if(count($groups[7])>0){
            //     $this->callTransferLoop($groups[7]);
            // }

            // if(count($groups[8])>0){
            //     $this->callTransferLoop($groups[8]);
            // }

            // if(count($groups[9])>0){
            //     $this->callTransferLoop($groups[9]);
            // }

            foreach($calls as $call) {

                $applications = DB::select("select * from applications_backup where call_id=".$call->id);

                if(count($applications)>0) {
                    foreach($applications as $app) {
                       
                        DB::table('monthly_applications')->insert(json_decode(json_encode($app), true));
                      
                    }
                } 

                $beginDate = strtotime(Str::substr($call->start_date, 0, 19));
                $endDate = strtotime(Str::substr($call->end_date, 0, 19));
               
                $call->blob_exist = $this->callChecker($call->citizen_number, $beginDate*1000, $endDate*1000);
              
                DB::table('monthly_calls')->insert(json_decode(json_encode($call), true)); 

            }
        }
   
        $time = strtotime($currentDateTime);
        $final = date("Y-m-d", strtotime("-2 month", $time));

        $lastDeletedCall = DB::select("select * from monthly_calls where start_date <= '$final' order by id desc limit 1");
        
        if(isset($lastDeletedCall[0]->id)) {
            DB::select("DELETE FROM `monthly_applications` WHERE call_id <=".$lastDeletedCall[0]->id);
            DB::select("DELETE FROM `monthly_calls` WHERE start_date <= '$final'");
        }
 
        echo "<h1> Proses başa çatdı! </h1>";
    }

    public function callTransferLoop($calls) {
        foreach($calls as $call) {

            $applications = DB::select("select * from applications_backup where call_id=".$call->id);

            if(count($applications)>0) {
                foreach($applications as $app) {
                   
                    DB::table('monthly_applications')->insert(json_decode(json_encode($app), true));
                   
                }
            } 

            $beginDate = strtotime(Str::substr($call->start_date, 0, 19));
            $endDate = strtotime(Str::substr($call->end_date, 0, 19));
            
            $call->blob_exist = $this->callChecker($call->citizen_number, $beginDate*1000, $endDate*1000);
         
            DB::table('monthly_calls')->insert(json_decode(json_encode($call), true)); 

        }
    }

    public function callChecker($number, $beginDate, $endDate) {

        // $this->checkCookie();

        $url_path = 'https://cagri-rec01.asan.local:8440/ora/queryService/query/getSessions'; 
        
        $postdata = '{
            "requestParameters":[
                {"fieldName":"deviceRef","fieldConditions":[{"fieldOperator":"equals","fieldValues":["'.$number.'"]}], "paramConnector":"AND"},
                {"fieldName":"sessionDuration","fieldConditions":[{"fieldOperator":"greaterThan","fieldValues":[0],"fieldConnector":"AND"},
                {"fieldOperator":"lessThan","fieldValues":[1540000]}],"paramConnector":"AND"},
                {"fieldName":"sessionState","fieldConditions":[{"fieldOperator":"equals","fieldValues":["ACTIVE"],"fieldConnector":"OR"},
                {"fieldOperator":"equals","fieldValues":["CLOSED_NORMAL"],"fieldConnector":"OR"},
                {"fieldOperator":"equals","fieldValues":["CLOSED_ERROR"]}], "paramConnector":"AND"},
                {"fieldName":"sessionStartDate","fieldConditions":[{"fieldOperator":"between","fieldValues":['.$beginDate.', '.$endDate.']}]}
            ],
            "pageParameters":{"offset":0,"limit":100},
            "sortParameters":[{"byFieldName":"sessionStartDate","order":"DESC"}]
        }';

        $ch = curl_init($url_path);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); 
        curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookie.txt');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        $result = curl_exec($ch);
        curl_close($ch);
        if(json_decode($result)->responseCode === 2000) {
            return 1;
        } else {
            return 0;
        }

    }
 
    //****************** timer ************************//

    public function setHour($totalSeconds) {
        $second = $this->pad($totalSeconds % 60);
        $minute = $this->pad(floor($totalSeconds / 60))>59 ? $this->pad($this->pad(floor($totalSeconds / 60)) % 60) : $this->pad(floor($totalSeconds / 60));
        
        $hour = $this->pad(floor($totalSeconds / 3600));
        return $hour.':'.$minute.':'.$second;
    }

    public function setTime($totalSeconds) {
        $second = $this->pad($totalSeconds % 60);
        $minute = $this->pad(floor($totalSeconds / 60));
        return $minute.':'.$second;
    }
    
    public function pad($val) {
        $valString = $val . "";
        if (strlen($valString) < 2) {
            return "0" . $valString;
        } else {
            return $valString;
        }
    }

    public function diffSecond($callEndDate, $callStartDate) {

        if($callEndDate != null) {
            $endDate = new DateTime($callEndDate);
            $beginDate = new DateTime($callStartDate);

            $diff = $endDate->diff($beginDate);
            $sec = ((($diff->format("%a") * 24) + $diff->format("%H")) * 60 + $diff->format("%i")) * 60 + $diff->format("%s");

            return $sec;

        } else {

            return 0;
            
        }
    }
 
    public function allUsers() {

        $cond = Auth::user() ? "" : " where asan_id=1";

        $operators = DB::select('select o.id, o.asan_id, o.full_name, o.user_name, o.phone_number from operators o'.$cond);

        return view('user')->with('data',$operators); 
    }

    public function userStatus($type, $id) {
        $type = $type===0 ? null : $type;
        $supervisorId = isset(Auth::user()->supervisor_id) ? Auth::user()->supervisor_id : 0;
        DB::select('UPDATE `operators` SET `asan_id` = ?, type = ? WHERE `operators`.`id` =?', [$type, $supervisorId, $id]);

        return redirect('/users');
    }

}
