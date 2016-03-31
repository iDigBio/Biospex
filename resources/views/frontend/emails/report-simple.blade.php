@extends('frontend.layouts.email')

{{-- Content --}}
@section('content')
    <p>
    <blockquote>
        <b>@lang('projects.project'):</b> {{ $projectTitle }}
        <br /><br />
        {{ $mainMessage }}
        <br />
    </blockquote>
    </p>
@stop