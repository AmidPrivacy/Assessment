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
 
    <div class="container operator-assessment">
      @if(Auth::user())
      <div class="assessment-left-arrow">
        <a href="/">
          <i class="fas fa-home"></i>
        </a>
      </div>
      @endif

      <h1 style="text-align: center; margin-top: 20px;"> {{ $data['status'] ? "Yenilənən" : "Açılmayan" }} zənglər </h1>


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

      <div class="list-group"  id="accordionExample">  
        <div class="table-scrool">
          <table>
            <thead>
              <tr>
                <th>Əlaqə nömrəsi</th>
                <th>Orqan</th>
                <th class="services-call-list">Xidmət</th>   
               
                <th></th>
              </tr>
            </thead>
            <tbody>
              @if($data['status'])
                @foreach ($data['renew'] as $item)
                <tr  data-id='{{ $item->id }}'>
                  <td>  
                    {{ $item->citizen_number }}
                  </td>
                  <td class="assessment-completed-score"> 
                    <span>{{ $item->organ }}</span> 
                  </td>
                  <td>
                    {{ $item->service }}
                  </td>  
                
                  <td> 
                    <button class="assessment-details assessment-call-archive-detail" 
                      data-call="{{ $item->id }}" data-start-date="{{ $item->start_date }}" data-end-date="{{ $item->end_date? $item->end_date: 'null' }}"
                      data-number="{{ $item->citizen_number }}" data-assessment="{{ $data['assessmentId'] }}"  data-collapse="0">
                      <img src="{{ asset('img/loading.svg') }}" class="assessment-response-loading" alt="">
                      Ətraflı
                    </button>   
                  </td>
                </tr>
                @endforeach 
              @endif

              @if(!$data['status'])
                @foreach ($data['closed'] as $item)
                <tr  data-id='{{ $item->id }}' style="background-color: #ff00001f">
                  <td>  
                    {{ $item->citizen_number }}
                  </td>
                  <td class="assessment-completed-score"> 
                    <span>{{ $item->organ }}</span> 
                  </td>
                  <td>
                    {{ $item->service }}
                  </td>  
                
                  <td> 
                    <button class="assessment-details assessment-call-archive-detail" 
                      data-call="{{ $item->id }}" data-start-date="{{ $item->start_date }}" data-end-date="{{ $item->end_date? $item->end_date: 'null' }}"
                      data-number="{{ $item->citizen_number }}" data-assessment="{{ $data['assessmentId'] }}"  data-collapse="0">
                      <img src="{{ asset('img/loading.svg') }}" class="assessment-response-loading" alt="">
                      Ətraflı
                    </button>   
                  </td>
                </tr>
                @endforeach
              @endif
            </tbody>
          </table>
        </div>
        <button class="completed-assessment"> <img src="{{ asset('img/loading.svg') }}" class="package-response-loading" alt=""> Paketə əlavə et </button>
        @if(!Auth::user() && $data['accept']==0)
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