@extends('layouts.email', ['adminEmail' => $adminEmail])

{{-- Content --}}
@section('content')
    <p>{{{ $importMessage }}}</p>
    <p>{{{ $csvMessage }}}</p>
    <p>{{{ $ocrImportMessage }}}</p>
@stop
