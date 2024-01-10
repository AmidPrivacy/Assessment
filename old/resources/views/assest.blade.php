<!doctype html>
<html lang="az">
  <head>  
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1"> 
      <title>Qiymətləndirilən zənglər</title> 
      <!-- Fonts -->
      <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">
      <link href="{{ asset('css/custom.css') }}" rel="stylesheet"> 
      <link href="{{ asset('css/assessment.css') }}" rel="stylesheet"> 
      <link
          href="{{ asset('css/all.min.css') }}"
          rel="stylesheet"
          type="text/css"
      />
     
      <link
          href="{{ asset('css/bootstrap.min.css') }}"
          rel="stylesheet"
          type="text/css"
      />
   
      <link
          href="{{ asset('css/bootstrap-datepicker3.standalone.min.css') }}"
          rel="stylesheet"
          type="text/css"
      />

      <link
          href="{{ asset('css/modal.css') }}"
          rel="stylesheet"
          type="text/css"
      />
    
      <script src="{{ asset('js/jquery-3.0.0.js') }}"></script>
  </head>
  <body style="background-image: url('/img/operator_background.svg'); background-color: #2090fe33">
    <div class="assessment-header"> 
      @include('layouts.dropdown')
      {{-- All time list --}}
      <div class="all-listening-times">
        <div id="audio-all-times">
          Zəng vaxtı
          <div class="supervisor-counter audio-all-times" data-second="0"> {{ $data['times']['audioAllTimes'] }} </div>
        </div>
        
        <div id="supervisor-counter">
          Qiymətləndirmə vaxtı
          <div class="supervisor-counter" data-second="0">{{ $data['times']['assessmentTimeSum'] }}</div>
        </div>
        
        <div id="play-time">
          Dinləmə vaxtı
          <div class="supervisor-counter play-time" data-second="0">{{ $data['times']['playTimeSum'] }}</div>
        </div>
         
        <div id="not-listen-time" style="display: none">
          Dinlənilməyən vaxt
          <div class="supervisor-counter not-listen-time" data-second="0">{{ $data['times']['notListenTime'] }}</div>
        </div>
        
        <div id="special-calculate-time" style="display: none">
          Kənarlaşma vaxtı
          <div class="supervisor-counter special-calculate-time" data-second="0">{{ $data['times']['specialTimeSum'] }}</div>
        </div> 
        
      </div>
    </div>

    <div class="container operator-assessment">
      @if(Auth::user())
      <div class="assessment-left-arrow">
        <a href="/">
          <i class="fas fa-home"></i>
        </a>
      </div>
      @endif
      <h3> {{ $data['fullName'] }} - {{ (int)$data['assessment']['percent'] }}% </h3>
      <h4 class="assessment-date-between"> {{ explode(' ', $data['dateBetween']->beginDate)[0].' - '.explode(' ', $data['dateBetween']->endDate)[0] }} </h4>
      <h5> tarixinə aid zənglərin qiymətləndirilməsi </h5>


      <img src="{{ asset('img/loading-audio.gif') }}" class="audio-loading selected-audio-loading" alt="">
      <div class="col-md-12 audio-player"> 
        <div class="container"> 
          <div class="waveform"></div> 
          <div id="waveform-time-indicator">
            <span class="time" id="timer">00:00:00 </span> 
          </div>
        </div>
        <button class="audio-play" type="button" data-play="0"><i class="fas fa-play"></i> <span>Play / Pause </span></button> 
      </div>

      <div class="new-critery-list"> 

        <div class="checked-critery" style="margin-bottom: 30px; margin-top: 25px; position: relative; top: 27px; display: none"> 
          <select class="callTypes" multiple>
            @foreach ($data['types'] as $item)
              <option value="{{ $item->id }}"> {{ $item->name }} </option>
            @endforeach 
          </select>
        </div>

        <ul class="list-group selected-items"> </ul> 
        <textarea class="call-comment selected-call-comment" cols="30" rows="4" placeholder="Zəng üçün rəy" disabled></textarea>

        @if(Auth::user() && Auth::user()->role ==1)
          <textarea class="complaint-comment selected-call-comment" cols="30" rows="4" placeholder="Şikayət üçün rəy" disabled></textarea>
          <textarea class="curator-comment selected-call-comment" cols="30" rows="4" placeholder="Kurator rəyi"></textarea>
        @elseif(Auth::user() && Auth::user()->role ==0)
          <textarea class="complaint-comment selected-call-comment" cols="30" rows="4" placeholder="Şikayət üçün rəy" disabled></textarea>
          <textarea class="curator-comment selected-call-comment" cols="30" rows="4" placeholder="Kurator rəy yazmayıb" disabled></textarea>
        @elseif(Auth::user() && Auth::user()->role ==2)
          <textarea class="complaint-comment selected-call-comment" cols="30" rows="4" placeholder="Şikayət üçün rəy" disabled></textarea>
          <textarea class="curator-comment selected-call-comment" cols="30" rows="4" placeholder="Kurator rəyi" disabled></textarea>
          <textarea class="leader-comment selected-call-comment" cols="30" rows="4" placeholder="Rəhbər rəyi"></textarea>
        @elseif(!(Auth::user()) && $_COOKIE["name"] == $data['fullName'])
          <textarea class="complaint-comment selected-call-comment" cols="30" rows="4" placeholder="Şikayət üçün rəy"></textarea>
          <div  class="complaint-done" data-operator='{{ isset($data["operatorId"]) ? $data["operatorId"] : 0 }}'>  <img src="{{ asset('img/loading.svg') }}" class="assessment-response-loading" alt=""> Şikayət et </div>
        @endif
        
        {{ csrf_field() }}
  
        <div  class="leader-done" data-operator='{{ isset($data["operatorId"]) ? $data["operatorId"] : 0 }}' data-score="{{ $data['assessment']['score'] }}">  <img src="{{ asset('img/loading.svg') }}" class="assessment-response-loading" alt=""> Göndər </div>
      </div> 
      <div class="list-group"  id="accordionExample">  
        @if(Auth::user())
        <button type="button" class="btn btn-primary" style="float: right; margin-bottom: 8px">Yenilənən zənglər({{ $data['renuwCount'] }}) </button> 
        <button type="button" class="btn btn-danger" style="float: right; margin-bottom: 8px; margin-right: 7px">Açılmayan zənglər({{ $data['closedCount'] }}) </button> 
        @endif
        <div class="table-scrool"> 
          <table>
            <thead>
              <tr>
                <th>Əlaqə nömrəsi</th>
                <th>Orqan</th>
                <th class="services-call-list">Xidmət</th>   
                <th>Yekun bal</th>
                <th>Status</th>
                <th>Şikayət</th>
                @if(Auth::user() !==null && Auth::user()->role !==0) <th>Dinləmə vaxtı</th> @endif
                <th></th>
              </tr>
            </thead>
            <tbody> 
              @foreach ($data['assessment']['calls'] as $item)
             
              @if(Auth::user() !==null && Auth::user()->role !==0)
              
                <tr  data-id='{{ $item->id }}' style='background-color: {{ $item->activeComplaint && Auth::user()->role==1 ? "antiquewhite" : "#fff" }} '>
                  <td>  
                    {{ $item->citizen_number }}
                  </td>
                  <td class="assessment-completed-score"> 
                    <span>{{ $item->organName }}</span> 
                  </td>
                  <td>
                    {{ $item->serviceName }}
                  </td>  
                  <td>
                    {{ $item->count }}
                  </td>
                  <td>
                    {{ $item->status }} {!! $item->comment !==null && $item->comment !== "Düzgün cavablandırılıb" ? '<i class="far fa-comments"></i>' : "" !!}
                  </td>
                  <td> 
                    {{ $item->complaint }}
                  </td>
                  @if(Auth::user() !==null && Auth::user()->role !==0)
                    <td> 
                      {{ $item->playTime != null ? $item->playTime." saniyə": "" }} 
                    </td>
                  @endif
                  <td> 
                    <button class="assessment-details assessment-calls-detail"  data-recording-id="{{ $item->recording_id }}"
                      data-call="{{ $item->callId }}" data-start-date="{{ $item->beginDate }}" 
                      data-number="{{ $item->citizen_number }}" data-assessment="{{ $item->assessmentId }}"  data-collapse="0">
                      <img src="{{ asset('img/loading.svg') }}" class="assessment-response-loading" alt="">
                      Ətraflı
                    </button>  
                    @if(isset($item->renew_calls) && $item->renew_calls !==null && Auth::user())
                    <a href="/renuw-calls/{{ $item->assessmentId }}" target="_blank">
                      <button type="button" class="btn btn-outline-info skip-calls">
                        Yenilənən  
                        <i class="fas fa-phone" style="margin-left: 7px"></i>
                      </button>
                    </a> 
                    @endif
                    @if(isset($item->closed_calls) && $item->closed_calls !==null && Auth::user())
                      <a href="/closed-calls/{{ $item->assessmentId }}" target="_blank">
                        <button type="button" class="btn btn-outline-danger skip-calls">
                          Açılmayan <i class="fas fa-phone-slash"></i>
                        </button>
                      </a>
                    @endif
                  </td>
                </tr>

              @else
                @if($item->status=="Qiymətləndirilib")
                <tr  data-id='{{ $item->id }}'>
                  <td>  
                    {{ $item->citizen_number }}
                  </td>
                  <td class="assessment-completed-score"> 
                    <span>{{ $item->organName }}</span> 
                  </td>
                  <td>
                    {{ $item->serviceName }}
                  </td>  
                  <td>
                    {{ $item->count }}
                  </td>
                  <td>
                    {{ $item->status }} {!! $item->comment !==null && $item->comment !== "Düzgün cavablandırılıb" ? '<i class="far fa-comments"></i>' : "" !!}
                  </td>
                  <td> 
                    {{ $item->complaint }}
                  </td>
                  @if(Auth::user() !==null && Auth::user()->role !==0)
                    <td> 
                      {{ $item->playTime }} saniyə
                    </td>
                  @endif
                  <td> 
                    <button class="assessment-details assessment-calls-detail" data-recording-id="{{ $item->recording_id }}"
                      data-call="{{ $item->callId }}" data-start-date="{{ $item->beginDate }}"
                      data-number="{{ $item->citizen_number }}" data-assessment="{{ $item->assessmentId }}"  data-collapse="0">
                      <img src="{{ asset('img/loading.svg') }}" class="assessment-response-loading" alt="">
                      Ətraflı
                    </button>  
                    @if(isset($item->renew_calls) && $item->renew_calls !==null && Auth::user())
                    <a href="/renuw-calls/{{ $item->assessmentId }}" target="_blank">
                      <button type="button" class="btn btn-outline-info skip-calls">
                        Yenilənən  
                        <i class="fas fa-phone" style="margin-left: 7px"></i>
                      </button>
                    </a> 
                    @endif
                    @if(isset($item->closed_calls) && $item->closed_calls !==null && Auth::user())
                      <a href="/closed-calls/{{ $item->assessmentId }}" target="_blank">
                        <button type="button" class="btn btn-outline-danger skip-calls">
                          Açılmayan <i class="fas fa-phone-slash"></i>
                        </button>
                      </a>
                    @endif
                  </td>
                </tr>
                @endif
              @endif
              
              @endforeach   
            </tbody>
          </table>
        </div>
        <button class="completed-assessment"> <img src="{{ asset('img/loading.svg') }}" class="package-response-loading" alt=""> Paketə əlavə et </button>
        @if(!Auth::user() && $data['accept']==0 && $_COOKIE["name"] == $data['fullName'])
        <div id="assessment-agreement">
          Qiymətləndirmənin yekunlaşmasına razılıq verirəm
          <input type="checkbox">
          {{ csrf_field() }}
          <button type="button" data-value="{{ $data['id'] }}" disabled> Qiymətləndirməni Yekunlaşdır </button>
        </div>
        @endif
      </div>
    </div>
   
    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
      @csrf
    </form>
    
      <div class="container">
        <dialog id="confirm-modal" class="modal-show">
          <div class="modal-content">
              <i class="fas fa-bookmark"></i>
              <h2 class="modal-title">Bildiriş</h2>
              <div class="modal-description"> </div>
              <div class="modal-options"> 
                <button class="btn btn-round btn-fill btn-fill-right option cancel " data-text="Bağla" data-request="true" onclick="document.querySelector('#confirm-modal').close()"></button>
              </div>
          </div>
        </dialog>
      </div>
      {{ csrf_field() }}

      <script src="{{ asset('js/popper.min.js') }}"></script>
      <script src="{{ asset('js/bootstrap.min.js') }}"></script>
      <script src="{{ asset('js/jquery.easing.min.js') }}"></script>  
      <script src="{{ asset('js/sweetalert2.all.min.js') }}"></script> 
      <script src="{{ asset('js/wavesurfer.js') }}"></script>  
      <script src="{{ asset('js/assessment.js') }}" ></script> 
      
  </body>
</html>