@extends('frontend.layouts.default')
{{-- Web site Title --}}
@section('title')
    @parent
    {{ $project->title }} @lang('pages.project_stats')
@stop

{{-- Content --}}
@section('content')
    {!! Breadcrumbs::render('web.projects.show.title', $project, trans('pages.project_stats')) !!}
    @include('frontend.statistics.partials.project-info')
    <div class="row">
        <div class="col-md-6">
            <div class="panel panel-info">
                <div class="panel-heading panel-title">
                    {{ trans('pages.transcriber_summary') }}
                    <i class="fa fa-expand pull-right"></i>
                </div>
                <div id="transcribers" class="panel-body">
                    <table class="table-responsive table-sort">
                        <thead>
                        <tr>
                            <th>User</th>
                            <th>Expeditions</th>
                            <th>Transcriptions</th>
                            <th>Last Date</th>
                        </tr>
                        </thead>
                        @each('frontend.statistics.partials.transcriber', $transcribers, 'transcriber')
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div id="chartTranscriptionsDiv"></div>
        </div>
    </div>
@stop