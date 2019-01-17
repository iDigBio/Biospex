@extends('front.layout.default')

{{-- Web site Title --}}
@section('title')
    {{ __('Contact') }}
@stop


@section('header')
    <header id="header-img" style="background-image: url(/storage/images/page-banners/banner-contact-smile.jpg);">
        <nav class="header navbar navbar-expand-md box-shadow">
            <a href="/"><img src="/storage/images/biospex_logo.svg" alt="BIOSPEX"
                             class="my-0 mr-md-auto top-logo font-weight-normal"/></a>
            @include('common.nav')
        </nav>
    </header>
@endsection

@section('content')
    <h2 class="text-center pt-4">{{ __('Contact BIOSPEX') }}</h2>
    <hr class="header mx-auto" style="width:300px;">
    <div class="col-12 col-md-10 offset-md-1">
        <div class="card white box-shadow py-5 my-5 p-sm-5">
            <div class="col-6 mx-auto">
                <form action="{{ route('front.contact.create') }}" method="post" role="form" class="form-horizontal">
                    {!! csrf_field() !!}
                    <div class="form-group">
                        <label for="name">{{ __('Name') }} <span class="color-action">*</span></label>
                        <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}"
                               required>
                        {{ $errors->first('name') }}
                    </div>
                    <div class="form-group">
                        <label for="email" class="mb-0">{{ __('Email') }} <span class="color-action">*</span></label>
                        <input type="text" name="email" id="email" class="form-control" value="{{ old('email') }}"
                               required>
                        {{ $errors->first('email') }}
                    </div>
                    <div class="form-group">
                        <label for="message" class="mb-0">{{ __('Message') }} <span
                                    class="color-action">*</span></label>
                        <textarea rows="6" name="message" id="message" class="form-control"
                                  required>{{ old('message') }}</textarea>
                        {{ $errors->first('message') }}
                    </div>
                    <div class="text-center">
                        <button type="submit" class="btn btn-primary pl-4 pr-4">{{ __('SUBMIT') }}</button>
                        {!! Honeypot::generate('formuser', 'formtime') !!}
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('footer')
    @include('common.footer')
    @include('common.contributors')
@endsection