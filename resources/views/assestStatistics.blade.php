<!doctype html>
<html lang="az">
  <head>  
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1"> 
      <title>Qiymətləndirmə statistikası</title> 
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
          href="{{ asset('css/statistics.css') }}"
          rel="stylesheet"
          type="text/css"
      />
      <link
          href="{{ asset('css/modal.css') }}"
          rel="stylesheet"
          type="text/css"
      />

      <style>
 
        .input-group, .form-control {
          width: 250px;
          float: left;
          margin: 0 5px 0 0; 
        }
  
        table tr {
          height: auto !important;
        }

        form {
          width: 585px;
          position: absolute;
          right: 42px;
          top: 17px;
        }

      </style>
    
      <script src="{{ asset('js/jquery-3.0.0.js') }}"></script>
  </head>
  <body style="background-image: url('/img/operator_background.svg'); background-color: #2090fe33">
    
      <div class="assessment-header">
        @include('layouts.dropdown') 
        <form action="assest-statistics" method="GET">
          @if(Auth::user() && Auth::user()->role !==0)
            <select name="id" class="form-control" id="selected-user-id">
              @foreach ($data['users'] as $user)
                <option value="{{ $user->id }}">{{ $user->name }}</option>
              @endforeach
            </select>
          @endif
          <div class="input-group date" data-provide="datepicker" data-date-format="yyyy-mm-dd" >
            <input type="text" class="form-control" readonly name="date" placeholder="Tarix seçin" value="{{ $data['date'] }}">
            <div class="input-group-addon">
                <span class="glyphicon glyphicon-th"></span>
            </div>
          </div>
          <button type="submit" class="btn btn-light">Axtar</button> 
        </form>
      </div>
      
    {{-- <div class="container operator-assessment"> --}}
      @include('layouts.statistics')
    {{-- </div> --}}

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
      <script src="{{ asset('js/bootstrap-datepicker.min.js') }}"></script>
      <script src="{{ asset('js/bootstrap-datepicker.az.min.js') }}"></script>
      {{-- <script src="{{ asset('js/assessment.js') }}" ></script>  --}}
      <script>
        $("#datepicker").datepicker({
          autoclose: true, 
          todayHighlight: true,
          language: "az",
          Default: true,
        });

        // $(document).on('change', '.form-control', function(){
        //   window.location.assign('/assest-statistics?date='+$(this).val())
        // })

        $(function() {

          $('#user-comment-send').click(function() {

            let comment = $("#user-comment").val();
            let dateSelection = $('.date .form-control').val();
            let userId = $('#selected-user-id').val();
            let _token = $('input[name="_token"]').val();

            if(comment.length > 0) { 
              $.ajax({
                  type: "post",
                  url: `/user-comment`,
                  dataType: "json",
                  data: { 
                    _token, 
                    date: dateSelection, 
                    comment, 
                    id: userId 
                  },
                  success: function(data) {
                    alert(data.message); 
                  }
              });
            }

          })

          $('#refreshTime').click(function() {

            let time = $("#additionalTime").val();
            let dateSelection = $('.date .form-control').val();
            // console.log(dateSelection)
            let userId = $('#selected-user-id').val();
            let _token = $('input[name="_token"]').val();

            if(Number(time) > 0) { 
              $.ajax({
                  type: "post",
                  url: `/add-time`,
                  dataType: "json",
                  data: { 
                    _token, 
                    date: dateSelection, 
                    time, 
                    id: userId 
                  },
                  success: function(data) {
                    alert(data.message); 
                  }
              });
            }
            
          })
        })
      </script>
   
  </body>
</html>