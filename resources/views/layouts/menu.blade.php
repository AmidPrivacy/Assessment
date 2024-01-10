<ul> 
  <li class="operator-step">
      {{-- <a href="/new-complaints"> --}}
        <div class="step-icon"> 
            <div class="active-step" data-complaint="new-complaints-tab"> {{ $data['complaintSum'] }} </div>
        </div>
        <div class="step-name">
            Bütün şikayətlər
        </div>
        {{-- </a> --}}
  </li> 
  <li class="date-step">
    {{-- <a href="/finished-complaints"> --}}
        <div class="step-icon">
            <div class="" data-complaint="finished-complaints-tab"> {{ $data['finishedComplaintSum'] }} </div>
        </div>
        <div class="step-name">
            Yekunlaşan
        </div>
    {{-- </a> --}}
  </li> 
  <li class="services-step">
    {{-- <a href="/unfinished-complaints"> --}}
      <div class="step-icon">
        <div class="" data-complaint="unfinished-complaints-tab"> {{ $data['unFinishedComplaintSum'] }} </div>
      </div>
      <div class="step-name">
        Yekunlaşmayan
      </div>
    {{-- </a> --}}
  </li> 
  {{-- <li class="assessment-step">
    <a href="#">
        <div class="step-icon">
            <div class=""> 0 </div>
        </div>
        <div class="step-name">
            Statistika
        </div>
    </a>
  </li>  --}}
</ul>