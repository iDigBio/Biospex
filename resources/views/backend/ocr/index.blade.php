@extends('backend.layouts.app')

@section('htmlheader_title')
    OCR
@endsection

@section('contentheader_title', 'Manage OCR files')

@section('main-content')
    @include('backend.ocr.partials.files')
@endsection
