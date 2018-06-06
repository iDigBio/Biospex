@extends('backend.layouts.app')

@section('htmlheader_title')
    Workflows
@endsection

@section('contentheader_title', 'Workflows')


@section('main-content')
    @include('backend.workflows.partials.form')
    @include('backend.workflows.partials.workflows')
@endsection