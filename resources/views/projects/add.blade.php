@extends('layouts.default')

{{-- Web site Title --}}
@section('title')
@parent
{{ trans('projects.add_data') }}
@stop

{{-- Content --}}
@section('content')
{{ Breadcrumbs::render('projects.inside', $project) }}
        
        <div class="jumbotron">
        <h4>Project:</h4>
        <h2>{{ $project->title }}</h2>
        <p>{{ $project->description_short }}</p>
        
        </div>


<div class="panel panel-default">
    {{ Form::open(array(
        'action' => array('projects.upload', $project->id),
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
            {{ Form::submit(trans('buttons.upload'), array('class' => 'btn btn-success'))}}
			{{ Form::button(trans('buttons.cancel'), ['class' => 'btn btn-large btn-primary btn-danger', 'onClick' => "location.href='$cancel'"]) }}
        </div>
    </div>
    {{ Form::close()}}
    <p>{{ trans('pages.add_data_desc') }}</p>
</div>

@stop