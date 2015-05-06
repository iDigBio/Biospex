@extends('layouts.email', ['adminEmail' => $adminEmail])

{{-- Content --}}
@section('content')
		<h2>@lang('emails.password_new')</h2>

		<p>@lang('emails.password_new_text'):</p>
		<p><blockquote>{{{ $newPassword }}}</blockquote></p>
@stop