@extends('layouts.default')

{{-- Web site Title --}}
@section('title')
@parent
@lang('pages.register')
@stop

{{-- Content --}}
@section('content')
<div class="row centered-form">
    <div class="col-xs-12 col-sm-8 col-md-4 col-sm-offset-2 col-md-offset-4">
        <div class="panel panel-info">
            <div class="panel-heading">
                <h3 class="panel-title">{{ trans('pages.register_account') }}</h3>
            </div>
            <div class="panel-body">
                {{ Form::open(array('action' => 'UsersController@store')) }}
                    <div class="row">
                        <div class="col-xs-6 col-sm-6 col-md-6">
                            <div class="form-group">
                                <div class="input-group {{ ($errors->has('first_name')) ? 'has-error' : '' }}">
                                    <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
                                    {{Form::text('first_name', '', ['id' => 'first_name', 'class' => 'form-control', 'placeholder' => trans('pages.first_name'),])}}
                                </div>
                                {{$errors->first('first_name')}}
                            </div>
                        </div>
                        <div class="col-xs-6 col-sm-6 col-md-6">
                            <div class="form-group">
                                <div class="input-group {{ ($errors->has('last_name')) ? 'has-error' : '' }}">
                                    <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
                                    {{Form::text('last_name', '', ['id' => 'last_name', 'class' => 'form-control', 'placeholder' => trans('pages.last_name')])}}
                                </div>
                                {{$errors->first('last_name')}}
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="input-group {{ ($errors->has('email')) ? 'has-error' : '' }}">
                            <span class="input-group-addon"><i class="glyphicon glyphicon-envelope"></i></span>
                            {{Form::email('email', $email, ['id' => 'email', 'class' => 'form-control', 'placeholder' => trans('pages.email')])}}
                        </div>
                        {{$errors->first('email')}}
                    </div>
                    <div class="row">
                        <div class="col-xs-12 col-sm-12 col-md-12">
                            <div class="form-group">
                                <div class="input-group {{ ($errors->has('password')) ? 'has-error' : '' }}">
                                    <span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
                                    {{Form::password('password', ['id' => 'password', 'class' => 'form-control', 'placeholder' => trans('pages.password')])}}
                                </div>
                                {{$errors->first('password')}}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-12 col-sm-12 col-md-12">
                            <div class="form-group">
                                <div class="input-group {{ ($errors->has('password_confirmation')) ? 'has-error' : '' }}">
                                    <span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
                                    {{Form::password('password_confirmation', ['class' => 'form-control', 'id' =>'password_confirmation', 'placeholder' => trans('pages.password_confirmation')])}}
                                </div>
                                {{$errors->first('password_confirmation')}}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-12 col-sm-12 col-md-12">
                            <div class="form-group {{ ($errors->has('invite')) ? 'has-error' : '' }}">
                                {{ Form::text('invite', $code, array('class' => 'form-control', 'placeholder' => trans('groups.invite_code'))) }}
                                {{ ($errors->has('invite') ?  $errors->first('invite') : '') }}
                            </div>
                        </div>
                        {{ Form::honeypot('registeruser', 'registertime') }}
                    </div>
                    {{ Form::submit(trans('buttons.register'), array('class' => 'btn btn-primary btn-block')) }}
                {{ Form::close() }}
                <div class="row">
                    <div class="col-xs-12 col-sm-12 col-md-12 margin-top-10">
                       {{link_to_action('login', trans('pages.already_have_account'))}}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop