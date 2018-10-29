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
                        <label for="email">{{ __('Email') }} <span class="color-action">*</span></label>
                        <input type="text" name="email" id="email" class="form-control" value="{{ old('email') }}"
                               required>
                        {{ $errors->first('email') }}
                    </div>
                    <div class="form-group {{ ($errors->has('password')) ? 'has-error' : '' }}">
                        <label for="password">{{ __('Password') }} <span class="color-action">*</span></label>
                        <input type="password" name="password" id="password" class="form-control" required>
                        {{ ($errors->has('password') ?  $errors->first('password') : '') }}
                    </div>
                    <div class="form-group {{ ($errors->has('password_confirmation')) ? 'has-error' : '' }}">
                        <label for="password_confirmation">{{ __('Password Confirmation') }} <span class="color-action">*</span></label>
                        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required>
                        {{ ($errors->has('password_confirmation') ?  $errors->first('password_confirmation') : '') }}
                    </div>
                    <div class="checkbox">
                        <label><input type="checkbox" value=""> {{ __('Remember Me') }}</label>
                    </div>
                    {!! Honeypot::generate('resetemail', 'testtime') !!}
                    <div class="text-center">
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

@section('footer')
    @include('common.footer')
    @include('common.contributors')
@endsection


@section('content')
    <div class="row top30">
        <div class="col-md-6 col-md-offset-3">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h3 class="panel-title">{{ trans('pages.password_reset') }}</h3>
                </div>
                <div class="panel-body">
                    {!! Form::open([
                    'route' => 'app.password.request',
                    'method' => 'post',
                    'class' => 'form-horizontal',
                    'role' => 'form'
                    ]) !!}
                    {!! Form::hidden('token', $token) !!}
                    <div class="form-group">
                        <div class="col-md-6 col-md-offset-3">
                            {!! Form::submit(trans('pages.password_reset'), array('class' => 'btn btn-primary btn-block')) !!}
                        </div>
                    </div>
                    {!! Honeypot::generate('formuser', 'formtime') !!}
                    {!! Form::close() !!}
                    <div class="col-md-6 col-md-offset-3 top15 text-center">
                        {!! link_to_route('app.get.login', trans('pages.back_to_login')) !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
