@extends('layout.default')

{{-- Web site Title --}}
@section('title')
    {{ t('Login') }}
@stop

{{-- Content --}}
@section('content')
    <h2 class="text-center pt-4 text-uppercase">{{ t('Biospex Rapid Login') }}</h2>
    <hr class="header mx-auto" style="width:300px;">
    <div class="col-12 col-md-10 offset-md-1">
        <div class="card white box-shadow py-5 my-5 p-sm-5">
            <div class="col-6 mx-auto">
                <form action="{{ route('app.post.login') }}" method="post" role="form">
                    @csrf
                    <div class="form-group">
                        <label for="email" class="col-form-label required">{{ t('Email') }}:</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror"
                               id="email" name="email"
                               value="{{ old('email') }}" required>
                        @error('email')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="password" class="col-form-label required">{{ t('Password') }}:</label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror"
                               id="password" name="password"
                               value="{{ old('password') }}" required>
                        @error('password')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="remember" name="remember">
                        <label class="custom-control-label" for="remember">{{ t('Remember Me') }}</label>
                    </div>
                    @include('partials.recaptcha')
                    @include('partials.submit-button')
                </form>
                <div class="mt-4 text-center">
                    <a href="{{ route('app.password.request') }}">{{ t('Forgot your Password?') }}</a> ||
                    <a href="{{ route('app.get.register') }}">{{ t('Register') }}</a>
                </div>
            </div>
        </div>
    </div>
@endsection
