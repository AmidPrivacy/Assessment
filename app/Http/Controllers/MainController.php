<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\DB;

use Illuminate\Support\Str;
 
use Illuminate\Support\Facades\Cookie;

use App\Models\Assessment;

use App\Models\AssessmentCriterias;

use Exception; 
 
use DateTime;

class MainController extends Controller
{
   
    public function assestCalls($id) {

     
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

        // dd($calls['calls']);
        foreach($calls['calls'] as $item) {

            $callTypes = json_decode($item->wrongSelection);
            $type = ""; 
            if ($callTypes !== 0 && $callTypes !== 1 && $callTypes !== null) {
                // dd($callTypes);
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

            // dd($item);
            $renuwCount += isset($item->renew_calls) ? count(json_decode($item->renew_calls)) : 0;
            
            $closedCount += isset($item->closed_calls) ? count(json_decode($item->closed_calls)) : 0;

            $assessmentTimeSum += $item->assessmentTime ? $item->assessmentTime : 0;
            $playTimeSum += $item->playTime ? $item->playTime : 0;
            $notListenTime += $item->unPlayTime ? $item->unPlayTime : 0;
            $specialTimeSum += $item->specialTime ? $item->specialTime : 0;
            $audioAllTimes += $item->audioTime ? $item->audioTime : 0;
            $item->complaint = count(DB::select('SELECT c.id FROM complaint_criterias c WHERE c.assessment_criterias_id=?',[$item->assessmentId]));     
            $item->activeComplaint = count(DB::select('SELECT c.id FROM complaint_criterias c WHERE c.curator_status=1 and c.assessment_criterias_id=?',[$item->assessmentId])) !==0;     
        }

        $times = [
            'assessmentTimeSum'=>$this->setHour($assessmentTimeSum), 
            'playTimeSum' => $this->setHour($playTimeSum), 
            'notListenTime' => $this->setHour($notListenTime), 
            'specialTimeSum' => $this->setHour($specialTimeSum), 
            'audioAllTimes' => $this->setHour($audioAllTimes)
        ];

        // dd($calls);
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
        // dd($data);
        return  view('assest')->with('data',$data); 
    }

    public function serviceCalls(Request $request)
    {
      
        // dd($request->all());

        $response = $this->apiRequest($request->all());

        if($response) {
            $response = json_decode($response);
            if($response->result){
                return [
                    "notWork" => $response->data->non_working_days, 
                    "selected_days_count" => $response->data->selected_days_count, 
                    "services" => $response->data->service_data,
                    "total_service_count" => $response->data->total_service_count,
                    "status" => "200",
                    "message" => ""
                ];
            } else {
                return [
                    "status" => "500",
                    "message" => $response->message
                ];
            }
        } else {
            return [
                "status" => "500",
                "message" => "CRM ilə əlaqə yaratmaq mümkün olmadı"
            ];
        }
 
    }

    public function completeAssessment($id) {

        $acceptDateTime = date("Y-m-d", time() - 5*86400); 

        DB::select("update assessment set is_active=1 where id=?",[$id]);
        DB::select("update assessment set is_accept=1 where appraiser_date = '$acceptDateTime'");

        return redirect()->back(); 

    }
 
    public function resetAssessment($id)
    {
        $assessment = Assessment::find($id); 

        $assessment->user_id = $assessment->user_id."0";
        $assessment->save();

        return redirect()->back(); 

    }

    public function assessmentCalls($servicesCount, $allCallCount, $id, $number, $startDate, $endDate, $count)
    {

         
        $data = array(  
            "internal_number"=> $number, 
            "start_date" => $startDate,
            "end_date" => $endDate,
            "call_count" => $count
        );

        $response = $this->apiRequest($data);
        
        if($response) {
            $response = json_decode($response);
            // dd($response);
            if($response->result){

                $oldAssessments = Assessment::where("user_id", '=', $id)->get(); 
                if(count($oldAssessments)>0 && $oldAssessments[count($oldAssessments)-1]["is_active"] ===0){ 
                    return [
                        "status" => "500",
                        "message" => "Seçilən əməkdaş başqası tərəfindən qiymətləndirilir"
                    ];
                } else {
                 
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
            
                        foreach ($response->data as $item)
                        {     
            
                            array_push($assessmentCriterias, [
                                'assessment_id' => $assessment->id,
                                'citizen_number' => $item->citizen_number,
                                'count' => 0,
                                'organization_id' => $item->organization_id,
                                'service_id' => $item->service_id,
                                'recording_id' => $item->recording_id,
                                'call_id' => isset($item->call_id) ? $item->call_id : 0,
                                'call_date' => $item->created_at,
                                'criterias' => null,
                                'is_active' => 1
                            ]);
    
                        }
            
                        $inserted = DB::table('assessment_criterias')->insert($assessmentCriterias);
                        
                        if($inserted){
                            return ['status' => 200, 'calls'=> $this->getAssessmentById($assessment->id)['calls'], 'id'=>$assessment->id];
                        }
                    } else {
                        return [
                            "status" => "500",
                            "message" => "Xəta baş verdi"
                        ];
                    }
                }
 
            } else {
                return [
                    "status" => "500",
                    "message" => $response->message
                ];
            }
        } else {
            return [
                "status" => "500",
                "message" => "CRM ilə əlaqə yaratmaq mümkün olmadı"
            ];
        }

    }

    public function mediasenseAudioWindow($audioId, $assessmentId){

            if($assessmentId !=0){

                $assessmentCritery = DB::select("select a.id as id, a.criterias as criterias, a.wrong_selection as wrongSelection, 
                a.assessment_time as time, a.unplay_time as unplayTime, a.special_time as specialTime, a.audio_time as audioTime, a.play_time as playTime,
                a.evaluator_comment as comment, a.count as count, a.operator_comment as operatorComment, a.curator_comment as curatorComment,
                a.leader_comment as leaderComment from assessment_criterias a where a.id=?", [$assessmentId]);

                $selectedRole ='';

                if(Auth::user()){

                    if(Auth::user()->role !== null) {
                        $selectedRole = ' and (curator_status !=0 or leading_status != 0) ';
                    } 
                }
                    
                $complaints = DB::select("select c.id as id, c.leading_status as leadingStatus, c.curator_status as curatorStatus, c.critery_id as critery from complaint_criterias c where c.assessment_criterias_id=?".$selectedRole, [$assessmentCritery[0]->id]);

                return ["code" => 200, "responseBody" => 'Success', "assessmentCritery" => $assessmentCritery[0], 'complaints' => $complaints, 'role' => (Auth::user() ? Auth::user()->role : null)];

            } else {
                return ["code" => 200, "responseBody" => 'Success'];
            }
      
    }

    public function mediasenseAudio($audioId, $assessmentId){

        $url = 'https://callcenter.sipline.local/api/get-recording';

        $op_number = DB::select("select o.phone_number from assessment_criterias a_c inner join assessment a on a_c.assessment_id = a.id inner join operators o on a.user_id=o.id where a_c.id=?",[$assessmentId]);

        $data = array(
            "token" => "SAxbG5qjS1XWEayKJI2IVjQXz1IxfgPOfpc1SVz9WVxTmu5Bp7",
            "recording_id" => $audioId,
            "internal_number"=> $op_number[0]->phone_number 
        );

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        
        
        // Receive server response ...
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $server_output = curl_exec($ch);
        
        $info = curl_getinfo($ch);

        curl_close ($ch); 
  
        if($info["http_code"]==200){

            return $server_output;

        } else {

            return ["code" => 2001, "responseMessage" => 'Audio tapılmadı'];

        }
    }

    public function getAssessmentById($id) {
  
        $assessmentScore = DB::select('select score_count as score, score_percent as percent, appraiser_date as date from assessment where id = '.$id); 

        $oldQuery = "SELECT a.id, a_c.call_date as beginDate, a_c.recording_id, a.score_count as score, '' as serviceName, 
        c_b.citizen_number, a_c.evaluator_comment as comment, a_c.id as assessmentId, a_c.play_time as playTime, a_c.unplay_time as unPlayTime, 
        a_c.special_time as specialTime, a_c.assessment_time as assessmentTime, a_c.audio_time as audioTime, a_c.is_assessment, a_c.renew_calls, 
        a_c.closed_calls, a_c.criterias as criterias, a_c.call_id as callId, a_c.wrong_selection as wrongSelection, '' as organName, 
        a_c.count as count from assessment a inner join assessment_criterias a_c on a.id = a_c.assessment_id 
        left join calls_backup c_b on a_c.call_id=c_b.id  where a.id=".$id." order by a_c.id asc";

        $newQuery ="SELECT a.id, a_c.call_date as beginDate, a_c.recording_id, a.score_count as score, s.name as serviceName, a_c.citizen_number, a_c.evaluator_comment as comment, a_c.id as assessmentId,
        a_c.play_time as playTime, a_c.unplay_time as unPlayTime, a_c.special_time as specialTime, a_c.assessment_time as assessmentTime, a_c.audio_time as audioTime, a_c.is_assessment, a_c.renew_calls, a_c.closed_calls,
        a_c.criterias as criterias, a_c.call_id as callId, a_c.wrong_selection as wrongSelection, o.name as organName, a_c.count as count 
        from assessment a inner join assessment_criterias a_c on a.id = a_c.assessment_id inner join assessment_services s on a_c.service_id = s.id 
        inner join assessment_organizations o on a_c.organization_id = o.id where a.id=".$id." order by a_c.id asc";

        $selectedQuery = $assessmentScore[0]->date>"2022-01-05" ? $newQuery : $oldQuery;

        // dd($selectedQuery);
        
        $calls = DB::select($selectedQuery);
        // dd($calls);
        return ["calls"=>$calls, "score" => $assessmentScore[0]->score, "percent" => $assessmentScore[0]->percent];

    }

    // public function getAssessmentById($id) {
  
    //     $assessmentScore = DB::select('select score_count as score, score_percent as percent from assessment where id = '.$id); 

    //     $calls = DB::select("SELECT a.id, a_c.call_date as beginDate, a_c.recording_id, a.score_count as score, s.name as serviceName, a_c.citizen_number, a_c.evaluator_comment as comment, a_c.id as assessmentId,
    //         a_c.play_time as playTime, a_c.unplay_time as unPlayTime, a_c.special_time as specialTime, a_c.assessment_time as assessmentTime, a_c.audio_time as audioTime, a_c.is_assessment, a_c.renew_calls, a_c.closed_calls,
    //         a_c.criterias as criterias, a_c.call_id as callId, a_c.wrong_selection as wrongSelection, o.name as organName, a_c.count as count 
    //         from assessment a inner join assessment_criterias a_c on a.id = a_c.assessment_id inner join assessment_services s on a_c.service_id = s.id 
    //         inner join assessment_organizations o on a_c.organization_id = o.id where a.id=".$id.' order by a_c.id asc');
    //     // dd($calls);
    //     return ["calls"=>$calls, "score" => $assessmentScore[0]->score, "percent" => $assessmentScore[0]->percent];

    // }

    public function apiRequest($body) {

        $url = 'https://callcenter.sipline.local/api/get-user-data';
 
        $userTypeId = isset(Auth::user()->supervisor_id) ? Auth::user()->supervisor_id : 0;

        $token = array("token" => "SAxbG5qjS1XWEayKJI2IVjQXz1IxfgPOfpc1SVz9WVxTmu5Bp7", "user_type"=>$userTypeId);
        $isIpoteka = array("is_ipoteka" => Auth::user()->centerId===0 && Auth::user()->supervisor_id===0);

        $data = array_merge($body, $token, $isIpoteka);
 
    //    dd($data);
 
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        
        
        // Receive server response ...
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  
        $server_output = curl_exec($ch);
        
        $info = curl_getinfo($ch);

        curl_close ($ch);
        
        return $server_output;

    }

    
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

    public function menuStatistics() {
        // $this->checkCookie();
        $operators = DB::select('select o.id, o.full_name from operators o where o.asan_id = 1');

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

}
