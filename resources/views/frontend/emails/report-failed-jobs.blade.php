@extends('frontend.layouts.email')

{{-- Content --}}
@section('content')
    <h2>@lang('emails.failed_job_subject')</h2>
    <p>
    <blockquote>
        {{ $text }}
    </blockquote>
    </p>
@stop