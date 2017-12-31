<!DOCTYPE html>
<html lang="en">

@section('htmlheader')
    @include('frontend.layouts.partials.htmlheader')
@show

<body>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 text-center col-md-offset-2">
            <img src="{{ asset('images/logo.png') }}">
        </div>
    </div>
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="text-center">
                <a class="btn btn-default" href="{{ route('home') }}">Biospex</a>
                <a class="btn btn-default" href="https://biospex.docs.apiary.io/#">API Docs</a>
                <a class="btn btn-default" href="https://github.com/iDigBio/Biospex">GitHub</a>
                @apiuser
                    <a class="btn btn-danger" rel="nofollow" data-method="delete" href="{{ route('api.get.logout') }}">Sign Out</a>
                @else
                    <a class="btn btn-success" href="{{ route('api.get.login') }}">Sign In</a>
                    <a class="btn btn-primary" rel="nofollow" data-method="delete" href="{{ route('api.get.register') }}">Register</a>
                @endapiuser
            </div>
        </div>
    </div>
    @yield('content')
</div>
@include('frontend.layouts.footer')

@section('scripts')
    @include('frontend.layouts.partials.scripts')
@show
</body>
</html>
