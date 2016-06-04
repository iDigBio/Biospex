@extends('backend.layouts.app')

@section('htmlheader_title')
    OCR
@endsection

@section('contentheader_title', 'Manage OCR files')


@section('main-content')
    <div class="container spark-screen">
        {!! $phpInfo !!}
    </div>
@endsection