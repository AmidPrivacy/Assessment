<?php

namespace App\Http\Controllers\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\UserReport;
use App\Models\User;
use App\Http\Controllers\HomeController; 
use Illuminate\Support\Facades\Cookie;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function login(Request $request)
    {
        $this->validateLogin($request);

        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        if(Auth::attempt(['email' => $request->email, 'password' => $request->password])) {

            date_default_timezone_set("Asia/Baku");
            // dd(Auth::user()->id);
            if(Auth::user()->role == 0) {
                $currentDateTime = date("Y-m-d H:i:s");
                $currentTime = date("H:i");

                $checkLogin = DB::select("SELECT DISTINCT date FROM user_report 
                WHERE user_id=".Auth::user()->id." and DATE_FORMAT(date, '%Y %m %d') = DATE_FORMAT('$currentDateTime', '%Y %m %d') ORDER BY date desc"); 

                $lastLogin = DB::select("select id, daily_login from user_report where user_id=".Auth::user()->id." order by id desc limit 1");

                Cookie::queue(Cookie::make('checked', $currentDateTime, 600));
               
                if(count($checkLogin) == 0) {

                    $report = new UserReport();
                    $report->user_id = Auth::user()->id;
                    $report->daily_login = json_encode(array(['startTime'=>$currentTime, 'endDate'=>'']));
                    $report->date = $currentDateTime;
                    $report->save(); 
                    
                } else {

                    $update = UserReport::find($lastLogin[0]->id);

                    if(round((strtotime('23:59') - strtotime(date("H:i"))), 1) > 0) {

                        $arr = json_decode($lastLogin[0]->daily_login);  

                        if($arr[count($arr)-1]->endDate =='') {
                            $arr[count($arr)-1]->endDate = date("H:i"); 
                        }    

                        array_push($arr, ['startTime'=>$currentTime, 'endDate'=>'']);

                        $update->daily_login = json_encode($arr);
                    }
                    
                    $update->save();
                    
                }

                $user =User::find(Auth::user()->id);
                $user->remember_token = $currentDateTime;
                $user->save(); 

                

            }

        }  else {
            $this->incrementLoginAttempts($request); 
        }

        $this->incrementLoginAttempts($request);
        return $this->sendFailedLoginResponse($request);
    }

    public function logout(Request $request) {
        
        date_default_timezone_set("Asia/Baku");

        if(Auth::user()->role == 0) {

            // $currentDateTime = date("Y-m-d H:i:s");

            $lastLogin = DB::select("select id, daily_login from user_report where user_id=".Auth::user()->id." order by id desc limit 1");

            
            $update = UserReport::find($lastLogin[0]->id);
            
            $arr = json_decode($lastLogin[0]->daily_login);  
            
            if($arr[count($arr)-1]->endDate =='') {
                $arr[count($arr)-1]->endDate = date("H:i"); 
                // dd($arr[count($arr)-1]);
            }  
            
            $update->daily_login = json_encode($arr);
            // dd($update);
            if($update->save()){
                Auth::logout();
                return redirect('/login');
            }
            
        } else {
            Auth::logout();
            return redirect('/login'); 
        }

    }
}
