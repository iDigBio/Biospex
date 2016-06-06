@extends('frontend.layouts.default')

{{-- Web site Title --}}
@section('title')
    @parent
    {{trans('pages.faq')}}
@stop

{{-- Content --}}
@section('content')
    <div class="jumbotron">
        <h3>{{ trans('pages.faq_title') }}</h3>
    </div>
    <div class="row">
        <div class="col-sm-10">
            <hr/>
            <div class="col-xs-2">
                <ul class="nav nav-tabs tabs-left sideways">
                    @if( ! $categories->isEmpty())
                        @foreach($categories as $category)
                            @include('frontend.partials.faq-tab-loop')
                        @endforeach
                    @endif
                </ul>
            </div>

            <div class="col-xs-8">
                <!-- Tab panes -->
                <div class="tab-content">
                    @if( ! $categories->isEmpty())
                        <div class="tab-pane {{ $category->id === 1 ? 'active' : '' }}" id="{{ $category->name }}">
                            <div id="accordion" role="tablist" aria-multiselectable="true">
                                @foreach($categories as $category)
                                    @foreach($category->faqs as $faq)
                                        @include('frontend.partials.faq-content-loop')
                                    @endforeach
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>

        </div>

    </div>
@stop