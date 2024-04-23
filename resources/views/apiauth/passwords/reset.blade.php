@extends('front.layout.default')

{{-- Web site Title --}}
@section('title')
    @parent
    {{ t('Reset Password') }}
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
    <h2 class="text-center pt-4">{{ t('Reset Password') }}</h2>
    <hr class="header mx-auto" style="width:300px;">
    <div class="col-12 col-md-10 offset-md-1">
        <div class="card white box-shadow py-5 my-5 p-sm-5">
            <div class="col-6 mx-auto">
                <form action="{{ route('api.password.request') }}" method="post" role="form" class="form-horizontal recaptcha">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">
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
                    <div class="form-group">
                        <label for="password_confirmation" class="col-form-label required">{{ t('Confirm Password') }}:</label>
                        <input type="password" class="form-control {{ ($errors->has('password_confirmation')) ? 'is-invalid' : '' }}"
                               id="password_confirmation" name="password_confirmation"
                               value="" required>
                        <span class="invalid-feedback">{{ $errors->first('password_confirmation') }}</span>
                    </div>
                    @include('common.recaptcha')
                    @include('common.submit-button')
                </form>
                @include('common.back-login', ['route' => route('api.get.login')])
            </div>
        </div>
    </div>
@endsection
