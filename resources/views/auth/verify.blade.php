@extends('layout.default')

{{-- Web site Title --}}
@section('title')
    {{ t('Verify') }}
@stop

{{-- Content --}}
@section('content')
    <h2 class="text-center pt-4">{{ t('Verify Your Email Address') }}</h2>
    <hr class="header mx-auto" style="width:300px;">
    <div class="col-12 col-md-10 offset-md-1">
        <div class="card white box-shadow py-5 my-5 p-sm-5">
            <div class="col-8 mx-auto">
                @if (session('resent'))
                    <div class="alert alert-success" role="alert">
                        {{ t('A fresh verification link has been sent to your email address.') }}
                    </div>
                @endif

                {{ t('Before proceeding, please check your email for a verification link.') }}
                {{ t('If you did not receive the email') }}, <a href="{{ route('verification.resend') }}">{{ t('click here to request another') }}</a>.
            </div>
        </div>
    </div>
@endsection
