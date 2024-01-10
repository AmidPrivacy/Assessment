<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1"> 
        <title>Laravel</title> 
        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">
        <link href="{{ asset('css/custom.css') }}" rel="stylesheet"> 
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
        <script src="{{ asset('js/jquery-3.0.0.js') }}"></script>
    </head>
    <body> 
      <header>
        <div class="header-top">
          <div class="container">
            <div class="row"> 
              <div class="col-md-2">
                <img src="{{ asset('img/asan-xidmet.png') }}" alt="" class="logo">
              </div>
              <div class="col-md-2 offset-md-9"> 
                <div class="user-setting">
                   <a href="#"> <i class="fas fa-user"></i>  Elcin Quliyev <i class="fas fa-angle-down"></i> </a> 
                  <ul>
                    <a href="#"> <li>  Statistika  </li> </a>
                    <a href="#"> <li>  Çıxış  </li> </a>
                  </ul>
                </div> 
              </div> 
            </div>
          </div>
        </div>
      </header>
      <section class="section section__menu">
        <div id="menu" class="menu container">
          <h1 class="heading">Operatorlar siyahısı</h1>
          <ul class="ul">
            <li class="child">
              <div class="operator-fullname">Aydin Agayev</div>
              <div class="assestment-date"> 16.08.2020 </div>
            </li>
              <button id="fishButton" class="button button--add child1" value="fish">Qiymətləndir</button>
              <button  class="button continue-assestment" value="fish">Davam et</button>
            <li class="child">
                <div class="operator-fullname">
                  Tura Həmzəyeva
                </div> 
                <div class="assestment-date"> 16.08.2020 </div>
              </li><button id="meatButton" class="button button--add child1" value="meat">Qiymətləndir</button>
            <button id="fishButton" class="button continue-assestment" value="fish">Davam et</button>
            <li class="child">
                <div class="operator-fullname">
                  Leyla Ayubzadə
                </div>
                <div class="assestment-date"> 16.08.2020 </div>
              </li><button id="coffeeButton" class="button button--add child1" value="coffee">Qiymətləndir</button>
            <button id="fishButton" class="button continue-assestment" value="fish">Davam et</button>
            <li class="child">
                <div class="operator-fullname">
                  Zefer Memmedov
                </div>
                <div class="assestment-date"> 16.08.2020 </div>
              </li><button id="dessertButton" class="button button--add child1" value="dessert">Qiymətləndir</button>
            <button id="fishButton" class="button continue-assestment" value="fish">Davam et</button>
          </ul>
        </div> 
      </section>
  
      <section class="step-section">
        <div class="container">
          <div class="row">
            <div class="col-md-12 ">
                <form id="msform">
                    <!-- progressbar -->
                    <ul id="progressbar">
                        <li class="active">Qiymətləndirmə tarixi</li>
                        <li>Xidmətlər</li>
                        <li>Qiymətləndirmə</li>
                    </ul>
                    <!-- fieldsets -->
                    {{-- <fieldset>
                        <h2 class="fs-title">Tarix üzrə Qiymətləndirmə</h2>
                        <h3 class="fs-subtitle assestment-user">  </h3> 
                        <div class="input-group input-daterange">
                            <label>Başlama tarixi</label>
                            <input type="text" class="form-control" placeholder="Başlama tarixi seçin" readonly>
                            <label>Bitmə tarixi</label>
                            <input type="text" class="form-control"  placeholder="Bitmə tarixi seçin" readonly>
                        </div>
                        <input type="button" name="next" class="next action-button" value="Növbəti"/>
                    </fieldset>
                    <fieldset class="assestment-services">
                      <h2 class="fs-title">Xidmətlər üzrə Qiymətləndirmə</h2>
                      <h3 class="fs-subtitle assestment-user"></h3>
                        <div class="col-md-6">
                          <ul class="list-group">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                              Cras justo odio
                              <span class="badge badge-primary badge-pill">14</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                              Dapibus ac facilisis in
                              <span class="badge badge-primary badge-pill">2</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                              Morbi leo risus
                              <span class="badge badge-primary badge-pill">1</span>
                            </li>
                          </ul>
                        </div>
                        <div class="col-md-6">
                          <input type="text" name="gplus" placeholder="Say seçin"/>  
                        </div> 
                        
                        <input type="button" name="previous" class="previous action-button-previous" value="Əvvəlki"/>
                        <input type="button" name="next" class="next action-button" value="Növbəti"/>
                    </fieldset> --}}
                    <fieldset class="audio-cutter">
                      <div class="audio-title">
                        <h2 class="fs-title">Səsi Qiymətləndir</h2>
                        <h3 class="fs-subtitle assestment-user"> </h3>
                      </div> 

                      <div class="accordion" id="accordionExample">

                        <div class="card">
                          <div class="card-header" id="headingOne">
                            <h2 class="mb-0">
                              <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                2301  / "ASAN xidmət" mərkəzləri / Dövlət rüsumları
                              </button>
                            </h2>
                          </div>
                      
                          <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#accordionExample">
                            <div class="card-body">
                              
                              <div class="col-md-12">
                                <div class='container'>
                                  <progress id="timer"></progress><br /> 
                                  <i class="far fa-play-circle"></i>
                                  <span id="audio-time">100</span>
                                  {{-- <input type="submit" name="submit" class="submit action-button cutter-btn" value="Kəs"/> --}}
                                </div>
                                <div class="container">
                                  <audio controls>
                                    <source src="horse.ogg" type="audio/ogg">
                                    <source src="horse.mp3" type="audio/mpeg">
                                    Your browser does not support the audio tag.
                                  </audio>
                                </div>
                              </div>
                              <div class="clearfix"></div>
                              <div class="col-md-6">
                                <ul class="list-group">
                                  <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span class="critery-name"> Cras justo odio </span> 
                                    <span class="badge badge-primary badge-pill">14</span>
                                    <i class="fas fa-plus add-critery"></i>
                                  </li>
                                  <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span class="critery-name"> Dapibus ac facilisis in </span> 
                                    <span class="badge badge-primary badge-pill">2</span>
                                    <i class="fas fa-plus add-critery"></i>
                                  </li>
                                  <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span class="critery-name"> Morbi leo risus </span> 
                                    <span class="badge badge-primary badge-pill">1</span>
                                    <i class="fas fa-plus add-critery"></i>
                                  </li>
                                </ul> 
                              </div>
                              <div class="col-md-6">
                                <ul class="list-group selected-items">
                                  <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Cras justo odio
                                    <span class="badge badge-primary badge-pill selected-time">01:20</span>
                                    <span class="badge badge-primary badge-pill">14</span>
                                    <i class="far fa-trash-alt"></i>
                                  </li>
                                  <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Dapibus ac facilisis in
                                    <span class="badge badge-primary badge-pill selected-time">01:45</span>
                                    <span class="badge badge-primary badge-pill">2</span>
                                    <i class="far fa-trash-alt"></i>
                                  </li>
                                  <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Morbi leo risus
                                    <span class="badge badge-primary badge-pill selected-time">02:10</span>
                                    <span class="badge badge-primary badge-pill">1</span>
                                    <i class="far fa-trash-alt"></i>
                                  </li>
                                </ul> 
                              </div>

                            </div>
                          </div>
                        </div>
                       
                      </div>
 
                        <input type="button" name="previous" class="previous action-button-previous" value="Əvvəlki"/>
                        <input type="submit" name="submit" class="submit action-button" value="Yadda saxla"/>
                        <input type="submit" name="submit" class="submit action-button" value="Göndər"/>
                    </fieldset>
                </form> 
            </div>
          </div> 
        </div>
      </section>   
 
      <script src="{{ asset('js/popper.min.js') }}"></script>
      <script src="{{ asset('js/bootstrap.min.js') }}"></script>
      <script src="{{ asset('js/jquery.easing.min.js') }}"></script> 
      <script src="{{ asset('js/bootstrap-datepicker.min.js') }}"></script>
      <script src="{{ asset('js/bootstrap-datepicker.az.min.js') }}"></script>
      <script src="{{ asset('js/custom.js') }}" ></script>
      <script>
        $(function() {
          $('.button--add').click(function() {
              $('.step-section').fadeIn()
          })
        })
      </script>
        
    </body>
</html>
