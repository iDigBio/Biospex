@extends('backend.layouts.app')

@section('htmlheader_title')
    Resources
@endsection

@section('contentheader_title', 'Resources')


@section('main-content')
    @include('backend.resources.partials.form')
    @include('backend.resources.partials.resources')
@endsection