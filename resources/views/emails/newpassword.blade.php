@extends('front.layouts.email', ['adminEmail' => $adminEmail])

{{-- Content --}}
@section('content')
    <h2>@lang('emails.password_new')</h2>
    <p><b>@lang('emails.password_new_text'):</b> {{ $newPassword }}</p>
@stop