@extends('layouts.default')

{{-- Web site Title --}}
@section('title')
@parent
{{ trans('pages.add_data') }}
@stop

{{-- Content --}}
@section('content')
{{ Breadcrumbs::render('projects.inside', $project) }}
        
        <div class="jumbotron">
        <h4>Project:</h4>
        <h2>{{ $project->title }}</h2>
        <p>{{ $project->description_short }}</p>

        </div>

<div class="col-xs-12">
    <div class="col-lg-4 col-md-4 col-sm-4">
        <div class="panel panel-info">
            <div class="panel-heading">
                <h3 class="panel-title">{{ trans('pages.upload_darwin_file') }}</h3>
            </div>
            <div class="panel-body">
                {{ Form::open([
                    'action' => ['projects.upload', $project->id],
                    'method' => 'post',
                    'files' => true,
                    'enctype' => 'multipart/form-data',
                    'id' => 'form-data',
                    'class' => 'form-horizontal',
                    'role' => 'form'
                ]) }}
                <p>{{ HTML::link("#dataModal", 'Instructions', ['class' => 'btn btn-xs btn-primary', 'data-toggle'=>'modal']) }}</p>

                <div class="form-group {{ ($errors->has('file')) ? 'has-error' : '' }}">
                    {{ Form::label('file', trans('forms.file'), ['id' => 'file', 'class' => 'col-sm-2 control-label']) }}
                    <div class="col-sm-10">
                        {{ Form::file('file') }}
                    </div>
                    {{ ($errors->has('file') ? $errors->first('file') : '') }}
                </div>

                <div class="form-group">
                    <div class="col-sm-offset-2 col-sm-10">
                        {{ Form::submit(trans('buttons.upload'), ['class' => 'btn btn-xs btn-success'])}}
                        {{ Form::button(trans('buttons.cancel'), ['class' => 'btn btn-xs btn-primary btn-danger', 'onClick' => "location.href='$cancel'"]) }}
                    </div>
                </div>
                {{ Form::hidden('class', 'DarwinCoreImport') }}
                {{ Form::close()}}
            </div>
        </div>
    </div>

    <div class="col-lg-4 col-md-4 col-sm-4">
        <div class="panel panel-info">
            <div class="panel-heading">
                <h3 class="panel-title">{{ trans('pages.upload_recordset') }}</h3>
            </div>
            <div class="panel-body">
                {{ Form::open(array(
                    'action' => array('projects.upload', $project->id),
                    'method' => 'post',
                    'id' => 'form-recordset',
                    'class' => 'form-horizontal',
                    'role' => 'form'
                )) }}
                <p>{{ HTML::link("#recordsetModal", 'Instructions', ['class' => 'btn btn-xs btn-primary', 'data-toggle'=>'modal']) }}</p>

                <div class="form-group {{ ($errors->has('recordset')) ? 'has-error' : '' }}">
                    {{ Form::label('recordset', trans('forms.recordset'), array('id' => 'recordset', 'class' => 'col-sm-2 control-label')) }}
                    <div class="col-sm-10">
                        {{Form::text('recordset', Input::old('recordset'), ['id' => 'recordset', 'class' => 'form-control input-sm', 'placeholder' => trans('pages.recordset'),])}}
                    </div>
                    {{ ($errors->has('recordset') ? $errors->first('recordset') : '') }}
                </div>

                <div class="form-group">
                    <div class="col-sm-offset-2 col-sm-10">
                        {{ Form::submit(trans('buttons.upload'), ['class' => 'btn btn-xs btn-success'])}}
                        {{ Form::button(trans('buttons.cancel'), ['class' => 'btn btn-xs btn-primary btn-danger', 'onClick' => "location.href='$cancel'"]) }}
                    </div>
                </div>
                {{ Form::hidden('class', 'RecordSetImport') }}
                {{ Form::hidden('user_id', $project->group->user_id) }}
                {{ Form::close()}}
            </div>
        </div>
    </div>

    <div class="col-lg-4 col-md-4 col-sm-4">
        <div class="panel panel-info">
            <div class="panel-heading">
                <h3 class="panel-title">{{ trans('pages.upload_nfn_results') }}</h3>
            </div>
            <div class="panel-body">
                {{ Form::open([
                    'action' => ['projects.upload', $project->id],
                    'method' => 'post',
                    'files' => true,
                    'enctype' => 'multipart/form-data',
                    'id' => 'form-trans',
                    'class' => 'form-horizontal',
                    'role' => 'form'
                ]) }}
                <p>{{ HTML::link("#transcriptionModal", 'Instructions', ['class' => 'btn btn-xs btn-primary', 'data-toggle'=>'modal']) }}</p>
                <div class="form-group {{ ($errors->has('file')) ? 'has-error' : '' }}">
                    {{ Form::label('file', trans('forms.file'), ['id' => 'file', 'class' => 'col-sm-2 control-label']) }}
                    <div class="col-sm-10">
                        {{ Form::file('file') }}
                    </div>
                    {{ ($errors->has('file') ? $errors->first('file') : '') }}
                </div>

                <div class="form-group">
                    <div class="col-sm-offset-2 col-sm-10">
                        {{ Form::submit(trans('buttons.upload'), ['class' => 'btn btn-xs btn-success'])}}
                        {{ Form::button(trans('buttons.cancel'), ['class' => 'btn btn-xs btn-primary btn-danger', 'onClick' => "location.href='$cancel'"]) }}
                    </div>
                </div>
                {{ Form::hidden('class', 'NfnTranscriptionImport') }}
                {{ Form::close()}}
            </div>
        </div>
    </div>
</div>
@include('layouts.import-modal')
@stop