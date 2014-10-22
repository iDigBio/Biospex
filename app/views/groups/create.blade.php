@extends('layouts.default')

{{-- Web site Title --}}
@section('title')
@parent
@lang('groups.group_create')
@stop

{{-- Content --}}
@section('content')
{{ Breadcrumbs::render('groups.show-with-link') }}
<div class="row">
    <div class="col-md-4 col-md-offset-4">
	{{ Form::open(array('action' => 'GroupsController@store', 'class' => 'form-horizontal')) }}
        <legend>@lang('groups.group_create')</legend>
    
        <div class="form-group {{ ($errors->has('name')) ? 'has-error' : '' }}">
            {{ Form::text('name', null, array('class' => 'form-control', 'placeholder' => trans('pages.name'))) }}
            {{ ($errors->has('name') ? $errors->first('name') : '') }}
        </div>

        {{ Form::hidden('user_id', $user->id) }}

        {{ Form::submit(trans('buttons.create'), array('class' => 'btn btn-primary')) }}

    {{ Form::close() }}
    </div>
</div>

@stop