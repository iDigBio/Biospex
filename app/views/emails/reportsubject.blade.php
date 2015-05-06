@extends('layouts.email', ['adminEmail' => $adminEmail])

{{-- Content --}}
@section('content')
    <h2>@lang('emails.import_complete')</h2>

    <p>@lang('emails.import_message'):</p>
    <p>
    <blockquote>
        <b>@lang('projects.project'):</b> {{{ $projectTitle }}}
        <br />
        {{{ $importMessage }}}<br />
    </blockquote>
    </p>
@stop
