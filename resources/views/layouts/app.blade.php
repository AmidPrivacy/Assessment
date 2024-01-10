<!doctype html>
<html lang="az">
<head>  
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1"> 
    <title> Operatorların qiymətləndirilməsi </title> 
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">
    <link href="{{ asset('css/custom.css') }}" rel="stylesheet"> 
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
<body style="background-image: {{ !isset(Auth::user()->name) ? 'url(img/asan_background.svg)' : 'none' }} ">
    <div id="app">
       
        @if(isset(Auth::user()->name))
        <main> 
            <div class="container-fuild">
                <div class="row">
                    <div class="col-md-2"> 
                        <nav>
                            <div class="assessment-logo">
                                <img src="{{ asset('img/asan_logo.svg') }}" alt=""/>

                                @include('layouts.dropdown')
                                {{-- <div class="dropdown">
                                    <a class="btn btn-secondary dropdown-toggle" 
                                    href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                     <span> {{ Auth::user()->name }} </span>
                                     <span class="badge badge-light"> {{ $data['menu']['complaint'] }} </span>
                                     <b>
                                      {{ Auth::user()->role == 0 ? 'Qiymətləndirən' : '' }}
                                      {{ Auth::user()->role == 1 ? 'Kurator' : '' }}
                                      {{ Auth::user()->role == 2 ? 'Rəhbər' : '' }}
                                      </b>
                                    </a> 
                                    <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                                        <a class="dropdown-item" href="{{ route('logout') }}"
                                            onclick="event.preventDefault();
                                            document.getElementById('logout-form').submit()">
                                                    Çıxış
                                        </a> 

                                        <a class="dropdown-item" href="/new-complaints" >
                                            Yeni şikayətlər 
                                            <span class="badge badge-danger"> {{ $data['menu']['complaint'] }} </span>
                                        </a> 

                                        <a class="dropdown-item" href="/finished-assessments" >
                                            Yekunlaşan qiymətləndirmələr
                                            <span class="badge badge-danger"> {{ $data['menu']['finishedAssessments'] }} </span>
                                        </a> 

                                        <a class="dropdown-item" href="/unfinished-assessments" >
                                            Yekunlaşmayan qiymətləndirmələr
                                            <span class="badge badge-danger">{{ $data['menu']['unFinishedAssessments'] }}</span>
                                        </a> 
                                        
                                    </div> 
                                </div> --}}
                            </div>
                            <div class="steps">
                                
                                @yield('steps')
                                
                            </div>
                        </nav>
                    </div>
                    <div class="col-md-10 assessment-container">
                        @yield('content') 
                   
                    </div>
                </div>
            </div> 
        </main>
        @else
        <div class="login-box">
            <h2> SİSTEMƏ GİRİŞ </h2>
            <form method="POST" action="{{ route('login') }}">
              @csrf
              <div class="user-box">
              <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}"  autocomplete="email" autofocus>
              <label>E-poçt</label> 
              @error('email')
                  <span class="invalid-feedback" role="alert">
                      <strong>{{ $message }}</strong>
                  </span>
              @enderror
              </div>
              <div class="user-box">
                  <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">
                  <label>Şifrə</label>
                  @error('password')
                      <span class="invalid-feedback" role="alert">
                          <strong>{{ $message }}</strong>
                      </span>
                  @enderror
              </div>
              <!-- <a href="#"> -->
                  <button type="submit">
                      <span></span>
                      <span></span>
                      <span></span>
                      <span></span>
                      GİRİŞ ET
                  </button> 
              <!-- </a> -->
            </form>
          </div>
        @endif 
        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
            @csrf
        </form>
    </div>
</body>
</html>
