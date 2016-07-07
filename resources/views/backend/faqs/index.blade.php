@extends('backend.layouts.app')

@section('htmlheader_title')
    Manage FAQs
@endsection

@section('contentheader_title', 'Manage FAQs')


@section('main-content')
        @include('backend.faqs.partials.forms')
        <div class="row">
            <div class="col-xs-12">
                @foreach($categories as $category)
                    @include('backend.faqs.partials.categories')
                @endforeach
            </div>
        </div>
@endsection

@section('scripts')
    <script src="//cdn.ckeditor.com/4.5.9/standard/ckeditor.js"></script>
@endsection