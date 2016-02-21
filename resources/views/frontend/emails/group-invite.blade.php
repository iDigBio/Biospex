@extends('frontend.layouts.email')

{{-- Content --}}
@section('content')
    <h2>@lang('emails.welcome')</h2>
    <p>@lang('emails.group_invite_message', ['group' => $group, 'link' => link_to_route('auth.get.register', 'Click Here', ['code' => $code])])</p>
    <p></p>
@stop