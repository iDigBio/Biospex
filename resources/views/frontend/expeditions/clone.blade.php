@extends('frontend.layouts.default')

{{-- Web site Title --}}
@section('title')
@parent
{{ trans('pages.clone') }} {{ trans('expeditions.expedition') }}
@stop

{{-- Content --}}
@section('content')
    {!! Breadcrumbs::render('web.projects.show.title', $expedition->project, trans('pages.clone') . ' ' . trans('expeditions.expedition')) !!}
    <div class="col-xs-12">
        <div class="panel panel-info">
            <div class="panel-heading">
                <h3 class="panel-title">{{ trans('pages.create') }} {{ trans('expeditions.expedition') }}</h3>
            </div>
            <div class="panel-body">
                {!! Form::open([
                'route' => ['web.expeditions.store', $expedition->project->id],
                'method' => 'post',
                'class' => 'form-horizontal',
                'role' => 'form'
                ]) !!}

                <div class="form-group required {{ ($errors->has('title')) ? 'has-error' : '' }}" for="title">
                    {!! Form::label('title', trans('forms.title'), ['class' => 'col-sm-2 control-label']) !!}
                    <div class="col-sm-10">
                        {!! Form::text('title', $expedition->title, ['class' => 'form-control', 'placeholder' => trans('forms.title')]) !!}
                    </div>
                    {{ ($errors->has('title') ? $errors->first('title') : '') }}
                </div>

                <div class="form-group required {{ ($errors->has('description')) ? 'has-error' : '' }}">
                    {!! Form::label('description', trans('forms.description'), ['class' => 'col-sm-2 control-label']) !!}
                    <div class="col-sm-10">
                        {!! Form::text('description', $expedition->description, ['class' => 'form-control', 'placeholder' => trans('forms.description')]) !!}
                    </div>
                    {{ ($errors->has('description') ? $errors->first('description') : '') }}
                </div>

                <div class="form-group required {{ ($errors->has('keywords')) ? 'has-error' : '' }}">
                    {!! Form::label('keywords', trans('forms.keywords'), ['class' => 'col-sm-2 control-label']) !!}
                    <div class="col-sm-10">
                        {!! Form::text('keywords', $expedition->keywords, ['class' => 'form-control', 'placeholder' => trans('forms.keywords')]) !!}
                    </div>
                    {{ ($errors->has('keywords') ? $errors->first('keywords') : '') }}
                </div>

                @if(in_array($expedition->project->workflow_id, Config::get('config.nfnWorkflows'), false))
                    <div class="form-group {{ ($errors->has('nfn_workflow_id')) ? 'has-error' : '' }}">
                        {!! Form::label('nfn_workflow_id', trans('forms.nfn_workflow_id'), ['class' => 'col-sm-2 control-label']) !!}
                        <div class="col-sm-4">
                            {!! Form::text('nfn_workflow_id', null, ['class' => 'form-control', 'placeholder' => trans('forms.nfn_workflow_id_note')]) !!}
                            {{ ($errors->has('nfn_workflow_id') ? $errors->first('nfn_workflow_id') : '') }}
                        </div>
                    </div>
                @endif

                <h4>{{ trans_choice('pages.subjects_assigned', 1) }} <span id="max">{{ trans('pages.subjects_assigned_max') }}</span>: <span id="subjectCountHtml">0</span></h4>
                <div class="table-responsive" id="jqtable">
                    <input type="hidden" id="url" value="{{ URL::route('web.grids.create', [$expedition->project->id]) }}">
                    <input type="hidden" id="showCb" value="1">
                    <input type="hidden" id="projectId" value="{{ $expedition->project->id }}">
                    <input type="hidden" id="subjectCount" name="subjectCount" value="">
                    <input type="hidden" id="subjectIds" name="subjectIds" value="">
                    <table class="table table-bordered jgrid" id="jqGridExpedition"></table>
                    <div id="pager"></div>
                    <br />
                    <button id="savestate" class="btn btn-default">Save Grid State</button>
                    <button id="loadstate" class="btn btn-default">Load Grid State</button>
                </div>
                <br />
                <div class="form-group">
                    <div class="col-sm-offset-2 col-sm-10">
                        {!! Form::hidden('project_id', $expedition->project->id) !!}
                        {!! Form::submit(trans('buttons.create'), ['class' => 'btn btn-primary']) !!}
                        {!! link_to(URL::previous(), trans('buttons.cancel'), ['class' => 'btn btn-large btn-primary btn-danger']) !!}
                    </div>
                </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
    @include('frontend/layouts/jqgrid')
@stop