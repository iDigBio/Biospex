@extends('front.layout.default')

{{-- Web site Title --}}
@section('title')
    {{ t('API Register') }}
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
    <h2 class="text-center pt-4 text-uppercase">{{ t('Register API Account') }}</h2>
    <hr class="header mx-auto" style="width:300px;">
    <div class="col-12 col-md-10 offset-md-1">
        <div class="card white box-shadow py-5 my-5 p-sm-5">
            <div class="col-6 mx-auto">
                <form action="{{ route('api.post.register') }}" method="post" role="form" class="form-horizontal recaptcha">
                    @csrf
                    <input type="hidden" name="apiuser" value="true">
                    <div class="form-group">
                        <label for="first_name" class="col-form-label required">{{ t('First Name') }}:</label>
                        <input type="text" class="form-control {{ ($errors->has('first_name')) ? 'is-invalid' : '' }}"
                               id="first_name" name="first_name"
                               value="{{ old('first_name') }}" required>
                        <span class="invalid-feedback">{{ $errors->first('first_name') }}</span>
                    </div>
                    <div class="form-group">
                        <label for="last_name" class="col-form-label required">{{ t('Last Name') }}:</label>
                        <input type="text" class="form-control {{ ($errors->has('last_name')) ? 'is-invalid' : '' }}"
                               id="last_name" name="last_name"
                               value="{{ old('last_name') }}" required>
                        <span class="invalid-feedback">{{ $errors->first('last_name') }}</span>
                    </div>
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
                               value="{{ old('password_confirmation') }}" required>
                        <span class="invalid-feedback">{{ $errors->first('password_confirmation') }}</span>
                    </div>
                    @include('common.recaptcha')
                    @include('common.submit-button')
                </form>
                <div class="mt-4 text-center">
                    <a href="{{ route('api.get.login') }}">{{ t('Already have an account? Login') }}</a>
                </div>
                </div>
            </div>
        </div>
    </div>
@stop