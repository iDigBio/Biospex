@extends('backend.layouts.app')

@section('htmlheader_title')
    Actors
@endsection

@section('contentheader_title', 'Actors')


@section('main-content')
    @include('backend.actors.partials.form')
    @include('backend.actors.partials.actors')
    @include('backend.actors.partials.trashed')
@endsection