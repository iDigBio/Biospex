@extends('front.api.default')

{{-- Web site Title --}}
@section('title')
@lang('pages.login')
@stop

{{-- Content --}}
@section('content')
<div class="row top30">
    <div class="col-md-4 col-md-offset-4">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <h3 class="panel-title">{!! trans('pages.signin') !!}</h3>
            </div>
            <div class="panel-body">
                {!! Form::open([
                'route' => 'api.post.login',
                'method' => 'post',
                'class' => 'form-horizontal',
                'role' => 'form'
                ]) !!}
                <div class="col-md-12">
                    <div class="form-group {{ ($errors->has('email')) ? 'has-error' : '' }}">
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-envelope fa-lg"></i></span>
                            {!! Form::email('email', '', ['id' => 'email', 'class' => 'form-control', 'placeholder' => 'Email address', 'required', 'autocomplete' => 'off']) !!}
                        </div>
                        {{ ($errors->has('email') ?  $errors->first('email') : '') }}
                    </div>
                </div>
                <div class="col-md-12">
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
                    <div class="col-md-12 top15 text-center">
                        {!! link_to_route('api.password.request', trans('pages.password_forgot')) !!} ||
                        {!! link_to_route('api.get.resend', trans('pages.resend_activation')) !!} ||
                        {!! link_to_route('api.get.register', trans('pages.register')) !!}
                   </div>
               </div>
            </div>
        </div>
    </div>
</div>
@stop