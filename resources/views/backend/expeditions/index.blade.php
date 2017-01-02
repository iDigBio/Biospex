@extends('backend.layouts.app')

@section('htmlheader_title')
    Projects
@endsection

@section('contentheader_title', 'Expeditions')


@section('main-content')
    @include('backend.expeditions.partials.form')
    @include('backend.expeditions.partials.expeditions')
    @include('backend.expeditions.partials.trashed')
@endsection