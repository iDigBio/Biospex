@extends('front.layouts.default')

{{-- Web site Title --}}
@section('title')
@parent
{{ trans('pages.add_data') }}
@stop

{{-- Content --}}
@section('content')
    {!! Breadcrumbs::render('projects.inside', $project) !!}

    <div class="jumbotron">
        <h4>{{ trans('pages.project') }}:</h4>
        <h2>{{ $project->title }}</h2>
        <p>{{ $project->description_short }}</p>
    </div>

    <div class="row">
        <div class="col-lg-4 col-md-4 col-sm-4">
            <div class="panel panel-info">
                <div class="panel-heading">
                    <h3 class="panel-title">{{ trans('pages.upload_darwin_file') }}</h3>
                </div>
                <div class="panel-body">
                    {!! Form::open([
                        'route' => ['projects.upload', $project->id],
                        'method' => 'post',
                        'files' => true,
                        'enctype' => 'multipart/form-data',
                        'id' => 'form-data',
                        'class' => 'form-vertical',
                        'role' => 'form'
                    ]) !!}
                    <p>{!! Html::link("#coreFileModal", 'Instructions', ['class' => 'btn btn-xs btn-info', 'data-toggle'=>'modal']) !!}</p>

                    <div class="form-group {{ ($errors->has('core')) ? 'has-error' : '' }}">
                        {!! Form::label('core', trans('forms.core_file'), ['id' => 'core', 'class' => 'control-label']) !!}
                        {!! Form::file('core') !!}
                        {{ ($errors->has('core') ? $errors->first('core') : '') }}
                    </div>

                    <div class="form-group">
                        <div class="col-sm-offset-2 col-sm-10">
                            {!! Form::submit(trans('buttons.upload'), ['class' => 'btn btn-xs btn-primary']) !!}
                            {!! link_to(URL::previous(), 'Cancel', ['class' => 'btn btn-xs btn-danger']) !!}
                        </div>
                    </div>
                    {!! Form::hidden('method', 'darwinCoreFileImport') !!}
                    {!! Form::hidden('project_id', $project->id) !!}
                    {!! Form::hidden('user_id', $project->group->user_id) !!}
                    {!! Form::close() !!}
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-4 col-sm-4">
            <div class="panel panel-info">
                <div class="panel-heading">
                    <h3 class="panel-title">{{ trans('pages.upload_recordset') }}</h3>
                </div>
                <div class="panel-body">
                    {!! Form::open(array(
                        'route' => array('projects.upload', $project->id),
                        'method' => 'post',
                        'id' => 'form-recordset',
                        'class' => 'form-vertical',
                        'role' => 'form'
                    )) !!}
                    <p>{!! Html::link("#recordsetModal", 'Instructions', ['class' => 'btn btn-xs btn-info', 'data-toggle'=>'modal']) !!}</p>

                    <div class="form-group {{ ($errors->has('recordset')) ? 'has-error' : '' }}">
                        {!! Form::label('recordset', trans('forms.recordset'), array('id' => 'recordset', 'class' => 'control-label')) !!}
                        {!! Form::text('recordset', Input::old('recordset'), ['id' => 'recordset', 'class' => 'form-control input-sm', 'placeholder' => trans('pages.recordset'),]) !!}
                        {{ ($errors->has('recordset') ? $errors->first('recordset') : '') }}
                    </div>

                    <div class="form-group">
                        <div class="col-sm-offset-2 col-sm-10">
                            {!! Form::submit(trans('buttons.upload'), ['class' => 'btn btn-xs btn-primary']) !!}
                            {!! link_to(URL::previous(), 'Cancel', ['class' => 'btn btn-xs btn-danger']) !!}
                        </div>
                    </div>
                    {!! Form::hidden('method', 'recordSetImport') !!}
                    {!! Form::hidden('project_id', $project->id) !!}
                    {!! Form::hidden('user_id', $project->group->user_id) !!}
                    {!! Form::close() !!}
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-4 col-sm-4">
            <div class="panel panel-info">
                <div class="panel-heading">
                    <h3 class="panel-title">{{ trans('pages.upload_nfn_results') }}</h3>
                </div>
                <div class="panel-body">
                    {!! Form::open([
                        'route' => ['projects.upload', $project->id],
                        'method' => 'post',
                        'files' => true,
                        'enctype' => 'multipart/form-data',
                        'id' => 'form-trans',
                        'class' => 'form-vertical',
                        'role' => 'form'
                    ]) !!}
                    <p>{!! Html::link("#transcriptionModal", 'Instructions', ['class' => 'btn btn-xs btn-info', 'data-toggle'=>'modal']) !!}</p>
                    <div class="form-group {{ ($errors->has('transcription')) ? 'has-error' : '' }}">
                        {!! Form::label('transcription', trans('forms.transcription_file'), ['id' => 'transcription', 'class' => 'control-label']) !!}
                        {!! Form::file('transcription') !!}

                        {{ ($errors->has('transcription') ? $errors->first('transcription') : '') }}
                    </div>

                    <div class="form-group">
                        <div class="col-sm-offset-2 col-sm-10">
                            {!! Form::submit(trans('buttons.upload'), ['class' => 'btn btn-xs btn-primary']) !!}
                            {!! link_to(URL::previous(), 'Cancel', ['class' => 'btn btn-xs btn-danger']) !!}
                        </div>
                    </div>
                    {!! Form::hidden('method', 'nfnTranscriptionImport') !!}
                    {!! Form::hidden('project_id', $project->id) !!}
                    {!! Form::hidden('user_id', $project->group->user_id) !!}
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-4 col-md-4 col-sm-4">
            <div class="panel panel-info">
                <div class="panel-heading">
                    <h3 class="panel-title">{{ trans('pages.upload_darwin_url') }}</h3>
                </div>
                <div class="panel-body">
                    {!! Form::open([
                        'route' => ['projects.upload', $project->id],
                        'method' => 'post',
                        'id' => 'form-core-url',
                        'class' => 'form-vertical',
                        'role' => 'form'
                    ]) !!}
                    <p>{!! Html::link("#coreUrlModal", 'Instructions', ['class' => 'btn btn-xs btn-info', 'data-toggle'=>'modal']) !!}</p>

                    <div class="form-group {{ ($errors->has('core-url')) ? 'has-error' : '' }}">
                        {!! Form::label('core-url', trans('forms.core_url'), array('id' => 'core-url', 'class' => 'control-label')) !!}
                        {!! Form::text('core-url', Input::old('core-url'), ['id' => 'url', 'class' => 'form-control input-sm', 'placeholder' => trans('pages.core_url'),]) !!}
                        {{ ($errors->has('core-url') ? $errors->first('core-url') : '') }}
                    </div>

                    <div class="form-group">
                        <div class="col-sm-offset-2 col-sm-10">
                            {!! Form::submit(trans('buttons.upload'), ['class' => 'btn btn-xs btn-primary']) !!}
                            {!! link_to(URL::previous(), 'Cancel', ['class' => 'btn btn-xs btn-danger']) !!}
                        </div>
                    </div>
                    {!! Form::hidden('method', 'darwinCoreUrlImport') !!}
                    {!! Form::hidden('project_id', $project->id) !!}
                    {!! Form::hidden('user_id', $project->group->user_id) !!}
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
    @include('front.layouts.import-modal')
@stop