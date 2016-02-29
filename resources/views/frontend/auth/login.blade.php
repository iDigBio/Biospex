@extends('frontend.layouts.default')

{{-- Web site Title --}}
@section('title')
@lang('pages.login')
@stop

{{-- Content --}}
@section('content')
<div class="row centered-form top-buffer">
    <div class="col-xs-12 col-sm-8 col-md-4 col-sm-offset-2 col-md-offset-4">
        <div class="panel panel-info">
            <div class="panel-heading">
                <h3 class="panel-title">{!! trans('pages.signin') !!}</h3>
            </div>
            <div class="panel-body">
                {!! Form::open([
                'route' => 'auth.post.login',
                'method' => 'post',
                'class' => 'form-horizontal',
                'role' => 'form'
                ]) !!}
                <div class="col-xs-12 col-sm-12 col-md-12">
                    <div class="form-group {{ ($errors->has('email')) ? 'has-error' : '' }}">
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-envelope fa-lg"></i></span>
                            {!! Form::email('email', '', ['id' => 'email', 'class' => 'form-control', 'placeholder' => 'Email address', 'required', 'autocomplete' => 'off']) !!}
                        </div>
                        {{ ($errors->has('email') ?  $errors->first('email') : '') }}
                    </div>
                </div>
                <div class="col-xs-12 col-sm-12 col-md-12">
                    <div class="form-group">
                        <div class="input-group {{ ($errors->has('password')) ? 'has-error' : '' }}">
                            <span class="input-group-addon"><i class="fa fa-lock fa-lg"></i></span>
                            {!! Form::password('password', ['id' => 'password', 'class' => 'form-control', 'placeholder' => 'Password', 'required', 'autocomplete' => 'off']) !!}
                        </div>
                        {{ ($errors->has('password') ?  $errors->first('password') : '') }}
                    </div>
                </div>
                {!!Form::label('remember', trans('pages.rememberme')) !!}
                {!! Form::checkbox('remember') !!}
                <input type="submit" value="Login" class="btn btn-primary btn-block">
                {!! Form::close() !!}
                <div class="row">
                    <div class="col-xs-12 col-sm-12 col-md-12 margin-top-10">
                        {!! link_to_route('password.get.email', trans('pages.password_forgot')) !!} ||
                        {!! link_to_route('auth.get.resend', trans('pages.resend_activation')) !!} ||
                        {!! link_to_route('auth.get.register', trans('pages.register')) !!}
                   </div>
               </div>
            </div>
        </div>
    </div>
</div>
@stop