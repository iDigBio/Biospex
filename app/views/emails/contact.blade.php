@extends('layouts.email', ['adminEmail' => $adminEmail])

{{-- Content --}}
@section('content')
    <h2>@lang('emails.contact') }}</h2>

    <p><b>@lang('emails.contact_first'):</b> {{{ $first_name }}}</p>
    <p><b>@lang('emails.contact_last'):</b> {{{ $last_name }}}</p>
    <p><b>@lang('emails.contact_email'):</b> {{{ $email }}}</p>
    <p><b>@lang('emails.contact_message'):</b><br />
        {{{ $email_message }}}
    </p>
@stop
