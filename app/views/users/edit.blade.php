@extends('layouts.default')

{{-- Web site Title --}}
@section('title')
@parent
@lang('pages.edit_profile')
@stop

{{-- Content --}}
@section('content')

<h4>{{ trans('pages.edit') }}
@if ($user->email == Sentry::getUser()->email)
	{{ trans('pages.your') }}
@else 
	{{ $user->email }}'s 
@endif 

{{ trans('pages.profile') }}</h4>
<div class="well">
	{{ Form::open(array(
        'action' => array('UsersController@update', $user->id),
        'method' => 'put',
        'class' => 'form-horizontal', 
        'role' => 'form'
        )) }}
        
        <div class="form-group {{ ($errors->has('firstName')) ? 'has-error' : '' }}" for="firstName">
            {{ Form::label('edit_firstName', trans('pages.first_name'), array('class' => 'col-sm-2 control-label')) }}
            <div class="col-sm-10">
              {{ Form::text('firstName', $user->first_name, array('class' => 'form-control', 'placeholder' => trans('pages.first_name'), 'id' => 'edit_firstName'))}}
            </div>
            {{ ($errors->has('firstName') ? $errors->first('firstName') : '') }}    			
    	</div>

        <div class="form-group {{ ($errors->has('lastName')) ? 'has-error' : '' }}" for="lastName">
            {{ Form::label('edit_lastName', trans('pages.last_name'), array('class' => 'col-sm-2 control-label')) }}
            <div class="col-sm-10">
              {{ Form::text('lastName', $user->last_name, array('class' => 'form-control', 'placeholder' => trans('pages.last_name'), 'id' => 'edit_lastName'))}}
            </div>
            {{ ($errors->has('lastName') ? $errors->first('lastName') : '') }}                
        </div>

        <div class="form-group {{ ($errors->has('email')) ? 'has-error' : '' }}" for="email">
            {{ Form::label('edit_email', trans('pages.email'), array('class' => 'col-sm-2 control-label')) }}
            <div class="col-sm-10">
                {{ Form::text('email', $user->email, array('class' => 'form-control', 'placeholder' => trans('pages.email'), 'id' => 'edit_email'))}}
            </div>
            {{ ($errors->has('email') ? $errors->first('email') : '') }}
        </div>

        @if ($superUser)
        <div class="form-group" for="activated">
            {{ Form::label('edit_activated', trans('pages.activated'), array('class' => 'col-sm-2 control-label')) }}
            <div class="checkbox col-sm-10">
                {{ Form::checkbox('activated', '1', $user->activated, array('class' => 'name')); }}
            </div>
        </div>
        @endif

        @if ($userEditGroups)
            @include('partials.editusergroups')
        @endif


        @if ($userEditPermissions)
            @include('partials.edituserpermissions')
        @endif
        <div class="form-group">
            <div class="col-sm-offset-2 col-sm-10">
                {{ Form::hidden('id', $user->id) }}
                {{ Form::submit(trans('buttons.update'), array('class' => 'btn btn-primary'))}}
            </div>
      </div>
    {{ Form::close()}}
</div>

<h4>@lang('pages.password_change')</h4>
<div class="well">
    {{ Form::open(array(
        'action' => array('UsersController@change', $user->id),
        'class' => 'form-inline', 
        'role' => 'form'
        )) }}
        
        <div class="form-group {{ $errors->has('oldPassword') ? 'has-error' : '' }}">
        	{{ Form::label('oldPassword', trans('pages.password_old'), array('class' => 'sr-only')) }}
			{{ Form::password('oldPassword', array('class' => 'form-control', 'placeholder' => trans('pages.password_old'))) }}
    	</div>

        <div class="form-group {{ $errors->has('newPassword') ? 'has-error' : '' }}">
        	{{ Form::label('newPassword', trans('pages.password_new'), array('class' => 'sr-only')) }}
            {{ Form::password('newPassword', array('class' => 'form-control', 'placeholder' => trans('pages.password_new'))) }}
    	</div>

    	<div class="form-group {{ $errors->has('newPassword_confirmation') ? 'has-error' : '' }}">
        	{{ Form::label('newPassword_confirmation', trans('pages.password_new_confirm'), array('class' => 'sr-only')) }}
            {{ Form::password('newPassword_confirmation', array('class' => 'form-control', 'placeholder' => trans('pages.password_new_confirm'))) }}
    	</div>

        {{ Form::submit(trans('buttons.update'), array('class' => 'btn btn-primary'))}}
	        	
      {{ ($errors->has('oldPassword') ? '<br />' . $errors->first('oldPassword') : '') }}
      {{ ($errors->has('newPassword') ?  '<br />' . $errors->first('newPassword') : '') }}
      {{ ($errors->has('newPassword_confirmation') ? '<br />' . $errors->first('newPassword_confirmation') : '') }}

      {{ Form::close() }}
  </div>

@stop