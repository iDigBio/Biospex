@extends('layouts.default')

{{-- Web site Title --}}
@section('title')
@parent
{{ trans('projects.add_data') }}
@stop

{{-- Content --}}
@section('content')

<h2>{{ $project->title }}</h2>
<h4>{{ trans('projects.add_data') }}</h4>
<div class="well">
    {{ Form::open(array(
        'action' => array('dataUpload', $project->group_id, $project->id),
        'method' => 'post',
        'files' => true,
        'enctype' => 'multipart/form-data',
        'id' => 'formAddData',
        'class' => 'form-horizontal',
        'role' => 'form'
    )) }}

    <div class="form-group {{ ($errors->has('file')) ? 'has-error' : '' }}">
        {{ Form::label('file', trans('forms.file'), array('id' => 'file', 'class' => 'col-sm-2 control-label')) }}
        <div class="col-sm-10">
            {{ Form::file('file') }}
        </div>
        {{ ($errors->has('file') ? $errors->first('file') : '') }}
    </div>

    <div class="form-group">
        <div class="col-sm-offset-2 col-sm-10">
            {{ Form::submit(trans('buttons.upload'), array('class' => 'btn btn-primary btn-xs'))}}
        </div>
    </div>
    {{ Form::close()}}
</div>

@stop