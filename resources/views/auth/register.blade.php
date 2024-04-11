@extends('layout.default')

{{-- Web site Title --}}
@section('title')
    {{ t('Register') }}
@stop

{{-- Content --}}
@section('content')
    <h2 class="text-center pt-4">{{ t('Register Account') }}</h2>
    <hr class="header mx-auto" style="width:300px;">
    <div class="col-12 col-md-10 offset-md-1">
        <div class="card white box-shadow py-5 my-5 p-sm-5">
            <div class="col-6 mx-auto">
                <form action="{{ route('app.post.register') }}" method="post" role="form" class="form-horizontal recaptcha">
                    @csrf
                    <input type="hidden" name="apiuser" value="false">
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
                    <div class="form-group">
                        <label for="password_confirmation" class="col-form-label required">{{ t('Confirm Password') }}:</label>
                        <input type="password" class="form-control @error('password_confirmation') is-invalid @enderror"
                               id="password_confirmation" name="password_confirmation"
                               value="{{ old('password_confirmation') }}" required>
                        @error('password_confirmation')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                    @include('partials.recaptcha')
                    @include('partials.submit-button')
                </form>
                <div class="mt-4 text-center">
                    <a href="{{ route('app.get.login') }}">{{ t('Already have an account? Login') }}</a>
                </div>
            </div>
        </div>
    </div>
@endsection
