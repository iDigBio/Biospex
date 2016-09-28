@extends('frontend.layouts.email')

{{-- Content --}}
@section('content')
    <h2>@lang('emails.error')</h2>
    <p>
    <blockquote>
        Error: {!! $error !!}
    </blockquote>
    </p>
@stop