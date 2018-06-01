@extends('frontend.layouts.default')

{{-- Web site Title --}}
@section('title')
@parent
    @lang('pages.create') @lang('pages.expedition')
@stop

{{-- Content --}}
@section('content')
    <div class="col-xs-12">
        <div class="panel panel-info">
            <div class="panel-heading">
                <h3 class="panel-title">{{ trans('pages.create') }} {{ trans('pages.expedition') }}</h3>
            </div>
            <div class="panel-body">
                {!! Form::open([
                'route' => ['webauth.expeditions.store', $project->id],
                'method' => 'post',
                'class' => 'form-horizontal gridForm',
                'role' => 'form'
                ]) !!}

                <div class="form-group required {{ ($errors->has('title')) ? 'has-error' : '' }}" for="title">
                    {!! Form::label('title', trans('pages.title'), ['class' => 'col-sm-2 control-label']) !!}
                    <div class="col-sm-10">
                        {!! Form::text('title', null, ['class' => 'form-control', 'placeholder' => trans('pages.title')]) !!}
                        {{ ($errors->has('title') ? $errors->first('title') : '') }}
                    </div>
                </div>

                <div class="form-group required {{ ($errors->has('description')) ? 'has-error' : '' }}">
                    {!! Form::label('description', trans('pages.description'), ['class' => 'col-sm-2 control-label']) !!}
                    <div class="col-sm-10">
                        {!! Form::text('description', null, ['class' => 'form-control', 'placeholder' => trans('pages.description')]) !!}
                        {{ ($errors->has('description') ? $errors->first('description') : '') }}
                    </div>
                </div>

                <div class="form-group required {{ ($errors->has('keywords')) ? 'has-error' : '' }}">
                    {!! Form::label('keywords', trans('pages.keywords'), ['class' => 'col-sm-2 control-label']) !!}
                    <div class="col-sm-10">
                        {!! Form::text('keywords', null, ['class' => 'form-control', 'placeholder' => trans('pages.keywords')]) !!}
                        {{ ($errors->has('keywords') ? $errors->first('keywords') : '') }}
                    </div>
                </div>

                @if(in_array($project->workflow_id, Config::get('config.nfnWorkflows'), false))
                <div class="form-group {{ ($errors->has('workflow')) ? 'has-error' : '' }}">
                    {!! Form::label('workflow', trans('pages.nfn_workflow_id'), ['class' => 'col-sm-2 control-label']) !!}
                    <div class="col-sm-6">
                        {!! Form::text('workflow', null, ['disabled', 'class' => 'form-control', 'placeholder' => trans('pages.nfn_workflow_id_note_create')]) !!}
                        {{ ($errors->has('workflow') ? $errors->first('workflow') : '') }}
                    </div>
                </div>
                @endif

                <h4>{{ trans_choice('pages.subjects_assigned', 1) }} <span id="max">{{ trans('pages.subjects_assigned_max', ['count' => Config::get('config.expedition_size')]) }} </span>: <span id="subjectCountHtml">0</span></h4>
                <div id="jqtable">
                    <table class="table table-bordered jgrid" id="jqGridExpedition"></table>
                    <div id="pager"></div>
                    <br />
                    <button id="savestate" class="btn btn-default">Save Grid State</button>
                    <button id="loadstate" class="btn btn-default">Load Grid State</button>
                </div>
                <br />
                <div class="form-group">
                    <div class="col-sm-offset-2 col-sm-10">
                        {!! Form::hidden('subjectIds', null, ['name' => 'subjectIds', 'id' => 'subjectIds']) !!}
                        {!! Form::hidden('project_id', $project->id) !!}
                        {!! Form::submit(trans('pages.create'), ['class' => 'btn btn-primary']) !!}
                        {!! link_to(URL::previous(), trans('pages.cancel'), ['class' => 'btn btn-large btn-primary btn-danger']) !!}
                    </div>
                </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
    @include('frontend/layouts/jqgrid')
@stop