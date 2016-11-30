@extends('backend.layouts.app')

@section('htmlheader_title')
    Groups
@endsection

@section('contentheader_title', 'Groups')


@section('main-content')
    @include('backend.groups.partials.form')
    @include('backend.groups.partials.groups')
    @include('backend.groups.partials.trashed')
@endsection