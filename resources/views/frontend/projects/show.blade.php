@extends('front.layouts.default')
{{-- Web site Title --}}
@section('title')
    @parent
    {{ $project->title }}
@stop

{{-- Content --}}
@section('content')
    @include('front.projects.partials.project-info')
    @include('front.projects.partials.expedition-table')
@stop
