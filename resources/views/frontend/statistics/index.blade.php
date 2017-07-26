@extends('frontend.layouts.default')
{{-- Web site Title --}}
@section('title')
    @parent
    {{ $project->title }}
@stop

{{-- Content --}}
@section('content')
    {!! Breadcrumbs::render('web.projects.show', $project) !!}
    @include('frontend.statistics.partials.project-info')
    <div class="row">
        <div class="col-md-3">
            <div class="panel panel-info">
                <div class="panel-heading panel-title">
                    {{ trans('pages.transcriber_summary') }}
                    <i class="fa fa-expand pull-right"></i>
                </div>
                <div id="transcribers" class="panel-body">
                    <table class="transcribers">
                    @each('frontend.statistics.partials.transcriber', $transcribers, 'transcriber')
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-9">
            <div id="chartTranscriptionsDiv"></div>
        </div>
    </div>
@stop