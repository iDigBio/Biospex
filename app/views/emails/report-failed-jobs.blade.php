@extends('layouts.email', ['adminEmail' => $adminEmail])

{{-- Content --}}
@section('content')
    <h2>@lang('emails.failed_job_subject')</h2>
    <p>
    <blockquote>
        {{{ $text }}}
    </blockquote>
    </p>
@stop