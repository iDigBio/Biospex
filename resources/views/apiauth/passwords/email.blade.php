@extends('front.layout.default')

{{-- Web site Title --}}
@section('title')
    {{ __('Resend Password') }}
@stop

{{-- Content --}}
@section('header')
    <header style="background-image: url(/storage/images/page-banners/banner-field.jpg);">
        <nav class="header-admin navbar navbar-expand-md box-shadow">
            <a href="/"><img src="/storage/images/biospex_logo.svg" alt="BIOSPEX"
                             class="my-0 mr-md-auto top-logo-admin font-weight-normal"/></a>
            @include('common.nav')
        </nav>
    </header>
@endsection

@section('content')
    <h2 class="text-center pt-4">{{ __('Send API Password Instructions') }}</h2>
    <hr class="header mx-auto" style="width:300px;">
    <div class="col-12 col-md-10 offset-md-1">
        <div class="card white box-shadow py-5 my-5 p-sm-5">
            <div class="col-6 mx-auto">
                <form action="{{ route('api.password.email') }}" method="post" role="form">
                    {!! csrf_field() !!}
                    <div class="form-group">
                        <label for="email" class="col-form-label required">{{ __('Email') }}:</label>
                        <input type="email" class="form-control {{ ($errors->has('email')) ? 'is-invalid' : '' }}"
                               id="email" name="email"
                               value="{{ old('email') }}" required>
                        <span class="invalid-feedback">{{ $errors->first('email') }}</span>
                    </div>
                    @include('common.recaptcha')
                    <div class="form group text-center">
                        <button type="submit" class="btn btn-primary pl-4 pr-4">{{ __('SUBMIT') }}</button>
                    </div>
                </form>
                <div class="mt-4 text-center">
                    <a href="{{ route('api.get.login') }}">{{ __('Back to Login') }}</a>
                </div>
            </div>
        </div>
    </div>
</div>
@stop