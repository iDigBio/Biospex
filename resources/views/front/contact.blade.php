@extends('front.layout.default')

{{-- Web site Title --}}
@section('title')
    {{ __('pages.contact') }}
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
    <h2 class="text-center pt-4 text-uppercase">{{ __('pages.biospex') }} {{ __('pages.contact') }}</h2>
    <hr class="header mx-auto" style="width:300px;">
    <div class="col-12 col-md-10 offset-md-1">
        <div class="jumbotron box-shadow py-5 my-5 p-sm-5">
            <div class="col-8 mx-auto">
                <form action="{{ route('front.contact.create') }}" method="post" role="form">
                    {!! csrf_field() !!}
                    <div class="form-group">
                        <label for="name" class="col-form-label required">{{ __('pages.name') }}:</label>
                        <input type="text" class="form-control {{ ($errors->has('name')) ? 'is-invalid' : '' }}"
                               id="name" name="name"
                               value="{{ old('name') }}" required>
                        <span class="invalid-feedback">{{ $errors->first('name') }}</span>
                    </div>
                    <div class="form-group">
                        <label for="email" class="col-form-label required">{{ __('pages.email') }}:</label>
                        <input type="email" class="form-control {{ ($errors->has('email')) ? 'is-invalid' : '' }}"
                               id="email" name="email"
                               value="{{ old('email') }}" required>
                        <span class="invalid-feedback">{{ $errors->first('email') }}</span>
                    </div>
                    <div class="form-group">
                        <label for="message" class="col-form-label required">{{ __('pages.message') }}:</label>
                        <textarea rows="6" class="form-control {{ ($errors->has('message')) ? 'is-invalid' : '' }}"
                                  id="message" name="message" required>{{ old('message') }}</textarea>
                        <span class="invalid-feedback">{{ $errors->first('message') }}</span>
                    </div>
                    @include('common.recaptcha')
                    @include('common.cancel-submit-buttons')
                </form>
            </div>
        </div>
    </div>
@endsection
