@extends('backend.layouts.app')

@section('htmlheader_title')
    Pages
@endsection

@section('contentheader_title', 'Pages')


@section('main-content')
    @include('backend.pages.partials.form')
    @include('backend.pages.partials.pages')
@endsection