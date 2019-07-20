@extends('front.layout.default')

{{-- Web site Title --}}
@section('title')
    {{ __('pages.login') }}
@stop

{{-- Content --}}
@section('header')
    <header style="background-image: url(/images/page-banners/banner-field.jpg);">
        <nav class="header navbar navbar-expand-md box-shadow">
            <a href="/"><img src="/images/page/biospex_logo.svg" alt="BIOSPEX"
                             class="my-0 mr-md-auto top-logo font-weight-normal"/></a>
            @include('common.nav')
        </nav>
    </header>
@endsection

@section('content')
    <h2 class="text-center pt-4 text-uppercase">{{ __('pages.biospex') }} {{ __('pages.login') }}</h2>
    <hr class="header mx-auto" style="width:300px;">
    <div class="col-12 col-md-10 offset-md-1">
        <div class="card white box-shadow py-5 my-5 p-sm-5">
            <div class="col-6 mx-auto">
                <form action="{{ route('app.post.login') }}" method="post" role="form">
                    @csrf
                    <div class="form-group">
                        <label for="email" class="col-form-label required">{{ __('pages.email') }}:</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror"
                               id="email" name="email"
                               value="{{ old('email') }}" required>
                        @error('email')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="password" class="col-form-label required">{{ __('pages.password') }}:</label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror"
                               id="password" name="password"
                               value="{{ old('password') }}" required>
                        @error('password')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="remember" name="remember">
                        <label class="custom-control-label" for="remember">{{ __('pages.remember_me') }}</label>
                    </div>
                    @include('common.recaptcha')
                    @include('common.submit-button')
                </form>
                <div class="mt-4 text-center">
                    <a href="{{ route('app.password.request') }}">{{ __('pages.forgot_your_pass') }}</a> ||
                    <a href="{{ route('app.get.register') }}">{{ __('pages.register') }}</a>
                </div>
            </div>
        </div>
    </div>
@endsection
