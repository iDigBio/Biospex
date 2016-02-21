@extends('frontend.layouts.email')

{{-- Content --}}
@section('content')
    <p>{{ $importMessage }}</p>
    <p>{{ $csvMessage }}</p>
    <p>{{ $ocrImportMessage }}</p>
@stop
