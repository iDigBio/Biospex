@extends('frontend.layouts.email')

{{-- Content --}}
@section('content')
    <h2>@lang('emails.contact')</h2>

    <p><b>@lang('emails.contact_first'):</b> {{ $firstName }}</p>
    <p><b>@lang('emails.contact_last'):</b> {{ $lastName }}</p>
    <p><b>@lang('emails.contact_email'):</b> {{ $email }}</p>
    <p><b>@lang('emails.contact_message'):</b><br />
        {{ $emailMessage }}
    </p>
@stop
