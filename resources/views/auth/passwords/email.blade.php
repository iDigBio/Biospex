@extends('layout.default')

{{-- Web site Title --}}
@section('title')
    {{ t('Resend Password') }}
@stop

{{-- Content --}}
@section('content')
    <h2 class="text-center pt-4">{{ t('Send Password Instructions') }}</h2>
    <hr class="header mx-auto" style="width:300px;">
    <div class="col-12 col-md-10 offset-md-1">
        <div class="card white box-shadow py-5 my-5 p-sm-5">
            <div class="col-6 mx-auto">
                <form action="{{ route('app.password.email') }}" method="post" role="form" class="recaptcha">
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
                    @include('partials.recaptcha')
                    @include('partials.submit-button')
                </form>
                @include('partials.back-login', ['route' => route('app.get.login')])
            </div>
        </div>
    </div>
@endsection
