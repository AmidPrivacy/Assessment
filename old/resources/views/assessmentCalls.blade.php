<!doctype html>
<html lang="az">
  <head>  
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1"> 
      <title>Qiymətləndirilən zənglər</title> 
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
  <body style="background-color: #f6f9ff">
    <div class="col-md-10 assessment-container"> 
      <section class="section section__menu"> 
        
        @include('layouts.dropdown')

        <div class="step-container-calls" style="display: block">
          <div class="selected-operator-info">
            Qiymətləndirilən operator: <span> Əfsanə Ələkbərova </span>
          </div>

          <div class="selected-call-assessment">
            <i class="fas fa-phone-volume"></i> Zəngləri qiymətləndir (<span>50</span>)
          </div>

          <img src="{{ asset('img/loading-audio.gif') }}" class="audio-loading" alt="">
          <div class="col-md-12 audio-player">  
            <div id="waveform" ></div> 
            <div id="waveform-time-indicator">
              <span class="time">00:00:00 </span> 
            </div> 
            <button class="audio-play" type="button" data-play="0"><i class="fas fa-play"></i> <span>Play / Pause </span></button> 
          </div>
          <div id="collapseOne" class="collapse body-detail" aria-labelledby="headingOne" data-parent="#accordionExample">
            <div class="card-body">  
              <div class="clearfix"></div>
              <div class="col-md-6 critery-list">
                <div class="checked-critery">
                  Düzgün seçim edilməyib
                  <input type="checkbox" value="1" />
                </div>
                <ul class="list-group">
                  @foreach ($data['criterias'] as $dataCount => $item)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                      <span class="services-counter"> {{ $dataCount+1 }}. </span>
                      <span class="critery-name"> {{ $item->name }} </span> 
                      <span class="badge badge-primary badge-pill">{{ $item->score }}</span>
                      <i class="fas fa-plus add-critery" data-id="{{ $item->id }}"></i>
                    </li>
                  @endforeach  
                </ul> 
              </div>
              <div class="col-md-6 new-critery-list">
                <div class="common-score"> Qiymətləndirmə balı:  <span> {{ count($data['criterias'])*3 }} </span> </div>
                <div class="clear-common-score">Təmizlə <i class="far fa-trash-alt"></i></div>
                <ul class="list-group selected-items"> </ul> 
              </div> 
            </div>
            <button class="call-assessment" type="button"> <img src="{{ asset('img/loading.svg') }}" class="response-loading assessment-call-loading" alt=""> Qiymətləndir </button>
            <textarea class="call-comment" cols="30" rows="6" placeholder="Zəng üçün rəy"></textarea>
          </div> 
          <div class="clearfix"></div>
          <div class="table-scrool">
            <table>
              <thead>
                <tr>
                  <th>Əlaqə nömrəsi</th>
                  <th>Orqan</th>
                  <th class="services-call-list">Xidmət</th> 
                  <th>Qiymətləndirmə müddəti</th>
                  <th>Status</th>
                  <th>Yekun bal</th>
                </tr>
              </thead>
              <tbody> 
              </tbody>
            </table> 
          </div>
          <button class="assesment-completed">
            <img src="{{ asset('img/loading.svg') }}" class="response-loading" alt=""> Göndər 
          </button>
        </div>
        <input type="hidden" class="operator-id">
        {{ csrf_field() }}
        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
            @csrf
        </form>
      </section>
{{--  
      <div class="container"> 
        <dialog id="confirm-modal" class="modal-show">
          <div class="modal-content">
              <i class="fas fa-bookmark"></i>
              <h2 class="modal-title">Bildiriş</h2> 
              <div class="modal-description"> </div>
              <div class="modal-options">  
                <button class="btn btn-round btn-fill btn-fill-right option cancel " data-text="Bağla" data-request="true" onclick="document.querySelector('#confirm-modal').close()">  </button>
              </div>
          </div>
        </dialog>
      </div>  --}}
      

      {{-- <script src="{{ asset('js/popper.min.js') }}"></script>
      <script src="{{ asset('js/bootstrap.min.js') }}"></script>
      <script src="{{ asset('js/jquery.easing.min.js') }}"></script> 
      <script src="{{ asset('js/bootstrap-datepicker.min.js') }}"></script>
      <script src="{{ asset('js/bootstrap-datepicker.az.min.js') }}"></script> 
      <script src="{{ asset('js/wavesurfer.js') }}"></script> 
      <script src="{{ asset('js/assessment.js') }}"></script>  --}}
  
    </div>
  </body>
</html>