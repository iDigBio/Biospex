@extends('frontend.layouts.default')

{{-- Web site Title --}}
@section('title')
@parent
{{ trans('pages.add_data') }}
@stop

{{-- Content --}}
@section('content')
    {!! Breadcrumbs::render('web.projects.show.title', $project, 'Import') !!}
    <div class="jumbotron">
        <h3>{{ $project->title }}</h3>
        <p>{{ $project->description_short }}</p>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="panel panel-info">
                <div class="panel-heading">
                    <h3  class="panel-title">{{ trans('pages.upload_darwin_file') }}</h3>
                </div>
                <div class="panel-body">
                    {!! Form::open([
                        'route' => ['web.dwcfile.upload', $project->id],
                        'method' => 'post',
                        'files' => true,
                        'enctype' => 'multipart/form-data',
                        'id' => 'form-data',
                        'class' => 'form-horizontal',
                        'role' => 'form'
                    ]) !!}
                    <p>{!! link_to("#dataFileModal", 'Instructions', ['class' => 'btn btn-sm btn-info', 'data-toggle'=>'modal']) !!}</p>

                    <div class="form-group {{ ($errors->has('dwc')) ? 'has-error' : '' }}">
                        {!! Form::label('dwc', trans('forms.file'), ['id' => 'dwc', 'class' => 'col-sm-2 control-label']) !!}
                        <div class="col-md-10">
                            {!! Form::file('dwc') !!}
                        </div>
                        {{ ($errors->has('dcw') ? $errors->first('dcw') : '') }}
                    </div>

                    <div class="form-group">
                        <div class="col-md-offset-2 col-md-10">
                            {!! Form::submit(trans('buttons.upload'), ['class' => 'btn btn-sm btn-primary']) !!}
                            {!! link_to(URL::previous(), trans('buttons.cancel'), ['class' => 'btn btn-sm btn-primary btn-danger']) !!}
                        </div>
                    </div>
                    {!! Form::hidden('user_id', $project->group->user_id) !!}
                    {!! Form::close() !!}
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="panel panel-info">
                <div class="panel-heading">
                    <h3 class="panel-title">{{ trans('pages.upload_recordset') }}</h3>
                </div>
                <div class="panel-body">
                    {!! Form::open([
                        'route' => ['web.recordset.upload', $project->id],
                        'method' => 'post',
                        'id' => 'form-recordset',
                        'class' => 'form-horizontal',
                        'role' => 'form'
                    ]) !!}
                    <p>{!! link_to("#recordsetModal", 'Instructions', ['class' => 'btn btn-sm btn-info', 'data-toggle'=>'modal']) !!}</p>

                    <div class="form-group {{ ($errors->has('recordset')) ? 'has-error' : '' }}">
                        {!! Form::label('recordset', trans('forms.recordset'), ['id' => 'recordset', 'class' => 'col-sm-2 control-label']) !!}
                        <div class="col-md-10">
                            {!! Form::text('recordset', Input::old('recordset'), ['id' => 'recordset', 'class' => 'form-control input-sm', 'placeholder' => trans('pages.recordset'),]) !!}
                        </div>
                        {{ ($errors->has('recordset') ? $errors->first('recordset') : '') }}
                    </div>

                    <div class="form-group">
                        <div class="col-md-offset-2 col-md-10">
                            {!! Form::submit(trans('buttons.upload'), ['class' => 'btn btn-sm btn-primary']) !!}
                            {!! link_to(URL::previous(), trans('buttons.cancel'), ['class' => 'btn btn-sm btn-primary btn-danger']) !!}
                        </div>
                    </div>
                    {!! Form::hidden('user_id', $project->group->user_id) !!}
                    {!! Form::close() !!}
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="panel panel-info">
                <div class="panel-heading">
                    <h3 class="panel-title">{{ trans('pages.upload_darwin_url') }}</h3>
                </div>
                <div class="panel-body">
                    {!! Form::open([
                        'route' => ['web.dwcuri.upload', $project->id],
                        'method' => 'post',
                        'id' => 'form-data-url',
                        'class' => 'form-horizontal',
                        'role' => 'form'
                    ]) !!}
                    <p>{!! link_to("#dataUrlModal", 'Instructions', ['class' => 'btn btn-sm btn-info', 'data-toggle'=>'modal']) !!}</p>

                    <div class="form-group {{ ($errors->has('data-url')) ? 'has-error' : '' }}">
                        {!! Form::label('data-url', trans('forms.url'), array('id' => 'data-url', 'class' => 'col-sm-2 control-label')) !!}
                        <div class="col-md-10">
                            {!! Form::text('data-url', Input::old('data-url'), ['id' => 'data-url', 'class' => 'form-control input-sm', 'placeholder' => trans('pages.core_url'),]) !!}
                        </div>
                        {{ ($errors->has('data-url') ? $errors->first('data-url') : '') }}
                    </div>

                    <div class="form-group">
                        <div class="col-md-offset-2 col-md-10">
                            {!! Form::submit(trans('buttons.upload'), ['class' => 'btn btn-sm btn-primary']) !!}
                            {!! link_to(URL::previous(), trans('buttons.cancel'), ['class' => 'btn btn-sm btn-primary btn-danger']) !!}
                        </div>
                    </div>
                    {!! Form::hidden('user_id', $project->group->user_id) !!}
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
    @include('frontend.layouts.import-modal')
@stop