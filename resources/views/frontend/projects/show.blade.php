@extends('frontend.layouts.default')
{{-- Web site Title --}}
@section('title')
    @parent
    {{ $project->title }}
@stop

{{-- Content --}}
@section('content')
    @include('frontend.projects.partials.project-info')
    @include('frontend.projects.partials.expedition-table')
@stop
