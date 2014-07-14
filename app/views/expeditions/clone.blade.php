@extends('layouts.default')

{{-- Web site Title --}}
@section('title')
@parent
{{ trans('pages.clone') }} {{ trans('expeditions.expedition') }}
@stop

{{-- Content --}}
@section('content')

<h4>{{ trans('projects.project') }}: {{ $project->title }}</h4>
<h3>{{ trans('pages.clone') }} {{ trans('expeditions.expedition') }}</h3>
<div class="well">
    {{ Form::open(array(
    'action' => array('ExpeditionsController@update', $group->id, $project->id),
    'method' => 'post',
    'class' => 'form-horizontal',
    'role' => 'form'
    )) }}

    @include('partials.expedition-fields', compact('expedition'))

    <div class="form-group">
        <div class="col-sm-offset-2 col-sm-10">
            {{ Form::hidden('group_id', $group->id) }}
            {{ Form::hidden('project_id', $project->id) }}
            {{ Form::hidden('id', $expedition->id) }}
            {{ Form::submit(trans('buttons.create'), array('class' => 'btn btn-primary'))}}
        </div>
    </div>
    {{ Form::close()}}
</div>

@stop