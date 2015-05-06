@extends('layouts.email')

{{-- Content --}}
@section('content')
		<h2><h2>{{ trans('emails.welcome') }}</h2></h2>

		<p><b>{{ trans('email.account') }}:</b> {{{ $email }}}</p>
		<p>{{ trans('email.activate_message_html') }}, {{{ $activateHtmlLink }}}</p>
		<p>{{ trans('email.activate_message_text') }}: <br /> {{{ $activateTextLink }}}</p>
@stop