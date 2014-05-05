@extends('layouts.default')

{{-- Web site Title --}}
@section('title')
@parent
@lang('pages.resend-activation')
@stop

{{-- Content --}}
@section('content')
<div class="row">
    <div class="col-md-4 col-md-offset-4">
        {{ Form::open(array('action' => 'UsersController@resend', 'method' => 'post')) }}
        	
            <h2>@lang('pages.resend-activation-email')</h2>
    		
            <div class="form-group {{ ($errors->has('email')) ? 'has-error' : '' }}">
                {{ Form::text('email', null, array('class' => 'form-control', 'placeholder' => trans('pages.email'), 'autofocus')) }}
                {{ ($errors->has('email') ? $errors->first('email') : '') }}
            </div>

            {{ Form::submit(trans('buttons.resend'), array('class' => 'btn btn-primary btn-xs')) }}

        {{ Form::close() }}
    </div>
</div>

@stop
