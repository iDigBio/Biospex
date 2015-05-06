@extends('layouts.email', ['adminEmail' => $adminEmail])

{{-- Content --}}
@section('content')
    <h2>@lang('emails.error')</h2>
    <p>
    <blockquote>
        <b>@lang('projects.project'):</b> {{{ $projectTitle }}}<br />
        <b>@lang('projects.import_id'):</b> {{{ $importId }}}<br />
        <b>@lang('emails.error_message'):</b>
        <br />
        {{{ $errorMessage }}}
    </blockquote>
    </p>
@stop