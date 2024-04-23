@extends('front.layout.default')

{{-- Web site Title --}}
@section('title')
    {{ t('API Login') }}
@stop

{{-- Content --}}
@section('header')
    <header style="background-image: url(/images/page-banners/banner-field.jpg);">
        <nav class="header-admin navbar navbar-expand-md box-shadow">
            <a href="/"><img src="/images/page/biospex_logo.svg" alt="BIOSPEX"
                             class="my-0 mr-md-auto top-logo-admin font-weight-normal"/></a>
            @include('common.nav')
        </nav>
    </header>
@endsection

@section('content')
    <h2 class="text-center pt-4 text-uppercase">{{ t('Biospex API Login') }}</h2>
    <hr class="header mx-auto" style="width:300px;">
    <div class="col-12 col-md-10 offset-md-1">
        <div class="card white box-shadow py-5 my-5 p-sm-5">
            <div class="col-6 mx-auto">
                <form action="{{ route('api.post.login') }}" method="post" role="form" class="recaptcha">
                    @csrf
                    <div class="form-group">
                        <label for="email" class="col-form-label required">{{ t('Email') }}:</label>
                        <input type="email" class="form-control {{ ($errors->has('email')) ? 'is-invalid' : '' }}"
                               id="email" name="email"
                               value="{{ old('email') }}" required>
                        <span class="invalid-feedback">{{ $errors->first('email') }}</span>
                    </div>
                    <div class="form-group">
                        <label for="password" class="col-form-label required">{{ t('Password') }}:</label>
                        <input type="password" class="form-control {{ ($errors->has('password')) ? 'is-invalid' : '' }}"
                               id="password" name="password"
                               value="{{ old('password') }}" required>
                        <span class="invalid-feedback">{{ $errors->first('password') }}</span>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="remember" name="remember">
                        <label class="custom-control-label" for="remember">{{ t('Remember Me') }}</label>
                    </div>
                    @include('common.recaptcha')
                    @include('common.submit-button')
                </form>
                <div class="mt-4 text-center">
                    <a href="{{ route('api.password.request') }}">{{ t('Forgot your Password?') }}</a> ||
                    <a href="{{ route('api.get.register') }}">{{ t('Register') }}</a>
                </div>
            </div>
        </div>
    </div>
@stop