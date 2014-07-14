@extends('layouts.default')

{{-- Web site Title --}}
@section('title')
@parent
{{ trans('pages.edit') }} {{ $project->title }}
@stop

{{-- Content --}}
@section('content')

<h4>{{ trans('pages.edit') }} {{ $project->title }}</h4>
<div class="well">
    {{ Form::open(array(
    'action' => array('ProjectsController@update', $project->group_id, $project->id),
    'method' => 'put',
    'class' => 'form-horizontal',
    'role' => 'form'
    )) }}

    @include('partials.project-fields', compact('project'))

    <div class="form-group">
        <button id="add_target" class="btn btn-default" type="button">@lang('buttons.target_add')</button>
        <button id="remove_target" class="btn btn-default" type="button">@lang('buttons.target_remove')</button>
    </div>

    <div class="form-group">
        <div class="col-sm-offset-2 col-sm-10">
            {{ Form::hidden('targetCount', $count, array('id' => 'targetCount')) }}
            {{ Form::hidden('user_id', $project->user_id) }}
            {{ Form::hidden('group_id', $project->group_id) }}
            {{ Form::hidden('id', $project->id) }}
            {{ Form::submit(trans('buttons.update'), array('class' => 'btn btn-primary'))}}
        </div>
    </div>
    {{ Form::close()}}
</div>
@stop