<?php

namespace App\Http\Controllers;

use Symfony\Component\HttpFoundation\StreamedResponse;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\DB;

use Exception;
use Session;
use App\Assessment;

use App\PackageAssessment;

use App\AssessmentCriterias;

class OperatorController extends Controller
{
  
 
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    { 

        $operators = DB::select('select o.id, o.full_name from operators o where o.asan_id = 1');
        
        $criterias = DB::select('select c.id, c.name, c.score from criterias c');
         
        foreach($operators as $operator) {

            $assessment = DB::select('select a.id, a.begin_date as beginDate, a.end_date as endDate, a.is_active from assessment a where a.user_id='.$operator->id.' ORDER BY a.id DESC LIMIT 1');
            $operator->beginDate = count($assessment) > 0 ? $assessment[0]->beginDate : 'Qiymətləndirmə edilməyib'; 
            $operator->endDate = count($assessment) > 0 ? $assessment[0]->endDate : '';
            $operator->is_active = count($assessment) > 0 ? $assessment[0]->is_active : null; 
            
            $operator->assessmentId  = count($assessment) > 0 ? $assessment[0]->id : 0; 
            $callCount = null;
            $selectedCallCount = null;
            if(count($assessment) > 0){
                $callCount = DB::select('select a.id from assessment_criterias a where a.assessment_id = ?', [$assessment[0]->id]);
                $selectedCallCount = DB::select('select a.id from assessment_criterias a where a.criterias is not null and a.assessment_id = ?', [$assessment[0]->id]);
                $callCount = count($callCount);
                $selectedCallCount = count($selectedCallCount);
            }  
            
            $operator->callCount = $callCount;
            $operator->callAssest = $selectedCallCount;
        }

        //Mediasense sign for session 

        $data = ['operators' => $operators, 'criterias' => $criterias]; 
        // dd($data);
        return view('index')->with('data',$data); 
    }

    public function assestCalls($id) {

        $check = DB::select('select is_active from assessment where id = '.$id); 
        if($check[0]->is_active==1){
            $calls = $this->getAssessmentById($id);
            $criterias = DB::select('select c.id, c.name, c.score from criterias c');

            $callTypeList = DB::select('select * from call_types');

            $operator = DB::select('SELECT o.full_name, o.id FROM assessment a INNER JOIN operators o on a.user_id=o.id WHERE a.id=?',[$id]);
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
                    $item->status =
                        $item->is_assessment ==1 ? "Qiymətləndirilib" : "Qiymətləndirilməyib"; 
                }
    
                $renuwCount += isset($item->renew_calls) ? count(json_decode($item->renew_calls)) : 0;
                
                $closedCount += isset($item->closed_calls) ? count(json_decode($item->closed_calls)) : 0;

                // dd($item);
                $assessmentTimeSum += $item->assessmentTime ? $item->assessmentTime : 0;
                $playTimeSum += $item->playTime ? $item->playTime : 0;
                $notListenTime += $item->unPlayTime ? $item->unPlayTime : 0;
                $specialTimeSum += $item->specialTime ? $item->specialTime : 0;
                $audioAllTimes += $item->audioTime ? $item->audioTime : 0;
                $item->complaint = count(DB::select('SELECT c.id FROM complaint_criterias c WHERE c.assessment_criterias_id=?',[$item->assessmentId]));     
            }

            $times = [
                'assessmentTimeSum'=>$this->setTime($assessmentTimeSum), 
                'playTimeSum' => $this->setTime($playTimeSum), 
                'notListenTime' => $this->setTime($notListenTime), 
                'specialTimeSum' => $this->setTime($specialTimeSum), 
                'audioAllTimes' => $this->setTime($audioAllTimes)
            ];


            $data = [
                'assessment'=> $calls, 
                'id'=> $id,
                'renuwCount' => $renuwCount,
                'closedCount' => $closedCount,
                'criterias' => $criterias, 
                'fullName' => $operator[0]->full_name, 
                'operatorId' => $operator[0]->id, 
                'dateBetween' => $date[0], 
                'accept' => $date[0]->accept,
                'times' => $times,
                'types' => $callTypeList 
            ];
            
            // dd($data);
            
            return  view('assest')->with('data',$data); 
        } else {
            return "<h1 style='text-align:center'>Qiymətləndirmə təsdiqlənməyib. Zəhmət olmasa gözləyin təsdiqləndikdən sonra hesabınızda görünəcək</h1>";
        }
    }

    public function callList($id, $startDate, $endDate) {

        $services = DB::select("SELECT s.id, s.service as name from organization_services s");

        $calls = DB::select("SELECT c.id, c.citizen_number, c.start_date, a.service_id as service, DATEDIFF(c.end_date, c.start_date) as diffDate, c.start_date as callStart, c.end_date as callEnd, s.service as serviceName, st.name as organName, c.end_date as endDate  
        
        from monthly_calls c inner join monthly_applications a on c.id = a.call_id inner join organizations st on a.organ_id = st.id inner join organization_services s on a.service_id = s.id WHERE LENGTH(c.citizen_number)>4 and c.operator_id=".$id."  AND NOT(a.muracietin_novu = 'Şikayət vərəqəsi')
        
        AND c.start_date BETWEEN '".date('Y-m-d', strtotime($startDate))."' AND '".date('Y-m-d', strtotime($endDate))."' ORDER BY c.start_date ASC");
 
        $callServices = [];
        foreach($services as $service) { 
            $service->call = [];
            
            foreach($calls as $call){ 
                if($service->id===$call->service){ 
                    array_push($service->call, $call);
                }
            } 

            if(count($service->call)>0){
                array_push($callServices, $service);
            }
        } 

        $exist = DB::select("SELECT DISTINCT DATE(start_date) FROM `monthly_calls` WHERE `start_date` BETWEEN '".date('Y-m-d', strtotime($startDate))."' AND '".date('Y-m-d', strtotime($endDate))."'");
 
        $isnotExist = (int)date_diff(date_create($startDate), date_create($endDate))->format('%a') - count($exist);

        return ['services'=> $callServices, 'notWork' => $isnotExist];

    }

    public function assessment($number) {

        
        $operator =  DB::table('operators')->where('phone_number', $number)->get();   

        $id =  $operator[0]->id;

        if(!isset($_COOKIE["number"])) {
            setcookie('number',$number, time() + (86400 * 1), "/");
            setcookie('name', $operator[0]->full_name, time() + (86400 * 1), "/");
        } 

        $assessment = DB::table('assessment')->where('user_id', $id)->where('is_package', 0)->where('is_active', 1)->get();
        
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

        $data = ['assessment'=>$assessment, 'criterias' => $criterias, 'package' => $package, 'fullName' => $operatorList[0]->full_name]; 
        // dd($data);
        return view('operatorAssessment')->with('data', $data); 
    }

    public function packageDetailAssessment($id) {

        $assessmentPackage = DB::table('package_assessment')->where('id', $id)->get();
        $operatorName = '';
        $operatorId = 0;
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
                $item->score_count = $criteryCount>0 ? ceil($criterySum/$criteryCount) : 0;
                $item->criterias = $this->assessmentCriterias($item->id);
            }
           
            array_push($assessmentList, $assessment[0]);
        }

        $criterias = DB::select('select c.id, c.name, c.score from criterias c');
        $data = ['assessment'=>$assessmentList, 'criterias' => $criterias, 'fullName' => $operatorName, 'operatorId' => $operatorId];  
        // dd($data);
        return view('operatorPackageAssessment')->with('data', $data); 
    }

    public function assessmentCriterias($id) {
        return DB::table('assessment_criterias')->where('assessment_id', $id)->get();
    }

    public function assessmentCalls($servicesCount, $allCallCount, $id, $startDate, $endDate, $count) {

        $count = (int)$count;

        $services = $this->callList($id, $startDate, $endDate);

        $calls = []; 
        // dd($services['services']);
        if(count($services['services'])>$count){
            foreach($services['services'] as $service) {
                if(count($service->call)>0 && $count>0){ 

                    $randomCount = rand(0, count($service->call)-1);
                    // if(strlen($service->call[$randomCount]->citizen_number)>4){

                        $count--;  
                        array_push($calls, $service->call[$randomCount]);
                        unset($service->call[$randomCount]); 

                    // }
                }
            }
        } else{   
            // dd(ceil($count/count($services['services'])));
            for($i = 0; (ceil($count/count($services['services']))) > $i; $i++) {
                
                foreach($services['services'] as $service) {
                    if(count($service->call)>0 && $count>0){ 

                        $randomCount = rand(0, count($service->call)-1); 

                        if(strlen($service->call[$randomCount]->citizen_number)>4){
                            $count--;  
                            if(array_push($calls, $service->call[$randomCount])){
                                unset($service->call[$randomCount]); 
                                $service->call = array_values($service->call);
                            } 
                        }
                        
                    }
                }
            } 
        }
 
        //assessment  
        $assessment = new Assessment(); 
        $assessment->begin_date = date('Y-m-d', strtotime($startDate));
        $assessment->end_date = date('Y-m-d', strtotime($endDate));;
        $assessment->user_id = $id; 
        $assessment->services_count = $servicesCount; 
        $assessment->calls_count = $allCallCount; 
        $assessment->supervisor_id = Auth::user()->id;
        // dd($assessment);

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
                return ['status' => 200, 'calls'=> $calls, 'id'=>$assessment->id];
            }
        } 
        
    }

    public function insertComplaint(Request $request)
    { 
        $complaints = [];

        if($request->input('status') == 0) {
            foreach($request->input('criteries') as $item)
            {     

                array_push($complaints, [
                    'assessment_criterias_id' => (int)$request->input('assestmentId'),
                    'operator_id' => (int)$request->input('operatorId'),
                    'curator_status' => 1,
                    'critery_id' => (int)$item 
                ]);

            }

            $inserted = DB::table('complaint_criterias')->insert($complaints);
        }else {
            foreach($request->input('criteries') as $item) {
                DB::select('update complaint_criterias set leading_status = 2 where id=?', [$item]);
            }
        }
        $criteryAssessment = assessmentCriterias::find((int)$request->input('assestmentId'));
        $criteryAssessment->operator_comment = $request->input('comment');

        if($criteryAssessment->save()){
            return ['status' => 200, 'message'=> 'Şikayət əlavə olundu']; 
        }
    } 


    public function getAssessmentById($id) {

        $assessmentScore = DB::select('select score_count as score, score_percent as percent from assessment where id = '.$id); 

        $calls = DB::select("SELECT a.id, a.score_count as score, a.begin_date as beginDate, a.end_date as endDate, s.service as serviceName, c.citizen_number, a_c.evaluator_comment as comment, a_c.id as assessmentId,
            a_c.play_time as playTime, a_c.unplay_time as unPlayTime, a_c.special_time as specialTime, a_c.assessment_time as assessmentTime, a_c.audio_time as audioTime, a_c.is_assessment, a_c.renew_calls, a_c.closed_calls,
            a_c.criterias as criterias, a_c.call_id as callId, a_c.wrong_selection as wrongSelection, c.start_date as callStart, o.name as organName, c.end_date as callEnd, a_c.count as count 
            from assessment a inner join assessment_criterias a_c on a.id = a_c.assessment_id inner join monthly_calls c on a_c.call_id = c.id 
            inner join monthly_applications app on c.id = app.call_id inner join organization_services s on app.service_id = s.id 
            inner join organizations o on app.organ_id = o.id where a.id=".$id );
 
        return ["calls"=>$calls, "score" => $assessmentScore[0]->score, "percent" => $assessmentScore[0]->percent];

    }

    public function getPackageById($id) {

        $package = DB::select("select p.id as id, p.score as completedScore, p.assessment as assessment, o.full_name as userName, p.date as date,
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
           
            $item = DB::select("select a_c.assessment_id, a.begin_date, a.end_date, a_c.assessment_time, a_c.call_id, a.calls_count, a_c.evaluator_comment, a_c.count, a_c.criterias, 
            a.score_count, a.services_count, a.supervisor_id as supervisorId, a.user_id as userId from assessment a left join assessment_criterias a_c on a.id=a_c.assessment_id where a.id=?", [$assessment]);
            $item[0]->wrong_selection = count($wrong_selection);
            $item[0]->assessmentTime = $this->setTime($second);
            $item[0]->isAssessment = count($isAssessment);
            array_push($list, $item[0]);

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

                $assessmentCritery = DB::select("select a.id as id, a.criterias as criterias, a.wrong_selection as wrongSelection, a.assessment_time as time, a.evaluator_comment as comment, a.count as count
                from assessment_criterias a where a.id=?", [$assessmentId]);
                return ["code" => 200, "responseBody" => 'Success', "assessmentCritery" => $assessmentCritery[0]];

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

            $audio = json_decode($result)->responseBody->sessions[0]->urls->mp4Url; 
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

            return ["code" => json_decode($result)->responseCode, "responseMessage" => json_decode($result)->responseMessage]; 

        } 
    }

    public function acceptAssessment($id) {

        $update = Assessment::find($id);

        $update->is_accept = 1;

        if($update->save()) {
            return ['status' => 200, 'message'=>'Uğurlu əməliyyat'];
        } else {
            return ['status' => 500, 'message'=>'Xəta baş verdi'];
        }

    }

    //****************** timer ************************//
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

}
