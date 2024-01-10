@extends('layouts.app')

@section('content')
      
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

@endsection
