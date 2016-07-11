@extends('backend.layouts.app')

@section('htmlheader_title')
    Notices
@endsection

@section('contentheader_title', 'Notices')


@section('main-content')
    @include('backend.notices.partials.form')
    @include('backend.notices.partials.notices')
    @include('backend.notices.partials.trashed')
@endsection