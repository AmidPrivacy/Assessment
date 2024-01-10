@extends('layouts.app')
@section('steps') 
  @include('layouts.steps')
@endsection
@section('content') 
      <section class="section section__menu"> 
        <div class="step-container-operators">
          <div class="operator-filter">
            <h1> OPERATORLARIN SİYAHISI </h1>
            <input type="text" id="name-filter" placeholder="Operator axtar....">
          </div> 
          <div class="assessment-tab">
              <div class="operators-tab active-tab">
                Hamısı ({{ count($data['operators']) }})
              </div>
              <div class="assessment-operators">
                Qiymətləndirilənlər (30)
              </div>
          </div>
          <div class="clearfix"></div>
          <div class="selected-user-show">
            <div class="selected-user-info">
              <i class="far fa-times-circle"></i>
              <i class="fas fa-user-circle"></i>
              <span> SAMIRƏ ISKƏNDƏROVA </span>  
            </div>
            <div class="selected-user-assessment">
              <button  class="button continue-assestment" data-id="1" data-operator="Aydin Eliyev">Davam et</button>
            </div>
          </div>
          <div class="table-scrool">
            <table>
              <thead>
                <tr>
                  <th>Ad, Soyad</th>
                  <th>Zəng sayı</th>
                  <th>Tarix</th>
                  <th>Mövcud status</th> 
                  @if(Auth::user() && Auth::user()->role != 0)
                  <th> Şikayət </th>
                  @endif
                  <th></th>
                </tr>
              </thead>
              <tbody>
                @foreach ($data['operators'] as $item) 
                <tr>
                  <td> 
                    <i class="fas fa-user-circle"></i>
                    <span class="operator-fullname"  style="color: {{ (isset($item->assessed) ? $item->assessed : false)  ? "#ff6a3b" : "#212529" }}">{{ $item->full_name }}</span> 
                  </td>
                  <td>{{ $item->callCount }} {{ $item->callAssest ? ('('.$item->callAssest.')') :null  }}</td>
                  <td> 
                    <span class="assestment-date">{{ substr($item->endDate, 0, 10) }}</span>
                  </td> 
                  <td> 
                    @if(Auth::user() && $item->is_active === 0 && Auth::user()->role == 0 && $item->assestUser == Auth::user()->id)
                      <button  class="button continue-assestment next-process" data-id="{{ $item->assessmentId }}" 
                        data-operator="{{ strtoupper($item->full_name) }}">
                        Davam et
                      </button>
                    @endif
                    @if($item->beginDate !== 'Qiymətləndirmə edilməyib' && $item->is_active > 0)
                      <a href="/assessment/{{ $item->id }}"> 
                        Ətraflı   
                      </a>
                    @endif
                  </td>
                  @if(Auth::user() && Auth::user()->role != 0)
                  <td>  
                    @if($item->complaint > 0)
                      <i class="fas fa-check" style="font-size: 16px; margin-left: 30px"></i>
                    @endif
                  </td>
                  @endif 
                  <td>
                    @if(Auth::user() && Auth::user()->role == 0 && ($item->is_active === null || $item->is_active > 0))
                      <button class="button button--add child1" data-id="{{ isset($item->phone_number)?$item->phone_number:"" }}" data-user="{{ $item->id }}">Qiymətləndir</button>  
                    @endif
                  </td>
                </tr>
                @endforeach  
              </tbody>
            </table>
          </div>
        </div> 
        <div class="step-container-date">
          <div class="selected-operator-info">
            Qiymətləndirilən operator: <span> Əfsanə Ələkbərova </span>
          </div>
          <h2 class="fs-title">Operatorların qiymətləndirilmə müddəti</h2> 
          <div class="input-group input-daterange">
              <div class="date-selected">
                <label>Başlama tarixi</label>
                <input type="text" class="form-control begin-date" placeholder="Başlama tarixi seçin" data-date-format="yyyy-mm-dd" readonly>
              </div>
              <div class="date-selected">
                <label>Bitmə tarixi</label>
                <input type="text" class="form-control end-date"  placeholder="Bitmə tarixi seçin" data-date-format="yyyy-mm-dd" readonly>
              </div>
          </div>
          <button type="button" class="next action-button"><img src="{{ asset('img/loading.svg') }}" class="response-loading" alt=""> Növbəti</button>
        </div>
        <div class="step-container-services"> 
          <div class="selected-operator-info">
            Qiymətləndirilən operator: <span> Əfsanə Ələkbərova </span>
          </div>

          <h3>Xidmətlər üzrə qiymətləndirmə</h3>

          <div class="statistic-counts">
            <div class="selected-services">
              <b>Ümumi xidmət sayı:</b> 
              <span>  </span> 
            </div>
            <div class="selected-calls">
              <b>Ümumi zəng sayı:</b> 
              <span>  </span> 
            </div>
            <div class="selected-not-days">
              <b>Gün sayı:</b> 
              <span>  </span> 
            </div>
          </div> 
          <div class="clearfix"></div>
          <input type="text" name="gplus" id="checked-count" value="50" placeholder="Say daxil edin"/>  
          <input type="hidden" class="selected-services-count">
          <input type="hidden" class="selected-call-count">
          <button type="button" class="next call-assessment-next">
            <img src="{{ asset('img/loading.svg') }}" class="response-loading" alt=""> Növbəti
          </button>
          <div class="table-scrool">
            <table>
              <thead>
                <tr>
                  <th>№</th>
                  <th>Xidmət</th>
                  <th>Zəng sayı</th>  
                </tr>
              </thead>
              <tbody> 
              </tbody>
            </table>
          </div>
        </div>
        <div class="step-container-calls">
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
              <span class="time">00:00:00 </span> / 
              <span class="completed-time">00:00:00 </span> 
            </div> 
            <input type="hidden" id="play-time" value="0"> 
            <input type="hidden" id="special-calculate-time" value="0">
            <button class="audio-play" type="button" data-play="0"><i class="fas fa-play"></i> <span>Play / Pause </span></button> 
            <div class="btn-group speed-adjustment" role="group" aria-label="First group" style="display: block">
              <div class="btn btn-primary" data-speed="0.5">0.5x</div>
              <div class="btn btn-primary active" data-speed="1">1x</div>
              <div class="btn btn-primary" data-speed="1.5">1.5x</div> 
              <div class="btn btn-primary" data-speed="2">2x</div>
              <div class="btn btn-primary" data-speed="2.5">2.5x</div>
              <div class="btn btn-primary" data-speed="3">3x</div>
              <div class="btn btn-primary" data-speed="3.5">3.5x</div>
              <div class="btn btn-primary" data-speed="4">4x</div>
              <div class="btn btn-primary" data-speed="4.5">4.5x</div>
              <div class="btn btn-primary" data-speed="5">5x</div>
              <div class="btn btn-primary" data-speed="5.5">5.5x</div>
              <div class="btn btn-primary" data-speed="6">6x</div>
            </div>
          </div>
          <div id="collapseOne" class="collapse body-detail" aria-labelledby="headingOne" data-parent="#accordionExample">
            <div class="card-body">  
              <div class="clearfix"></div>
              <div class="col-md-6 critery-list">
                <div class="checked-critery"> 
                  <select class="callTypes" multiple>
                    @foreach ($data['types'] as $item)
                      <option value="{{ $item->id }}"> {{ $item->name }} </option>
                    @endforeach
                  </select>
                </div> 
                <ul class="list-group">
                  @foreach ($data['criterias'] as $dataCount => $item)
                    <li class="list-group-item d-flex justify-content-between align-items-center" data-score="{{ $item->score }}"  data-id="{{ $item->id }}">
                      <span class="services-counter"> {{ $dataCount+1 }}. </span>
                      <span class="critery-name"> {{ $item->name }} </span>
                      <span class="badge badge-success" data-id="{{ $item->id }}" style="opacity: 0">0</span>
                      <span class="badge badge-primary badge-pill">{{ $item->score }}</span>
                      <i class="fas fa-ellipsis-v"></i>
                      <i class="fas fa-plus add-critery" data-id="{{ $item->id }}"   data-maxScore="{{ $item->maxScore }}"></i>
                    </li>
                  @endforeach  
                </ul> 
              </div>
              <div class="col-md-6 new-critery-list">
                <div class="common-score"> Qiymətləndirmə balı:  <span> {{ count($data['criterias'])*3 }}  </span> </div>
                <div class="clear-common-score">Təmizlə <i class="far fa-trash-alt"></i></div>
                <ul class="list-group selected-items"> </ul> 
              </div> 
            </div>
            <button id="close-call" type="button"> <img src="{{ asset('img/loading.svg') }}" class="response-loading assessment-call-loading" alt=""> Bağla </button>
            <button id="call-refresh" type="button" data-assessment="0" data-status="1"> <img src="{{ asset('img/loading.svg') }}" class="response-loading assessment-call-loading" alt=""> Zəngi yenilə </button>
            <button id="call-not-assessment" type="button" data-assessment="0" data-status="0"> <img src="{{ asset('img/loading.svg') }}" class="response-loading assessment-call-loading" alt=""> Qiymətləndirmə </button>
            <button class="call-assessment" type="button" data-status="1" data-assessment="0"> <img src="{{ asset('img/loading.svg') }}" class="response-loading assessment-call-loading" alt=""> Qiymətləndir </button>
            <textarea class="call-comment" cols="30" rows="6" placeholder="Zəng üçün rəy"></textarea>
            <i class="fas fa-paragraph add-text"></i>
          </div> 
          <div class="clearfix"></div>
          <div class="all-listening-times">
            <div id="audio-all-times">
              Zəng vaxtı
              <div class="supervisor-counter audio-all-times" data-second="0"><label class="minutes">00</label>:<label class="seconds">00</label></div>
            </div>
            
            <div id="supervisor-counter">
              Qiymətləndirmə vaxtı
              <div class="supervisor-counter" data-second="0"><label class="minutes">00</label>:<label class="seconds">00</label></div>
            </div>
            
            <div id="play-time">
              Dinləmə vaxtı
              <div class="supervisor-counter play-time" data-second="0"><label class="minutes">00</label>:<label class="seconds">00</label></div>
            </div>
          
          </div>
          <div class="assessment-statistics">
            <button type="button" class="btn btn-primary">
              Qiymətləndirilib <span class="badge badge-light call-status">0</span>
            </button>
            <button type="button" class="btn btn-primary">
              Qiymətləndirmə balı <span class="badge badge-light calls-completed-score">33/100%</span>
            </button>
          </div>
          <div class="table-scrool">
            <table>
              <thead>
                <tr>
                  <th>№</th>
                  <th>Əlaqə nömrəsi</th>
                  <th>Orqan</th>
                  <th class="services-call-list">Xidmət</th> 
                  <th>Qiymətləndirmə müddəti</th>
                  <th > Status 
                  </th>
                  <th > Yekun bal 
                  </th>
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
      </section>

      <div class="container"> 
        <dialog id="confirm-modal" class="modal-show">
          <div class="modal-content">
              <i class="fas fa-bookmark"></i>
              <h2 class="modal-title">Bildiriş</h2> 
              <div class="modal-description"> </div>
              <div class="modal-options">  
                <button class="btn btn-round btn-fill btn-fill-right option cancel" 
                  data-text="Bağla" data-request="true" onclick="document.querySelector('#confirm-modal').close()">  
                </button>
                <button class="btn btn-round btn-fill btn-fill-right option cancel call-refresh" data-text="Yenilə" data-status="0"></button>
              </div>
          </div>
        </dialog>
      </div> 
      

      <script src="{{ asset('js/popper.min.js') }}"></script>
      <script src="{{ asset('js/bootstrap.min.js') }}"></script>
      <script src="{{ asset('js/jquery.easing.min.js') }}"></script> 
      <script src="{{ asset('js/bootstrap-datepicker.min.js') }}"></script>
      <script src="{{ asset('js/bootstrap-datepicker.az.min.js') }}"></script> 
      <script src="{{ asset('js/wavesurfer.js') }}"></script> 
      <script src="{{ asset('js/assessment.js') }}"></script> 
      <script>
  
 
        $(function() {

          // $('.button--add').click(function() {
          //   console.log('llllllllll')
          //     $('.step-section').fadeIn()
          // })
 
        })

      </script>
      {{-- <script src="{{ asset('js/custom.js') }}" ></script> --}}

@endsection