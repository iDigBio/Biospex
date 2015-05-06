@extends('layouts.email', ['adminEmail' => $adminEmail])

{{-- Content --}}
@section('content')
    <p>
    {{ $completeMessage }}
    </p>
@stop