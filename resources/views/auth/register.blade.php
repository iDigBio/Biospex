@extends('frontend.layouts.default')

{{-- Web site Title --}}
@section('title')
    @parent
    @lang('pages.register')
@stop

{{-- Content --}}
@section('content')
    <div class="row centered-form top30">
        <!--   <div class="col-xs-12 col-sm-8 col-md-8 col-sm-offset-4 col-md-offset-4"> -->
        <div class="col-xs-12 col-sm-6 col-sm-offset-3">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h3 class="panel-title">{{ trans('pages.register_account') }}</h3>
                </div>
                <div class="panel-body">
                    {!! Form::open([
                    'route' => 'app.post.register',
                    'method' => 'post',
                    'class' => 'form-horizontal',
                    'role' => 'form'
                    ]) !!}
                    {!! csrf_field() !!}
                    <div class="col-xs-6 col-sm-6 col-md-6">
                        <div class="form-group">
                            <div class="input-group {{ ($errors->has('first_name')) ? 'has-error' : '' }}">
                                <span class="input-group-addon"><i class="fa fa-user fa-lg"></i></span>
                                {!! Form::text('first_name', '', ['id' => 'first_name', 'class' => 'form-control', 'placeholder' => trans('pages.first_name'),]) !!}
                            </div>
                            {{ $errors->first('first_name') }}
                        </div>
                    </div>
                    <div class="col-xs-6 col-sm-6 col-md-6">
                        <div class="form-group">
                            <div class="input-group {{ ($errors->has('last_name')) ? 'has-error' : '' }}">
                                <span class="input-group-addon"><i class="fa fa-user fa-lg"></i></span>
                                {!! Form::text('last_name', '', ['id' => 'last_name', 'class' => 'form-control', 'placeholder' => trans('pages.last_name')]) !!}
                            </div>
                            {{ $errors->first('last_name') }}
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-12">
                        <div class="form-group">
                            <div class="input-group {{ ($errors->has('email')) ? 'has-error' : '' }}">
                                <span class="input-group-addon"><i class="fa fa-envelope fa-lg"></i></span>
                                {!! Form::email('email', $email, ['id' => 'email', 'class' => 'form-control', 'placeholder' => trans('pages.email')]) !!}
                            </div>
                            {{ $errors->first('email') }}
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-12">
                        <div class="form-group">
                            <div class="input-group {{ ($errors->has('password')) ? 'has-error' : '' }}">
                                <span class="input-group-addon"><i class="fa fa-lock fa-lg"></i></span>
                                {!! Form::password('password', ['id' => 'password', 'class' => 'form-control', 'placeholder' => trans('pages.password')]) !!}
                            </div>
                            {{ $errors->first('password') }}
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-12">
                        <div class="form-group">
                            <div class="input-group {{ ($errors->has('password_confirmation')) ? 'has-error' : '' }}">
                                <span class="input-group-addon"><i class="fa fa-lock fa-lg"></i></span>
                                {!! Form::password('password_confirmation', ['class' => 'form-control', 'id' =>'password_confirmation', 'placeholder' => trans('pages.password_confirmation')]) !!}
                            </div>
                            {{ $errors->first('password_confirmation') }}
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-12">
                        <div class="form-group">
                            <div class="input-group {{ ($errors->has('invite')) ? 'has-error' : '' }}">
                                <span class="input-group-addon"><i class="fa fa-users fa-lg"></i></span>
                                {!! Form::text('invite', $code, array('class' => 'form-control', 'placeholder' => trans('groups.invite_code'))) !!}
                            </div>
                            {{ ($errors->has('invite') ?  $errors->first('invite') : '') }}
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-12">
                        <div class="form-group">
                            {!! Honeypot::generate('registeruser', 'registertime') !!}
                            {!! Form::submit(trans('buttons.register'), array('class' => 'btn btn-primary btn-block')) !!}
                            {!! Form::hidden('apiuser', 0) !!}
                            {!! Form::close() !!}
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-12 top15 text-center">
                        {!! link_to_route('app.get.login', trans('pages.already_have_account')) !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop