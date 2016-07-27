@extends('frontend.layouts.default')

{{-- Web site Title --}}
@section('title')
    @parent
    {{trans('pages.faq')}}
@stop

{{-- Content --}}
@section('content')
    <div class="row centered-form top-buffer">
        <div class="col-xs-8 col-xs-offset-2">
            <h2>{{ trans('pages.faq_title') }}</h2>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-10 col-sm-offset-1">
            <hr/>
            <div class="col-xs-2"> <!-- required for floating -->
                <!-- Nav tabs -->
                <ul class="nav nav-tabs tabs-left sideways">
                    @foreach($categories as $category)
                        <li class="{{ $category->id === 1 ? 'active' : '' }}">
                            <a href="#{{ $category->name }}" data-toggle="tab">{{ $category->name }}</a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="col-xs-8">
                <!-- Tab panes -->
                <div class="tab-content">
                    @foreach($categories as $category)
                        @include('frontend.faqs.partials.faq-tab-loop')
                    @endforeach

                </div>
            </div>
        </div>
    </div>
@stop