@extends('frontend.layouts.default')

{{-- Web site Title --}}
@section('title')
@parent
@lang('pages.resend_activation')
@stop

{{-- Content --}}
@section('content')
<div class="row centered-form top-buffer">
    <div class="col-xs-12 col-sm-8 col-md-4 col-sm-offset-2 col-md-offset-4">
        <div class="panel panel-info middle-panel">
            <div class="panel-heading">
                <h3 class="panel-title">{{  trans('pages.resend_activation_email') }}</h3>
            </div>
            <div class="panel-body">
                {!! Form::open([
                'route' => 'auth.post.resend',
                'method' => 'post',
                'class' => 'form-horizontal',
                'role' => 'form'
                ]) !!}
                <div class="col-xs-12 col-sm-12 col-md-12">
                    <div class="form-group {{ ($errors->has('email')) ? 'has-error' : '' }}">
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-envelope fa-lrg"></i></span>
                            {!! Form::email('email', '', ['id' => 'email', 'class' => 'form-control', 'placeholder' => trans('pages.email'), 'autofocus']) !!}
                        </div>
                        {{ ($errors->has('email') ?  $errors->first('email') : '') }}
                    </div>
                </div>
                <div class="col-xs-12 col-sm-12 col-md-12">
                    <div class="form-group">
                        {!! Form::submit(trans('buttons.resend'), array('class' => 'btn btn-primary btn-block')) !!}
                        {!! Form::close() !!}
                    </div>
                </div>
                <div class="col-xs-12 col-sm-12 col-md-12 margin-top-10">
                    {!! link_to_route('auth.get.login', trans('pages.back_to_login')) !!}
                </div>
            </div>
        </div>
    </div>
</div>
@stop
