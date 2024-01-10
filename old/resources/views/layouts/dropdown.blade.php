@if(Auth::user())
  <div class="dropdown">
    
    <a class="btn btn-secondary dropdown-toggle header-dropdown" 
    href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
     <span> {{ Auth::user()->name }} </span>
     <span class="badge badge-light"> {{ $data['menu']['complaint'] }} </span>
     <br />
     <b>
      {{ Auth::user()->role == 0 ? 'Qiymətləndirən' : '' }}
      {{ Auth::user()->role == 1 ? 'Kurator' : '' }}
      {{ Auth::user()->role == 2 ? 'Rəhbər' : '' }}
      </b>
    </a> 
    <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
         
        <a class="dropdown-item" href="/">
            Əsas səhifə  
        </a> 

        {{-- <a class="dropdown-item" href="/assest-statistics">
            Statistika  
        </a>  --}}

        @if(Auth::user()->role === 1)
        <a class="dropdown-item" href="/call-report" target="_blank">
            Zəng statistikası  
        </a> 
        <a class="dropdown-item" href="/complaint-report" target="_blank">
            Şikayət statistikası  
        </a> 
        @endif

        @if(Auth::user()->role === 2)
            <a class="dropdown-item" href="/critery" >
                Kriteriyalar  
            </a>  
            <a class="dropdown-item" href="/users" >
                Operatorlar  
            </a>  
            <a class="dropdown-item" href="/complete-month"  onclick="if (! confirm('Bu gündən öncəki son 40 günün qiymətləndirmələrini təsdiq edirsinizmi?')) { return false; }">
                Ayı Yekunlaşdır 
            </a>
        @endif
        
        <a class="dropdown-item" href="/new-complaints" >
            Yeni şikayətlər 
            <span class="badge badge-danger"> {{ $data['menu']['complaint'] }} </span>
        </a>  

        <a class="dropdown-item" href="/packages" >
            Paketlər 
        </a> 

        <a class="dropdown-item" href="/finished-assessments" >
            Yekunlaşan qiymətləndirmələr
            <span class="badge badge-danger"> {{ $data['menu']['finishedAssessments'] }} </span>
        </a> 

        <a class="dropdown-item" href="/unfinished-assessments" >
            Yekunlaşmayan qiymətləndirmələr
            <span class="badge badge-danger">{{ $data['menu']['unFinishedAssessments'] }}</span>
        </a> 
 
        
            {{-- @if(Auth::user()->role === 0)
            <a class="dropdown-item" href="/call-transfer" target="_blank">
                Zəngləri köçür
            </a> 
        @endif --}}

        <a class="dropdown-item" href="{{ route('logout') }}"
            onclick="event.preventDefault();
            document.getElementById('logout-form').submit()">
                    Çıxış
        </a> 
    </div> 
  </div>
@endif