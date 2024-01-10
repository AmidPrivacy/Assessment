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
      {{-- <link
          href="{{ asset('css/table.css') }}"
          rel="stylesheet"
          type="text/css"
      /> --}}
      <link
          href="{{ asset('css/modal.css') }}"
          rel="stylesheet"
          type="text/css"
      />

    
      <script src="{{ asset('js/jquery-3.0.0.js') }}"></script>
  </head>
  <body style="background-image: url('/img/operator_background.svg'); background-color: #2090fe33">

    <div class="assessment-header">
      <div class="dropdown">
        <a class="btn btn-secondary dropdown-toggle" 
        href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          {{ Auth::user()->name }}
        </a> 
        <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
          <a class="dropdown-item" href="{{ route('logout') }}"
            onclick="event.preventDefault();
            document.getElementById('logout-form').submit()">
              Çıxış
            </a> 
        </div>
      </div>
    </div>

    <div class="container operator-assessment">
      <div class="assessment-left-arrow">
        <a href="/"><i class="fas fa-arrow-left"></i></a>
      </div>
      <h3> {{ $data['fullName'] }} </h3>
      <h4>Qiymətləndirmə siyahısı</h4>
      <div class="all-assessment-tab">
        <button class="common-assessment-tab"> Ümumi </button>
        <button class="packed-assessment"> Paketləşdirilən </button>
      </div>

      <div class="list-group"  id="accordionExample">  
        <div class="table-scrool">
          <table>
            <thead>
              <tr>
                <th>Tarix</th>
                <th>Yekun bal</th> 
                <th>Paketləşdirilib</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              @foreach ($data['assessment'] as $item) 
              <tr class="checked-assessment"  data-id='{{ $item->id }}'>
                <td>  
                  <span class="operator-fullname">{{ $item->begin_date.' '.$item->begin_date }}</span> 
                </td>
                <td class="assessment-completed-score"> <span>{{ $item->score_count }}</span> </td>
                <td>
                  <input type="checkbox" class="op-assessment-checked" value="{{ $item->id }}" data-operator="{{ $item->operatorId }}"> 
                </td>  
                <td>
                  <a href="/assest-calls/{{ $item->id }}">
                    <button class="assessment-details" data-id="{{ $item->id }}">Ətraflı</button>  
                  </a> 
                </td>
              </tr>
              @endforeach  
              @foreach ($data['package'] as $item) 
              <tr class="assessment-packages"  data-id='{{ $item->id }}'>
                <td>  
                  <span class="operator-fullname">{{ $item->date }}</span> 
                </td>
                <td> {{ $item->score }} </td>
                <td>
                  <i class="fas fa-print" data-toggle="modal" data-target=".print-modal" data-id="{{ $item->id }}"></i>
                </td>  
                <td>
                  <a href="/assessment-detail/{{ $item->id }}">
                    <button class="assessment-details" data-id="{{ $item->id }}">Ətraflı</button>  
                  </a> 
                </td>
              </tr>
              @endforeach  
            </tbody>
          </table>
        </div>
        <button class="completed-assessment"> <img src="{{ asset('img/loading.svg') }}" class="package-response-loading" alt=""> Paketə əlavə et </button>
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
  <div class="modal fade print-modal" tabindex="-1" role="dialog" aria-labelledby="statusModalLabel" aria-modal="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content" id="assessment-report-print">
            <div class="modal-header">
                YEKUN QİYMƏTLƏNDİRMƏ
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body"> 
              <table> 
                <thead>
                  <tr>
                    <th scope="col">Qiymətləndirmə</th> 
                    <th scope="col">Tarix aralığı</th>
                    <th scope="col">Ümumi xidmət</th>
                    <th scope="col">Ümumi zəng</th>
                    <th scope="col">Qiymətləndirilən zəng</th>
                    <th scope="col">Düzgün seçilməyənlər</th>
                    {{-- <th scope="col">Danışıq vaxtı</th> --}}
                    <th scope="col">Qiymətləndirmə vaxtı</th>
                    <th scope="col">Yekun balı</th>
                  </tr>
                </thead>
                <tbody></tbody>
              </table>
              <div class="completed-package-score"></div>
              <div class="package-sign">
                <div class="package-user">
                  <span> </span> <div class="sign-line">_______________________________</div>
                </div>
                <div class="package-supervisor">
                  <span> </span> <div class="sign-line">_______________________________</div>
                </div>
              </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary print-report" onclick="printDiv('assessment-report-print')">Çap et</button>
            </div>
      </div>
    </div>
  </div>

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