@extends('layouts.email', ['adminEmail' => $adminEmail])

{{-- Content --}}
@section('content')
		<h2>{{ trans('emails.welcome') }}</h2>
		<p><b>{{ trans('emails.account') }}:</b> {{{ $email }}}</p>
		<p>{{ trans('emails.activate_message_html') }}, {{ $activateHtmlLink }}</p>
		<p>{{ trans('emails.activate_message_text') }}: {{ $activateTextLink }}</p>
@stop