@extends('layouts.default')

{{-- Web site Title --}}
@section('title')
@parent
{{ trans('pages.create') }} {{ trans('projects.project') }}
@stop

{{-- Content --}}
@section('content')

<h2>{{ $group->name }}</h2>
<h4>{{ trans('pages.create') }} {{ trans('projects.project') }}</h4>
<div class="well">
    {{ Form::open(array(
    'action' => array('ProjectsController@store', $group->id),
    'method' => 'post',
    'enctype' => 'multipart/form-data',
    'class' => 'form-horizontal',
    'class' => 'form-horizontal',
    'role' => 'form'
    )) }}

    @include('partials.project-fields', compact('project'))

    <div class="form-group">
        <button id="add_target" class="btn btn-default btn-xs" type="button">@lang('buttons.target-add')</button>
        <button id="remove_target" class="btn btn-default btn-xs" type="button">@lang('buttons.target-remove')</button>
    </div>

    <div class="form-group">
        <div class="col-sm-offset-2 col-sm-10">
            {{ Form::hidden('targetCount', 0, array('id' => 'targetCount')) }}
            {{ Form::hidden('group_id', $group->id) }}
            {{ Form::submit(trans('buttons.create'), array('class' => 'btn btn-primary btn-xs'))}}
        </div>
    </div>
    {{ Form::close()}}
</div>
@stop