@extends('backend.layouts.app')

@section('htmlheader_title')
    Projects
@endsection

@section('contentheader_title', 'Projects')


@section('main-content')
    @include('backend.projects.partials.form')
    @include('backend.projects.partials.projects')
    @include('backend.projects.partials.trashed')
@endsection