<!doctype html>
<html lang="az">
  <head>  
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1"> 
      <title>Qiymətləndirmə hesabatı </title>  
 
      <link
          href="css/all.min.css"
          rel="stylesheet"
          type="text/css"
      /> 
 
      <link href="css/bootstrap.min.css" rel="stylesheet" type="text/css" />
   

      <style>
        .table .thead-dark th { 
            font-size: 13px;
            background-color: #0a74dd;
            border-color: #0a74dd;
        } 
      </style> 
      <script src="{{ asset('js/jquery-3.0.0.js') }}"></script>
  </head>
  <body >
 
    <div class="container-fuild"> 
      @if(Auth::user())
        <a href="/">
          <button type="button" class="btn btn-primary" style="margin: 15px 3px"> << Əsas səhifə</button>
        </a>
      @endif
      <table class="table">
        <thead class="thead-dark">
          <tr>
            <th scope="col">№</th>
            <th scope="col"> Ad Soyad </th>
            <th scope="col"> 
              @if(Auth::user())
                Aktivlik 
              @else 
                Keçid linki 
              @endif
            </th> 
          </tr>
        </thead>
        <tbody>
          @foreach($data as $index => $item)
          <tr>
            <th scope="row">{{ $index+1 }}</th>
            <td>{{ $item->full_name }}</td> 
            @if(Auth::user())
            <td>
              @if($item->asan_id)
                <a href="/user-status/0/{{ $item->id }}" onclick="if (! confirm('Bu operatoru sistemdən silmək istəyirsinizmi?')) { return false; }">
                  <button type="button" class="btn btn-danger">Sil</button>
                </a>
              @else 
                <a href="/user-status/1/{{ $item->id }}" onclick="if (! confirm('Bu operatoru sistemə əlavə etmək istəyirsinizmi?')) { return false; }">
                  <button type="button" class="btn btn-success">Əlavə et</button>
                </a>
              @endif 
            </td> 
            @else 
            <td>
              <a href="/op-assessment/{{ $item->phone_number }}" target="_blank">
                Ətraflı
              </a>
            </td> 
            @endif
            
          </tr>
          @endforeach
        </tbody>
      </table>
 

    </div>

       
      <script src="{{ asset('js/bootstrap.min.js') }}"></script>
      <script src="{{ asset('js/bootstrap-datepicker.min.js') }}"></script>
      <script src="{{ asset('js/bootstrap-datepicker.az.min.js') }}"></script> 

      <script>
        $(function () {
            $(".input-daterange input").each(function () {
                $(this).datepicker({
                    autoclose: true,
                    todayHighlight: true,
                    language: "az",
                    Default: true,
                });
            });
        });
      </script>

  </body>
</html>