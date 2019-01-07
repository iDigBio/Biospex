@extends('front.layout.default')

{{-- Web site Title --}}
@section('title')
    {{ __('Register Account') }}
@stop

{{-- Content --}}
@section('header')
    <header style="background-image: url(/storage/images/page-banners/banner-field.jpg);">
        <nav class="header navbar navbar-expand-md box-shadow">
            <a href="/"><img src="/storage/images/biospex_logo.svg" alt="BIOSPEX"
                             class="my-0 mr-md-auto top-logo font-weight-normal"/></a>
            @include('common.nav')
        </nav>
    </header>
@endsection

@section('content')
    <h2 class="text-center pt-4">{{ __('Register Account') }}</h2>
    <hr class="header mx-auto" style="width:300px;">
    <div class="col-12 col-md-10 offset-md-1">
        <div class="card white box-shadow py-5 my-5 p-sm-5">
            <div class="col-6 mx-auto">
                <form action="{{ route('app.post.register') }}" method="post" role="form" class="form-horizontal">
                    {!! csrf_field() !!}
                    <div class="form-group {{ ($errors->has('first_name')) ? 'has-error' : '' }}">
                        <label for="first_name">{{ __('First Name') }} <span class="color-action">*</span></label>
                        <input type="text" name="first_name" id="first_name" class="form-control" value="{{ old('first_name') }}"
                               required>
                        {{ $errors->first('first_name') }}
                        </div>
                        <div class="form-group {{ ($errors->has('last_name')) ? 'has-error' : '' }}">
                            <label for="last_name">{{ __('Last Name') }} <span class="color-action">*</span></label>
                            <input type="text" name="last_name" id="last_name" class="form-control" value="{{ old('last_name') }}"
                                   required>
                            {{ $errors->first('last_name') }}
                        </div>
                    <div class="form-group {{ ($errors->has('email')) ? 'has-error' : '' }}">
                        <label for="email">{{ __('Email') }} <span class="color-action">*</span></label>
                        <input type="text" name="email" id="email" class="form-control" value="{{ old('email') }}"
                               required>
                        {{ $errors->first('email') }}
                    </div>
                    <div class="form-group {{ ($errors->has('password')) ? 'has-error' : '' }}">
                        <label for="password">{{ __('Password') }} <span class="color-action">*</span></label>
                        <input type="password" name="password" id="password" class="form-control" required>
                        {{ ($errors->has('password') ?  $errors->first('password') : '') }}
                    </div>
                    <div class="form-group {{ ($errors->has('password_confirmation')) ? 'has-error' : '' }}">
                        <label for="password_confirmation">{{ __('Password Confirmation') }} <span class="color-action">*</span></label>
                        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required>
                        {{ ($errors->has('password_confirmation') ?  $errors->first('password_confirmation') : '') }}
                    </div>
                    <div class="form-group {{ ($errors->has('invite')) ? 'has-error' : '' }}">
                        <label for="invite">{{ __('Invite Code') }}</label>
                        <input type="text" name="invite" id="invite" class="form-control">
                        {{ ($errors->has('invite') ?  $errors->first('invite') : '') }}
                    </div>
                    {!! Honeypot::generate('formuser', 'formtime') !!}
                    {!! Form::hidden('apiuser', 0) !!}
                    <div class="text-center">
                        <button type="submit" class="btn btn-primary pl-4 pr-4">{{ __('SUBMIT') }}</button>
                    </div>
                </form>
                <div class="mt-4 text-center">
                    {!! link_to_route('app.get.login', __('Already have an Account? Login')) !!}
                </div>
            </div>
        </div>
    </div>
@endsection

@section('footer')
    @include('common.footer')
    @include('common.contributors')
@endsection
