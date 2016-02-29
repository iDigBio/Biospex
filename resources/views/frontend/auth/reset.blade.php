@extends('frontend.layouts.default')

{{-- Web site Title --}}
@section('title')
    @parent
    @lang('pages.password_reset')
@stop

{{-- Content --}}
@section('content')
    <div class="row centered-form top-buffer">
        <div class="col-xs-12 col-sm-8 col-md-4 col-sm-offset-2 col-md-offset-4">
            <div class="panel panel-info middle-panel">
                <div class="panel-heading">
                    <h3 class="panel-title">{{ trans('pages.password_reset') }}</h3>
                </div>
                <div class="panel-body">
                    {!! Form::open([
                    'route' => 'password.post.reset',
                    'method' => 'post',
                    'class' => 'form-horizontal',
                    'role' => 'form'
                    ]) !!}
                    {!! Form::hidden('token', $token) !!}
                    <div class="col-xs-12 col-sm-12 col-md-12">
                        <div class="form-group {{ ($errors->has('email')) ? 'has-error' : '' }}">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-envelope fa-lg"></i></span>
                                {!! Form::email('email', '', ['id' => 'email', 'class' => 'form-control', 'placeholder' => 'Email address']) !!}
                            </div>
                            {{ ($errors->has('email') ?  $errors->first('email') : '') }}
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
                    {!! Form::submit(trans('pages.password_reset'), array('class' => 'btn btn-primary btn-block')) !!}
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
@stop

