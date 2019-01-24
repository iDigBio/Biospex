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
            <div class="col-8 mx-auto">
                <form action="{{ route('front.contact.create') }}" method="post" role="form">
                    {!! csrf_field() !!}
                    <div class="form-group {{ ($errors->has('name')) ? 'has-error' : '' }}">
                        <label for="name" class="col-form-label required">{{ __('Name:') }}</label>
                            <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
                            {{ $errors->first('name') }}
                    </div>

                    <div class="form-group {{ ($errors->has('email')) ? 'has-error' : '' }}">
                        <label for="email" class="col-form-label required">{{ __('Email:') }}</label>
                        <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}" required>
                        {{ $errors->first('email') }}
                    </div>

                    <div class="form-group {{ ($errors->has('message')) ? 'has-error' : '' }}">
                        <label for="message" class="col-form-label required">{{ __('Message:') }}</label>
                        <textarea rows="6" name="message" id="message" class="form-control"
                                  required>{{ old('message') }}</textarea>
                        {{ $errors->first('message') }}
                    </div>
                    <div class="form-group text-center">
                        <button type="submit" class="btn btn-primary pl-4 pr-4">{{ __('SUBMIT') }}</button>
                        {!! Honeypot::generate('formuser', 'formtime') !!}
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
