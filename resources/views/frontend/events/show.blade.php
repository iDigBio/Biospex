@extends('frontend.layouts.default')
{{-- Web site Title --}}
@section('title')
    @parent
    {{ $event->title }}
@stop

{{-- Content --}}
@section('content')
    @include('frontend.events.partials.event-info')
    @include('frontend.events.partials.group-table')
@stop