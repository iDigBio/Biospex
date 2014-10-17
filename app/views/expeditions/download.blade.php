@extends('layouts.default')

{{-- Web site Title --}}
@section('title')
@parent
{{ $expedition->title }}
@stop

{{-- Content --}}
@section('content')

    <ul class="breadcrumb">
    <li><a href="{{ action('ProjectsController@show', [$expedition->project->id]) }}">{{ $expedition->project->title }}</a></li>
    <li>@lang('pages.created'): {{ $expedition->created_at }}</li>
    <li>@lang('pages.updated'): {{ $expedition->updated_at }}</li>
    </ul>

    <div class="jumbotron">
    <h4>Expedition:</h4>
    <h2>{{ $expedition->title }}</h2>
    <p>{{ $expedition->description }}</p>
    <p>@lang('pages.keywords'): {{ $expedition->keywords }} </p>
    </div>

</div>

@stop