@extends('front.layouts.default')

{{-- Web site Title --}}
@section('title')
@parent
@lang('pages.edit_profile')
@stop

{{-- Content --}}
@section('content')

<h4>@lang('pages.account_profile')</h4>

<div class="well clearfix">
    <div class="col-md-8">
        <p><strong>{{ trans('pages.first_name') }}:</strong> {{ $user->profile->first_name }} </p>
        <p><strong>{{ trans('pages.last_name') }}:</strong> {{ $user->profile->last_name }} </p>
        <p><strong>{{ trans('pages.email') }}:</strong> {{ $user->email }}</p>
    </div>
    <div class="col-md-4">
        <p><em>{{ trans('pages.account_created') }}: {{ format_date($user->created_at, 'Y-m-d', $user->timezone) }}</em></p>
        <p><em>{{ trans('pages.last_updated') }}: {{ format_date($user->updated_at, 'Y-m-d', $user->timezone) }}</em></p>
    </div>
</div>
<h4>{{ trans('pages.edit') }}
    @if ($user->email == Sentry::getUser()->email)
    {{ trans('pages.your') }}
    @else
    {{ $user->email }}'s
    @endif
{{ trans('pages.profile') }}</h4>
<div class="well">
	{!! Form::open([
        'action' => array('UsersController@update', $user->id),
        'method' => 'put',
        'class' => 'form-horizontal', 
        'role' => 'form'
        ]) !!}
        
        <div class="form-group {{ ($errors->has('first_name')) ? 'has-error' : '' }}" for="first_name">
            {!! Form::label('edit_first_name', trans('pages.first_name'), array('class' => 'col-sm-2 control-label')) !!}
            <div class="col-sm-10">
              {!! Form::text('first_name', $user->profile->first_name, array('class' => 'form-control', 'placeholder' => trans('pages.first_name'), 'id' => 'edit_first_name')) !!}
            </div>
            {{ ($errors->has('first_name') ? $errors->first('first_name') : '') }}
    	</div>

        <div class="form-group {{ ($errors->has('last_name')) ? 'has-error' : '' }}" for="last_name">
            {!! Form::label('edit_last_name', trans('pages.last_name'), array('class' => 'col-sm-2 control-label')) !!}
            <div class="col-sm-10">
              {!! Form::text('last_name', $user->profile->last_name, array('class' => 'form-control', 'placeholder' => trans('pages.last_name'), 'id' => 'edit_last_name')) !!}
            </div>
            {{ ($errors->has('last_name') ? $errors->first('last_name') : '') }}
        </div>

        <div class="form-group {{ ($errors->has('email')) ? 'has-error' : '' }}" for="email">
            {!! Form::label('edit_email', trans('pages.email'), array('class' => 'col-sm-2 control-label')) !!}
            <div class="col-sm-10">
                {!! Form::text('email', $user->email, array('class' => 'form-control', 'placeholder' => trans('pages.email'), 'id' => 'edit_email')) !!}
            </div>
            {{ ($errors->has('email') ? $errors->first('email') : '') }}
        </div>

        <div class="form-group {{ ($errors->has('timezone')) ? 'has-error' : '' }}" for="timezone">
            {!! Form::label('timezone', trans('forms.timezone'), array('class' => 'col-sm-2 control-label')) !!}
            <div class="col-sm-10">
                {!! Form::select('timezone', $timezones, $user->timezone, array('class' => 'form-control')) !!}
            </div>
            {{ ($errors->has('timezone') ? $errors->first('timezone') : '') }}
        </div>


        @if ($superUser)
        <div class="form-group" for="activated">
            {!! Form::label('edit_activated', trans('pages.activated'), array('class' => 'col-sm-2 control-label')) !!}
            <div class="checkbox col-sm-10">
                {!! Form::checkbox('activated', '1', $user->activated, array('class' => 'name')); !!}
            </div>
        </div>
        @else
        {!! Form::hidden('activated', $user->activated) !!}
        @endif

        @if ($userEditGroups)
            @include('front.partials.editusergroups')
        @endif


        @if ($userEditPermissions)
            @include('front.partials.edituserpermissions')
        @endif
        <div class="form-group">
            <div class="col-sm-offset-2 col-sm-10">
                {!! Form::hidden('id', $user->id) !!}
                {!! Form::submit(trans('buttons.update'), array('class' => 'btn btn-primary')) !!}
                {!! Form::button(trans('buttons.cancel'), ['class' => 'btn btn-large btn-default', 'onClick' => "location.href='$cancel'"]) !!}
            </div>
      </div>
    {!! Form::close() !!}
</div>

<h4>@lang('pages.password_change')</h4>
<div class="well">
    {!! Form::open(array(
        'action' => array('UsersController@change', $user->id),
        'class' => 'form-inline', 
        'role' => 'form'
        )) !!}
        
        <div class="form-group {{ $errors->has('oldPassword') ? 'has-error' : '' }}">
        	{!! Form::label('oldPassword', trans('pages.password_old'), array('class' => 'sr-only')) !!}
			{!! Form::password('oldPassword', array('class' => 'form-control', 'placeholder' => trans('pages.password_old'))) !!}
    	</div>

        <div class="form-group {{ $errors->has('newPassword') ? 'has-error' : '' }}">
        	{!! Form::label('newPassword', trans('pages.password_new'), array('class' => 'sr-only')) !!}
            {!! Form::password('newPassword', array('class' => 'form-control', 'placeholder' => trans('pages.password_new'))) !!}
    	</div>

    	<div class="form-group {{ $errors->has('newPassword_confirmation') ? 'has-error' : '' }}">
        	{!! Form::label('newPassword_confirmation', trans('pages.password_new_confirm'), array('class' => 'sr-only')) !!}
            {!! Form::password('newPassword_confirmation', array('class' => 'form-control', 'placeholder' => trans('pages.password_new_confirm'))) !!}
    	</div>

        {!! Form::submit(trans('buttons.update'), array('class' => 'btn btn-primary')) !!}
	        	
      {{ ($errors->has('oldPassword') ? '<br />' . $errors->first('oldPassword') : '') }}
      {{ ($errors->has('newPassword') ?  '<br />' . $errors->first('newPassword') : '') }}
      {{ ($errors->has('newPassword_confirmation') ? '<br />' . $errors->first('newPassword_confirmation') : '') }}

      {!! Form::close() !!}
  </div>

@stop