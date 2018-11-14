@extends('front.layouts.default')
{{-- Web site Title --}}
@section('title')
    @parent
    {{ $event->title }}
@stop

{{-- Content --}}
@section('content')
    @include('front.events.partials.event-info')
    @include('front.events.partials.team-table')
@stop