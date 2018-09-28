@extends('front.layout.default')

{{-- Web site Title --}}
@section('title')
    {{ __('Resend Activation') }}
@stop

{{-- Content --}}
@section('header')
    <header>
        <nav class="header navbar navbar-expand-md box-shadow" style="background-image: url(/images/banner-binoculars.jpg);">
            <a href="/"><img src="/images/biospex_logo.svg" alt="BIOSPEX"
                             class="my-0 mr-md-auto top-logo font-weight-normal"/></a>
            @include('common.nav')
        </nav>
    </header>
@endsection

@section('content')
    <h2 class="text-center pt-4">{{ __('Resend Activation') }}</h2>
    <hr class="header mx-auto" style="width:300px;">
    <div class="col-12 col-md-10 offset-md-1">
        <div class="card white box-shadow py-5 my-5 p-sm-5">
            <div class="col-6 mx-auto">
                <form action="{{ route('app.post.resend') }}" method="post" role="form" class="form-horizontal">
                    {!! csrf_field() !!}
                    <div class="form-group {{ ($errors->has('email')) ? 'has-error' : '' }}">
                        <label for="email">{{ __('Email') }} <span class="color-action">*</span></label>
                        <input type="text" name="email" id="email" class="form-control" value="{{ old('email') }}"
                               required>
                        {{ $errors->first('email') }}
                    </div>
                    {!! Honeypot::generate('formuser', 'formtime') !!}
                    <div class="text-center">
                        <button type="submit" class="btn btn-primary pl-4 pr-4">{{ __('SUBMIT') }}</button>
                    </div>
                </form>
                <div class="mt-4 text-center">
                    {!! link_to_route('app.get.login', __('Back to Login')) !!}
                </div>
            </div>
        </div>
    </div>
@endsection

@section('footer')
    @include('common.footer')
    @include('common.contributors')
@endsection
