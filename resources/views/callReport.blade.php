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
  
      <link
          href="{{ asset('css/bootstrap-datepicker3.standalone.min.css') }}"
          rel="stylesheet" type="text/css" />

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
      <div class="container-date" style="margin-top: 25px; margin-bottom: 25px">  
        <form action="/call-report" method="get">
          <div class="input-group input-daterange" style="float: left; display: unset; width: 25%;">
            
              <div class="date-selected" style="width: 49%; float: left">
                <label>Başlama tarixi</label>
                <input type="text" name="startDate" class="form-control begin-date" placeholder="Başlama tarixi seçin" data-date-format="yyyy-mm-dd" readonly>
              </div>
              <div class="date-selected" style="width: 49%; float: left; margin-left: 2%">
                <label>Bitmə tarixi</label>
                <input type="text" name="endDate" class="form-control end-date"  placeholder="Bitmə tarixi seçin" data-date-format="yyyy-mm-dd" readonly>
              </div>
            
          </div>
          <button type="submit" class="search-button" style="width: 150px; border: none; background-color: #0a74dd; color: #fff; font-weight: 600; margin-top: 32px; height: 37.5px"> Axtar </button>
          
          <h2 class="fs-title" style="width: 376px; font-size: 20px; float: right; line-height: 92px">Zəng statistikası(seçilən tarix üzrə)</h2> 
          @if(strlen($startDate)>0)
          <div style="width: 300px; float: right; margin-right: 50px; margin-top: 37px">
            <b>Seçilən tarix:</b> {{ $startDate." - ".$endDate }}
          </div>
          @endif
        </form>
      </div>
      <table class="table">
        <thead class="thead-dark">
          <tr>
            <th scope="col">№</th>
            <th scope="col"> Ad Soyad </th>
            <th scope="col"> Ümumi seçilən zəng sayı </th> 
            <th scope="col"> Qiymətləndirmə </th> 
            <th scope="col"> Yalnız baxılan </th>  
          </tr>
        </thead>
        <tbody>
          @foreach($data as $index => $item)
          <tr>
            <th scope="row">{{ $index+1 }}</th>
            <td>{{ $item["operator"] }}</td> 
            <td>{{ $item["commonCalls"] }}</td> 
            <td>{{ $item["assessedCount"] }}</td> 
            <td>{{ $item["notAssessedCount"] }}</td> 
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