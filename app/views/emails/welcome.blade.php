@extends('layouts.email', ['adminEmail' => $adminEmail])

{{-- Content --}}
@section('content')
		<h2>@lang('emails.welcome')</h2>
		<p><b>@lang('emails.account'):</b> {{{ $email }}}</p>
		<p><b>@lang('emails.activate_message_html'),</b> {{ $activateHtmlLink }}</p>
		<p><b>@lang('emails.activate_message_text'):</b> {{ $activateTextLink }}</p>
@stop