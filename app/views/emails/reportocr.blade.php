@extends('layouts.email', ['adminEmail' => $adminEmail])

{{-- Content --}}
@section('content')
    <p>
    <blockquote>
        <b>@lang('projects.project'):</b> {{{ $projectTitle }}}
        <br /><br />
        {{{ $mainMessage }}}
        <br />
    </blockquote>
    </p>
@stop