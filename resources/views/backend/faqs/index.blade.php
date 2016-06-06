@extends('backend.layouts.app')

@section('htmlheader_title')
    OCR
@endsection

@section('contentheader_title', 'Manage FAQs')


@section('main-content')
    <div class="row">
        <div class="col-xs-12">
            @foreach($categories as $category)
                @include('backend.layouts.partials.faqcategories')
            @endforeach
        </div>
    </div>
@endsection