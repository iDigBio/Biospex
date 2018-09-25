@extends('front.layout.default')

{{-- Web site Title --}}
@section('title')
    {{ __('Faq') }}
@stop

@section('header')
    <header>
        <nav class="header navbar navbar-expand-md box-shadow">
            <a href="/"><img src="/images/biospex_logo.svg" alt="BIOSPEX"
                             class="my-0 mr-md-auto top-logo font-weight-normal"/></a>
            @include('common.nav')
        </nav>
    </header>
@endsection

@section('content')
    <h2 class="text-center col-6 mx-auto pt-4">
        {{ __('Find your BIOSPEX answers for your frequently asked questions answered here.') }}</h2>
    <hr class="header mx-auto" style="width:300px;">
    <div class="col-12 col-md-10 offset-md-1">
        <div class="card white box-shadow py-5 my-5 p-sm-5">
            @foreach($categories as $category)
                <h2 class="mt-4">{{ $category->name }}</h2>
                <div id="accordion{{ $category->id }}">
                    @foreach($category->faqs as $key => $faq)
                        <div class="faq card">
                            <div class="card-header" id="heading{{ $faq->id }}">
                                <button class="faq btn btn-link text-left p-0" data-toggle="collapse"
                                        data-target="#collapse{{ $faq->id }}" aria-expanded="true"
                                        aria-controls="collapse{{ $faq->id }}">
                                    {{ $faq->question }}
                                </button>
                            </div>

                            <div id="collapse{{ $faq->id }}" class="collapse"
                                 aria-labelledby="heading{{ $faq->id }}"
                                 data-parent="#accordion{{ $category->id }}">
                                <div class="card-body">
                                    {!! $faq->answer !!}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>
    </div>
    <div class="text-center mb-4">
    <h2 class="text-center col-6 pt-4">
        {{ __('Don\'t see your question listed above?') }}</h2>
    <a href="{{ route('contact.get.index') }}" class="btn btn-primary mx-auto">{{ __('CONTACT US') }}</a>
    </div>
@endsection

@section('footer')
    @include('common.footer')
    @include('common.contributors')
@endsection
