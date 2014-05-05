@extends('layouts.default')

{{-- Web site Title --}}
@section('title')
@parent
@lang('pages.password-forgot')
@stop

{{-- Content --}}
@section('content')
<div class="row">
    <div class="col-md-4 col-md-offset-4">
        {{ Form::open(array('action' => 'UsersController@forgot', 'method' => 'post')) }}
            
            <h2>@lang('pages.forgot-your-pass')</h2>
            
            <div class="form-group {{ ($errors->has('email')) ? 'has-error' : '' }}">
                {{ Form::text('email', null, array('class' => 'form-control', 'placeholder' => trans('pages.email'), 'autofocus')) }}
                {{ ($errors->has('email') ? $errors->first('email') : '') }}
            </div>

            {{ Form::submit(trans('buttons.send-instructions'), array('class' => 'btn btn-primary btn-xs'))}}

  		{{ Form::close() }}
  	</div>
</div>

@stop