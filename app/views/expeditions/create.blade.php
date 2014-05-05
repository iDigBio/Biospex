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

    <div class="form-group {{ ($errors->has('title')) ? 'has-error' : '' }}" for="title">
        {{ Form::label('title', trans('forms.title'), array('class' => 'col-sm-2 control-label')) }}
        <div class="col-sm-10">
            {{ Form::text('title', null, array('class' => 'form-control', 'placeholder' => trans('forms.title'))) }}
        </div>
        {{ ($errors->has('title') ? $errors->first('title') : '') }}
    </div>

    <div class="form-group {{ ($errors->has('description')) ? 'has-error' : '' }}">
        {{ Form::label('description', trans('forms.description'), array('class' => 'col-sm-2 control-label')) }}
        <div class="col-sm-10">
            {{ Form::textarea('description', null, array('class' => 'form-control', 'placeholder' => trans('forms.description'))) }}
        </div>
        {{ ($errors->has('description') ? $errors->first('description') : '') }}
    </div>

    <div class="form-group {{ ($errors->has('keywords')) ? 'has-error' : '' }}">
        {{ Form::label('keywords', trans('forms.keywords'), array('class' => 'col-sm-2 control-label')) }}
        <div class="col-sm-10">
            {{ Form::text('keywords', null, array('class' => 'form-control', 'placeholder' => trans('forms.keywords'))) }}
        </div>
        {{ ($errors->has('keywords') ? $errors->first('keywords') : '') }}
    </div>

    <div class="form-group {{ ($errors->has('subjects')) ? 'has-error' : '' }}" for="title">
        {{ Form::label('subjects', trans('forms.assign-subjects'), array('class' => 'col-sm-2 control-label')) }}
        <div class="col-sm-10">
            {{ Form::text('subjects', null, array('class' => 'form-control', 'placeholder' => $subjects . ' ' . trans('forms.unassigned'))) }}
        </div>
        {{ ($errors->has('subjects') ? $errors->first('subjects') : '') }}
    </div>

    <div class="form-group">
        <div class="col-sm-offset-2 col-sm-10">
            {{ Form::hidden('group_id', $group->id) }}
            {{ Form::hidden('project_id', $project->id) }}
            {{ Form::hidden('id', $expedition->id) }}
            {{ Form::submit(trans('buttons.create'), array('class' => 'btn btn-primary btn-xs'))}}
        </div>
    </div>
    {{ Form::close()}}
</div>

@stop