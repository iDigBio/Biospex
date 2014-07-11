@extends('layouts.default')

{{-- Web site Title --}}
@section('title')
@parent
@lang('groups.group_create')
@stop

{{-- Content --}}
@section('content')
<div class="row">
    <div class="col-md-4 col-md-offset-4">
	{{ Form::open(array('action' => 'GroupsController@store')) }}
        <h2>@lang('groups.group_create')</h2>
    
        <div class="form-group {{ ($errors->has('name')) ? 'has-error' : '' }}">
            {{ Form::text('name', null, array('class' => 'form-control', 'placeholder' => trans('pages.name'))) }}
            {{ ($errors->has('name') ? $errors->first('name') : '') }}
        </div>

        {{ Form::hidden('user_id', $user->id) }}

        {{ Form::submit(trans('buttons.create'), array('class' => 'btn btn-primary btn-xs')) }}

    {{ Form::close() }}
    </div>
</div>1

@stop