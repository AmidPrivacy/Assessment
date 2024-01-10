@extends('layouts.app')
{{-- @section('steps') 
  @include('layouts.menu')
@endsection --}}
@section('content') 
      <section class="section section__menu"> 
        <div class="step-container-operators">
          <div class="operator-filter">
            <h1> KRİTERİYLAR SİYAHISI </h1>
          </div>  
          <div class="clearfix"></div> 
          <div class="table-scrool">
            <table>
              <thead>
                <tr>
                  <th> № </th>
                  <th> Kriteriya </th>
                  <th> Qiymətləndirmə balı </th>
                  <th> Düzəliş et </th> 
                </tr>
              </thead>
              <tbody class="complaint-tab-content">
                @foreach ($data['critery'] as $index  => $item) 
                <tr class="show-tab" data-complaint="new-complaints-tab">
                  <td> 
                     {{ $index+1 }}
                  </td>
                  <td> 
                    {{ $item->name }}
                  </td>
                  <td> 
                    <span class="critery-score">
                      {{ $item->max_score }}
                    </span> 
                  </td> 
                  <td>  
                    <i class="far fa-edit edit-critery" data-id="{{ $item->id }}" data-toggle="modal" data-target=".criteryModal" 
                      style="margin-left: 30px;
                        font-size: 22px;
                        color: #2090fe;">
                    </i> 
                   </td>  
                </tr>
                @endforeach   
              </tbody>
            </table>
          </div>
        </div> 
        
      </section>

      
      <div class="modal fade criteryModal" id="exampleModal" role="dialog" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="exampleModalLabel">Kriteriya balı daxil edin:</h5>
              <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close">X</button>
            </div>
            <div class="modal-body">
              @csrf
              <input id="critery_val" type="text" placeholder="Kriteriya balı daxil edin" 
                  style="width: 100%;
                    height: 40px;
                    padding: 0 8px;
                    border: solid 1px #ccc;
                    border-radius: 4px;"
              >
              <input type="hidden" id="critery_id">
            </div>
            <div class="modal-footer"> 
              <button type="button" class="btn btn-primary" id="send_critery">Göndər</button>
            </div>
          </div>
        </div>
      </div>

      <script src="{{ asset('js/popper.min.js') }}"></script>
      <script src="{{ asset('js/bootstrap.min.js') }}"></script> 

      <script>
        $(function(){
          $(".edit-critery").click(function() {
              let id = $(this).attr("data-id");
              let number = $(this).parent().parent().find('.critery-score').text();
              $('#critery_val').val(Number(number));
              $("#critery_id").val(id);
          });

          $("#send_critery").click(function() {
              let id = $("#critery_id").val();
              let critery = $("#critery_val").val();
              let _token = $('input[name="_token"]').val();
              console.log(id);
              $.ajax({
                  type: "POST",
                  url: "/update-critery",
                  dataType: "json",
                  data: {
                      id,
                      critery,
                      _token
                  },
                  success: function(res) {
                      if (res.status === 200) {
                          // alert(res.message);
                          setTimeout(function(){
                            window.location.reload()
                          },2000) 
                      }
                  }
              });
          });
        })
      </script>
 
@endsection