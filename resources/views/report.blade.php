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
 
          <link
              href="css/bootstrap.min.css"
              rel="stylesheet"
              type="text/css"
          />
  
      <style>
        .table .thead-dark th { 
            font-size: 13px;
            background-color: #0a74dd;
            border-color: #0a74dd;
        } 
      </style>
  </head>
  <body >
 
    <div class="container-fuild">

      <table class="table">
        <thead class="thead-dark">
          <tr>
            <th scope="col">№</th>
            <th scope="col">Ad Soyad</th>
            <th scope="col">Salamlama</th> 
            <th scope="col">Özünütəqdimetmə</th> 
            <th scope="col">Müraciətin məzmunun dəqiqləşdirilməsi</th> 
            <th scope="col">Birbaşa cavablandırma </th> 
            <th scope="col">Cavablandırmanın düzgünlüyü və mövcud qanunvericiliyə uyğunluğu</th> 
            <th scope="col">Cavablandırmanın tamlığı və aydınlığı</th> 
            <th scope="col">Cavablandırmanın yekunlaşdırılması</th> 
            <th scope="col">Operativlik, müraciətin təkrarlanması</th> 
            <th scope="col">Müraciətin aydınlaşdırılması üçün düzgün sual vermə</th> 
            <th scope="col">Ədəbi dilin (orfoepiya) normalarına riayət etmə</th> 
            <th scope="col">Nəzakətlilik </th> 
          </tr>
        </thead>
        <tbody>
          @foreach($data as $index => $operator)
          <tr>
            <th scope="row">{{ $index+1 }}</th>
            <td>{{ $operator->full_name }}</td>
            @foreach($operator->criterias as $critery)
            <td> {{ isset($critery->count) && $critery->count !==0 ? $critery->count : "0" }} </td>
            @endforeach
          </tr>
          @endforeach
        </tbody>
      </table>
 

    </div>


      <script src="js/popper.min.js"></script>
      <script src="js/bootstrap.min.js"></script>

  </body>
</html>