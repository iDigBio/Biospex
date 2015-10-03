@extends('front.layouts.email', ['adminEmail' => $adminEmail])

{{-- Content --}}
@section('content')
    <h2>@lang('emails.password_reset')</h2>
    <p><b>@lang('emails.password_message_html'),</b> {{ $resetHtmlLink }}</p>
    <p><b>@lang('emails.password_message_text'):</b> {{ $resetTextLink }}</p>
    <p>@lang('emails.password_warning')</p>
@stop
