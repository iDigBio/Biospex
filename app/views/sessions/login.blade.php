@extends('layouts.default')

{{-- Web site Title --}}
@section('title')
@lang('pages.login')
@stop

{{-- Content --}}
@section('content')
<div class="row">
    <div class="col-md-4 col-md-offset-4">
        {{ Form::open(array('action' => 'SessionsController@store')) }}

            <h2 class="form-signin-heading">@lang('pages.signin')</h2>

            <div class="form-group {{ ($errors->has('email')) ? 'has-error' : '' }}">
                {{ Form::text('email', null, array('class' => 'form-control', 'placeholder' => trans('pages.email'), 'autofocus')) }}
                {{ ($errors->has('email') ? $errors->first('email') : '') }}
            </div>

            <div class="form-group {{ ($errors->has('password')) ? 'has-error' : '' }}">
                {{ Form::password('password', array('class' => 'form-control', 'placeholder' => trans('pages.password')))}}
                {{ ($errors->has('password') ?  $errors->first('password') : '') }}
            </div>
            
            <label class="checkbox">
                {{ Form::checkbox('rememberMe', 'rememberMe') }} @lang('pages.rememberme')
            </label>
            {{ Form::submit(trans('buttons.login'), array('class' => 'btn btn-primary btn-xs'))}}
            <a class="btn btn-link btn-xs" href="{{ route('forgotPasswordForm') }}">@lang('buttons.password-forgot')</a>
        {{ Form::close() }}
    </div>
</div>

@stop