@extends('front.layout.default')

{{-- Web site Title --}}
@section('title')
    {{ __('Login') }}
@stop

{{-- Content --}}
@section('header')
    <header style="background-image: url(/storage/images/page-banners/banner-field.jpg);">
        <nav class="header navbar navbar-expand-md box-shadow">
            <a href="/"><img src="/storage/images/biospex_logo.svg" alt="BIOSPEX"
                             class="my-0 mr-md-auto top-logo font-weight-normal"/></a>
            @include('common.nav')
        </nav>
    </header>
@endsection

@section('content')
    <h2 class="text-center pt-4">{{ __('Login') }}</h2>
    <hr class="header mx-auto" style="width:300px;">
    <div class="col-12 col-md-10 offset-md-1">
        <div class="card white box-shadow py-5 my-5 p-sm-5">
            <div class="col-6 mx-auto">
                <form action="{{ route('app.password.request') }}" method="post" role="form" class="form-horizontal">
                    {!! csrf_field() !!}
                    <div class="form-group {{ ($errors->has('email')) ? 'has-error' : '' }}">
                        <label for="email" class="col-form-label required">{{ __('Email') }}:</label>
                        <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}"
                               required>
                        {{ $errors->first('email') }}
                    </div>
                    <div class="form-group {{ ($errors->has('password')) ? 'has-error' : '' }}">
                        <label for="password" class="col-form-label required">{{ __('Password') }}:</label>
                        <input type="password" name="password" id="password" class="form-control" required>
                        {{ ($errors->has('password') ?  $errors->first('password') : '') }}
                    </div>
                    <div class="form-group {{ ($errors->has('password_confirmation')) ? 'has-error' : '' }}">
                        <label for="password_confirmation" class="col-form-label required">{{ __('Password Confirmation') }}:</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required>
                        {{ ($errors->has('password_confirmation') ?  $errors->first('password_confirmation') : '') }}
                    </div>
                    @include('common.recaptcha')
                    <div class="form-group text-center">
                        <button type="submit" class="btn btn-primary pl-4 pr-4">{{ __('SUBMIT') }}</button>
                    </div>
                </form>
                <div class="mt-4 text-center">
                    {!! link_to_route('app.get.login', __('Back to Login')) !!}
                </div>
            </div>
        </div>
    </div>
@endsection
