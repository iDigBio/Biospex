@extends('layouts.default')

{{-- Web site Title --}}
@section('title')
@parent
@if ($register)
    @lang('pages.register')
@else
    @lang('pages.create')
@endif
@stop

{{-- Content --}}
@section('content')
<div class="row">
    <div class="col-md-4 col-md-offset-4">
        {{ Form::open(array('action' => 'UsersController@store')) }}

        <h2>@lang('pages.register_account')</h2>

        <div class="form-group {{ ($errors->has('email')) ? 'has-error' : '' }}">
            {{ Form::text('email', $email, array('class' => 'form-control', 'placeholder' => trans('pages.email'))) }}
            {{ ($errors->has('email') ? $errors->first('email') : '') }}
        </div>

        <div class="form-group {{ ($errors->has('password')) ? 'has-error' : '' }}">
            {{ Form::password('password', array('class' => 'form-control', 'placeholder' => trans('pages.password'))) }}
            {{ ($errors->has('password') ?  $errors->first('password') : '') }}
        </div>

        <div class="form-group {{ ($errors->has('password_confirmation')) ? 'has-error' : '' }}">
            {{ Form::password('password_confirmation', array('class' => 'form-control', 'placeholder' => trans('pages.password_confirm'))) }}
            {{ ($errors->has('password_confirmation') ?  $errors->first('password_confirmation') : '') }}
        </div>
        @if ($register)
            <div class="form-group {{ ($errors->has('invite')) ? 'has-error' : '' }}">
                {{ Form::text('invite', $code, array('class' => 'form-control', 'placeholder' => trans('groups.invite_code'))) }}
                {{ ($errors->has('invite') ?  $errors->first('invite') : '') }}
            </div>
        @else
            <div class="form-group">
                {{ Form::select('group', $groups, Input::old('group')) }}
            </div>
        @endif

        {{ Form::submit(trans('buttons.register'), array('class' => 'btn btn-primary')) }}
        @if ( ! $register)
            {{ Form::button(trans('buttons.cancel'), ['class' => 'btn btn-large btn-primary btn-danger', 'onClick' => "location.href='$cancel'"]) }}
        @endif

            
        {{ Form::close() }}
    </div>
</div>

@stop