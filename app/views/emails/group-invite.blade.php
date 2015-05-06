@extends('layouts.email', ['adminEmail' => $adminEmail])

{{-- Content --}}
@section('content')
    <h2>@lang('emails.welcome')</h2>
    <p>@lang('emails.group_invite_message', ['group' => $group, 'invite' => $invite])</p>
    <p></p>
@stop