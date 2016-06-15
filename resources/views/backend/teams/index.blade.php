@extends('backend.layouts.app')

@section('htmlheader_title')
    BIOSPEX Team
@endsection

@section('contentheader_title', 'BIOSPEX Team Members')


@section('main-content')
    @foreach($categories as $category)
        @include('backend.layouts.partials.teamcategories')
    @endforeach
@endsection