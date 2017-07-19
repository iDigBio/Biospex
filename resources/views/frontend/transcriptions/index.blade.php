@extends('frontend.layouts.default')

{{-- Web site Title --}}
@section('title')
    @parent
    @lang('expeditions.transcriptions')
@stop

{{-- Content --}}
@section('content')
    {!! Breadcrumbs::render('web.transcriptions.show.title', $expedition, 'Transcriptions') !!}

    <div class="jumbotron">
        <h2>{{ $expedition->title }} @lang('expeditions.transcriptions')</h2>
        <p>{{ $expedition->description }}</p>
    </div>

    <div class="row">
        <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-body">
                <button title="Back to Expedition Details" class="btn btn-info btn-xs" type="button"
                        onClick="location.href='{{ route('web.expeditions.show', [$expedition->project->id, $expedition->id]) }}'">
                    <span class="glyphicon glyphicon-eye-open"></span> Return
                </button>
            </div>
        </div>
        </div>
    </div>


    <div class="row">
        <div class="col-md-3">
            <div class="panel panel-info">
                <div class="panel-heading">
                    <h3 class="panel-title">{{ trans('pages.default') }}</h3>
                </div>
                <div class="panel-body">
                    Content
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="panel panel-info">
                <div class="panel-heading">
                    <h3 class="panel-title">{{ trans('pages.default') }}</h3>
                </div>
                <div class="panel-body">
                    Content
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="panel panel-info">
                <div class="panel-heading">
                    <h3 class="panel-title">{{ trans('pages.default') }}</h3>
                </div>
                <div class="panel-body">
                    Content
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="panel panel-info">
                <div class="panel-heading">
                    <h3 class="panel-title">{{ trans('pages.default') }}</h3>
                </div>
                <div class="panel-body">
                    Content
                </div>
            </div>
        </div>
    </div>
@stop