@extends('layouts.default')

{{-- Web site Title --}}
@section('title')
@lang('pages.login')
@stop

{{-- Content --}}
@section('content')
<div class="row centered-form top-margin">
    <div class="col-xs-12 col-sm-8 col-md-4 col-sm-offset-2 col-md-offset-4">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">{!! trans('pages.signin') !!}</h3>
            </div>
            <div class="panel-body">
                {!! Form::open(array('action' => 'SessionsController@store')) !!}
                <div class="row">
                    <div class="col-xs-12 col-sm-12 col-md-12">
                        <div class="form-group {{ ($errors->has('email')) ? 'has-error' : '' }}">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="glyphicon glyphicon-envelope"></i></span>
                                {!! Form::email('email', '', ['id' => 'email', 'class' => 'form-control', 'placeholder' => 'Email address', 'required', 'autocomplete' => 'off']) !!}
                            </div>
                            {{ ($errors->has('email') ?  $errors->first('email') : '') }}
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-12 col-sm-12 col-md-12">
                        <div class="form-group">
                            <div class="input-group {{ ($errors->has('password')) ? 'has-error' : '' }}">
                                <span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
                                {!! Form::password('password', ['id' => 'password', 'class' => 'form-control', 'placeholder' => 'Password', 'required', 'autocomplete' => 'off']) !!}
                            </div>
                            {{ ($errors->has('password') ?  $errors->first('password') : '') }}
                        </div>
                    </div>
                </div>
                {!!Form::label('remember', trans('pages.rememberme')) !!}
                {!! Form::checkbox('remember') !!}
                <input type="submit" value="Login" class="btn btn-primary btn-block">
                {!! Form::close() !!}
                <div class="row">
                    <div class="col-xs-12 col-sm-12 col-md-12 margin-top-10">
                        {!! link_to_route('forgotPasswordForm', trans('pages.password_forgot')) !!}
                        or <i class="glyphicon glyphicon-log-in"></i> {!! link_to_route('register', trans('pages.register')) !!}
                   </div>
               </div>
            </div>
        </div>
    </div>
</div>
@stop