@extends('front.layouts.email')

{{-- Content --}}
@section('content')
    <h2>@lang('emails.error')</h2>
    <p>
    <blockquote>
    @lang('emails.error_message')<br />
    {{ $errorMessage }}
    </blockquote>
    </p>
@stop