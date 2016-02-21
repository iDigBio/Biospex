@extends('frontend.layouts.email')

{{-- Content --}}
@section('content')
		<h2>@lang('emails.welcome')</h2>
		<p><b>@lang('emails.account'):</b> {{ $email }}</p>
		<p><b>@lang('emails.activate_message_html'),</b> {!! link_to_route('auth.get.activate', 'Click Here', ['id' => $id, 'code' => $code]) !!}</p>
		<p><b>@lang('emails.activate_message_text'):</b> {!! route('auth.get.activate', ['id' => $id, 'code' => $code]) !!}</p>
@stop