@extends('layouts.default')

{{-- Web site Title --}}
@section('title')
@parent
{{ trans('pages.create') }} {{ trans('expeditions.expedition') }}
@stop

{{-- Content --}}
@section('content')
 
<h2>{{ $group->name }} : {{ $project->title }}</h2>
<h4>{{ trans('pages.create') }} {{ trans('expeditions.expedition') }}</h4>
<div class="well">
    {{ Form::open(array(
    'action' => array('ExpeditionsController@update', $group->id, $project->id),
    'method' => 'post',
    'class' => 'form-horizontal',
    'role' => 'form'
    )) }}

    @include('partials.expedition-fields')

    <div class="form-group">
        <div class="col-sm-offset-2 col-sm-10">
            {{ Form::hidden('group_id', $group->id) }}
            {{ Form::hidden('project_id', $project->id) }}
            {{ Form::submit(trans('buttons.create'), array('class' => 'btn btn-primary'))}}
        </div>
    </div>
    {{ Form::close()}}
</div>

@stop