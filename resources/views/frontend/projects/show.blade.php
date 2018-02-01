@extends('frontend.layouts.default')
{{-- Web site Title --}}
@section('title')
    @parent
    {{ $project->title }}
@stop

{{-- Content --}}
@section('content')
    {!! Breadcrumbs::render('webauth.projects.show', $project) !!}
    @include('frontend.projects.partials.project-info')
    @include('frontend.projects.partials.expedition-table')
    @include('frontend.projects.partials.expedition-trashed-table')
@stop
