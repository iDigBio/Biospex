@extends('front.layouts.email')

{{-- Content --}}
@section('content')
    @lang('emails.click_reset_password'): {{ url('password/reset/' . $token) }}
@stop