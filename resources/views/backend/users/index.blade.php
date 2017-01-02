@extends('backend.layouts.app')

@section('htmlheader_title')
    Users
@endsection

@section('contentheader_title', 'Users')


@section('main-content')
    @include('backend.users.partials.form')
    @include('backend.users.partials.users')
    @include('backend.users.partials.trashed')
@endsection