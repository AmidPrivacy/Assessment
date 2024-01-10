<!doctype html>
<html lang="az">
  <head>  
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1"> 
      <title>Laravel</title> 
      <!-- Fonts -->
      <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">
      <link href="{{ asset('css/assessment.css') }}" rel="stylesheet"> 
      <link
          href="{{ asset('css/all.min.css') }}"
          rel="stylesheet"
          type="text/css"
      />
    
      @if(isset(Auth::user()->name))
          <link
              href="{{ asset('css/bootstrap.min.css') }}"
              rel="stylesheet"
              type="text/css"
          />
      @else
          <link
          href="{{ asset('css/login.css') }}"
          rel="stylesheet"
          type="text/css"
          />
      @endif
      
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
 
    </div>
   
    <div class="container operator-assessment">
      @if(Auth::user() && Auth::user()->role ==0)
      <div class="assessment-left-arrow">
        <a href="/"><i class="fas fa-arrow-left"></i></a>
      </div>
      @endif
   
      <div class="all-assessment-tab"> 
        <button class="{{ $data['finished'] ? 'packed-assessment' : 'common-assessment-tab' }} "> {{ $data['finished'] ? 'Yekunlaşan qiymətləndirmələr' : 'Yekunlaşmayan qiymətləndirmələr' }} </button>
      </div>

      <div class="list-group"  id="accordionExample">  
        <div class="table-scrool">
          <table>
            <thead>
              <tr>
                <th>Tarix</th>
                <th>Yekun bal</th> 
                <th> Operator </th>
                <th></th> 
              </tr>
            </thead>
            <tbody>
              @foreach ($data['assessments'] as $item) 
              <tr class="checked-assessment"  data-id='{{ $item->id }}'>
                <td>  
                  <span class="operator-fullname">{{ explode(" ",$item->beginDate)[0].' -- '.explode(" ",$item->endDate)[0] }}</span> 
                </td>
                <td class="assessment-completed-score"> <span>{{ $item->score_count }}</span> </td>  
                <td class="assessment-completed-score"> <span>{{ $item->full_name }}</span> </td>  
                <td>
                  <a href="{{ (Auth::user() ? '/assest-calls/':'/op-assest-calls/').$item->id }}">
                    <button class="assessment-details" data-id="{{ $item->id }}">Ətraflı</button>  
                  </a> 
                </td> 
              </tr>
              @endforeach   
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <div class="col-md-12 audio-player"> 
      <div class="container"> 
        <div class="waveform"></div> 
        <div id="waveform-time-indicator">
          <span class="time" id="timer">00:00:00 </span> 
        </div>
      </div>
      <button class="audio-play" type="button" data-play="0"><i class="far fa-play-circle"></i> <span>Play / Pause </span></button> 
  </div>


 
      {{ csrf_field() }}
      <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
        @csrf
      </form>
      <script src="{{ asset('js/popper.min.js') }}"></script>
      <script src="{{ asset('js/bootstrap.min.js') }}"></script>
      <script src="{{ asset('js/jquery.easing.min.js') }}"></script>  
      <script src="{{ asset('js/sweetalert2.all.min.js') }}"></script> 
      <script src="{{ asset('js/wavesurfer.js') }}"></script>  
      <script src="{{ asset('js/assessment.js') }}" ></script> 
      <script> 
        function printDiv(divName) {
          var printContents = document.getElementById(divName).innerHTML;
          var originalContents = document.body.innerHTML;

          document.body.innerHTML = printContents;

          window.print();
          location.reload(); 
        }
      </script>
  </body>
</html>