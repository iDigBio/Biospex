@extends('frontend.api.default')

{{-- Web site Title --}}
@section('title')
@parent
@lang('pages.password_forgot')
@stop

{{-- Content --}}
@section('content')
<div class="row centered-form top30">
    <div class="col-md-4 col-md-offset-4">
        <div class="panel panel-primary middle-panel">
            <div class="panel-heading">
                <h3 class="panel-title">{{ trans('pages.forgot_your_pass') }}</h3>
            </div>
            <div class="panel-body">
                {!! Form::open([
                'route' => 'api.password.email',
                'method' => 'post',
                'class' => 'form-horizontal',
                'role' => 'form'
                ]) !!}
                <div class="col-md-12">
                    <div class="form-group {{ ($errors->has('email')) ? 'has-error' : '' }}">
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-envelope fa-lg"></i></span>
                            {!! Form::email('email', '', ['id' => 'email', 'class' => 'form-control', 'placeholder' => 'Email address']) !!}
                        </div>
                        {{ ($errors->has('email') ?  $errors->first('email') : '') }}
                    </div>
                </div>
                {!! Form::submit(trans('pages.send_instructions'), array('class' => 'btn btn-primary btn-block')) !!}
                {!! Form::close() !!}
                <div class="col-md-6 col-md-offset-3 top15 text-center">
                    {!! link_to_route('api.get.login', trans('pages.back_to_login')) !!}
                </div>
            </div>
        </div>
    </div>
</div>
@stop