@extends('frontend.layouts.default')

{{-- Web site Title --}}
@section('title')
    @parent
    {{trans('pages.faq')}}
@stop

{{-- Content --}}
@section('content')
    <div class="row top30">
        <div class="col-md-8 col-md-offset-2">
            <h2>{{ trans('pages.faq_title') }}</h2>
        </div>
    </div>
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <ul class="nav nav-tabs">
                @foreach($categories as $category)
                    <li class="{{ $category->id === 1 ? 'active' : '' }}">
                        <a href="#{{ $category->name }}" data-toggle="tab">{{ $category->name }}</a>
                    </li>
                @endforeach
            </ul>
            <!-- Tab panes -->
            <div class="tab-content">
                @foreach($categories as $category)
                    @include('frontend.faqs.partials.faq-tab-loop')
                @endforeach

            </div>
        </div>
    </div>
@stop