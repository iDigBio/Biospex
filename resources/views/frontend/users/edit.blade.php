@extends('front.layouts.default')

{{-- Web site Title --}}
@section('title')
@parent
@lang('pages.edit_profile')
@stop

{{-- Content --}}
@section('content')
    <div class="col-xs-12">
        <div class="panel panel-info top20">
            <div class="panel-heading">
                <h3 class="panel-title">{{ trans('pages.account_profile') }}</h3>
            </div>
            <div class="panel-body">
                <div class="row">
                <div class="col-md-8">
                    <p><strong>{{ trans('pages.first_name') }}:</strong> {{ $user->profile->first_name }} </p>
                    <p><strong>{{ trans('pages.last_name') }}:</strong> {{ $user->profile->last_name }} </p>
                    <p><strong>{{ trans('pages.email') }}:</strong> {{ $user->email }}</p>
                </div>
                <div class="col-md-4">
                    <p><em>{{ trans('pages.account_created') }}: {{ DateHelper::formatDate($user->created_at, 'Y-m-d', $user->profile->timezone) }}</em></p>
                    <p><em>{{ trans('pages.last_updated') }}: {{ DateHelper::formatDate($user->updated_at, 'Y-m-d', $user->profile->timezone) }}</em></p>
                </div>
                </div>
                {!! Form::open([
                'route' => ['admin.users.update', $user->id],
                'method' => 'put',
                'class' => 'form-horizontal',
                'enctype' => 'multipart/form-data',
                'role' => 'form'
                ]) !!}
                <div class="form-group clearfix required {{ ($errors->has('first_name')) ? 'has-error' : '' }}">
                    {!! Form::label('edit_first_name', trans('pages.first_name'), ['class' => 'col-sm-2 control-label']) !!}
                    <div class="col-sm-6">
                      {!! Form::text('first_name', $user->profile->first_name, array('class' => 'form-control', 'placeholder' => trans('pages.first_name'))) !!}
                    </div>
                    {{ ($errors->has('first_name') ? $errors->first('first_name') : '') }}
                </div>

                <div class="form-group required {{ ($errors->has('last_name')) ? 'has-error' : '' }}">
                    {!! Form::label('edit_last_name', trans('pages.last_name'), array('class' => 'col-sm-2 control-label')) !!}
                    <div class="col-sm-6">
                      {!! Form::text('last_name', $user->profile->last_name, array('class' => 'form-control', 'placeholder' => trans('pages.last_name'), 'id' => 'edit_last_name')) !!}
                    </div>
                    {{ ($errors->has('last_name') ? $errors->first('last_name') : '') }}
                </div>

                <div class="form-group required {{ ($errors->has('email')) ? 'has-error' : '' }}">
                    {!! Form::label('edit_email', trans('pages.email'), array('class' => 'col-sm-2 control-label')) !!}
                    <div class="col-sm-6">
                        {!! Form::text('email', $user->email, array('class' => 'form-control', 'placeholder' => trans('pages.email'), 'id' => 'edit_email')) !!}
                    </div>
                    {{ ($errors->has('email') ? $errors->first('email') : '') }}
                </div>

                <div class="form-group {{ ($errors->has('timezone')) ? 'has-error' : '' }}">
                    {!! Form::label('timezone', trans('pages.timezone'), array('class' => 'col-sm-2 control-label')) !!}
                    <div class="col-sm-6">
                        {!! Form::select('timezone', $timezones, $user->profile->timezone, array('class' => 'form-control')) !!}
                    </div>
                    {{ ($errors->has('timezone') ? $errors->first('timezone') : '') }}
                </div>

                <div class="form-group {{ ($errors->has('avatar')) ? 'has-error' : '' }}">
                    {!! Form::label('avatar', trans('pages.avatar'), ['class' => 'col-sm-2 control-label']) !!}
                    <div class="col-sm-5">
                        {!! Form::file('avatar') !!}
                    </div>
                    <div class="col-sm-5">
                        <img src="{{ $user->profile->present()->avatar_medium }}"/>
                    </div>
                    {{ ($errors->has('avatar') ? $errors->first('avatar') : '') }}
                </div>

                <div class="form-group">
                    <div class="col-sm-offset-2 col-sm-10">
                        {!! Form::submit(trans('pages.update'), array('class' => 'btn btn-primary')) !!}
                        {!! Form::button(trans('pages.cancel'), ['class' => 'btn btn-large btn-default', 'onClick' => "location.href='$cancel'"]) !!}
                    </div>
                </div>
                {!! Form::hidden('id', $user->id) !!}
                {!! Form::close() !!}
            </div>

            <div class="panel-body">
                <h4>@lang('pages.password_change')</h4>
                {!! Form::open([
                    'route' => ['admin.users.password', $user->id],
                    'method' => 'put',
                    'class' => 'form-inline',
                    'role' => 'form'
                    ]) !!}
                <div class="form-group required {{ $errors->has('oldPassword') ? 'has-error' : '' }}">
                    {!! Form::label('oldPassword', trans('pages.password_old'), array('class' => 'sr-only')) !!}
                    {!! Form::password('oldPassword', array('class' => 'form-control', 'placeholder' => trans('pages.password_old'))) !!}
                </div>

                <div class="form-group required {{ $errors->has('newPassword') ? 'has-error' : '' }}">
                    {!! Form::label('newPassword', trans('pages.password_new'), array('class' => 'sr-only')) !!}
                    {!! Form::password('newPassword', array('class' => 'form-control', 'placeholder' => trans('pages.password_new'))) !!}
                </div>

                <div class="form-group requred {{ $errors->has('newPassword_confirmation') ? 'has-error' : '' }}">
                    {!! Form::label('newPassword_confirmation', trans('pages.password_new_confirm'), array('class' => 'sr-only')) !!}
                    {!! Form::password('newPassword_confirmation', array('class' => 'form-control', 'placeholder' => trans('pages.password_new_confirm'))) !!}
                </div>
                {!! Form::hidden('id', $user->id) !!}
                {!! Form::submit(trans('pages.update'), array('class' => 'btn btn-primary')) !!}

                {!! ($errors->has('oldPassword') ? '<br />' . $errors->first('oldPassword') : '') !!}
                {!! ($errors->has('newPassword') ?  '<br />' . $errors->first('newPassword') : '') !!}
                {!! ($errors->has('newPassword_confirmation') ? '<br />' . $errors->first('newPassword_confirmation') : '') !!}

                {!! Form::close() !!}
            </div>
        </div>
    </div>
@stop