@extends('layouts.default')

{{-- Web site Title --}}
@section('title')
@parent
@lang('pages.password_forgot')
@stop

{{-- Content --}}
@section('content')
<div class="row centered-form">
    <div class="col-xs-12 col-sm-8 col-md-4 col-sm-offset-2 col-md-offset-4">
        <div class="panel panel-info middle-panel">
            <div class="panel-heading">
                <h3 class="panel-title">{{ trans('pages.forgot_your_pass') }}</h3>
            </div>
            <div class="panel-body">
                {{ Form::open(array('action' => 'UsersController@forgot', 'method' => 'post')) }}
                <div class="row">
                    <div class="col-xs-12 col-sm-12 col-md-12">
                        <div class="form-group {{ ($errors->has('email')) ? 'has-error' : '' }}">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="glyphicon glyphicon-envelope"></i></span>
                                {{Form::email('email', '', ['id' => 'email', 'class' => 'form-control', 'placeholder' => 'Email address'])}}
                            </div>
                            {{ ($errors->has('email') ?  $errors->first('email') : '') }}
                        </div>
                    </div>
                </div>
                {{ Form::submit(trans('buttons.send_instructions'), array('class' => 'btn btn-primary btn-block'))}}
                {{Form::close()}}
                <div class="row">
                    <div class="col-xs-12 col-sm-12 col-md-12 margin-top-10">
                        {{ link_to_action('login', trans('pages.back_to_login'))}} <i class="glyphicon glyphicon-log-in"></i>
                   </div>
               </div>
            </div>
        </div>
    </div>
</div>
@stop