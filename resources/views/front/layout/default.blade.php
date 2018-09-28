<!DOCTYPE html>
<html lang="en">
<head>
    @include('common.head')
    @yield('custom-style')
</head>
<body>
@include('common.notices')
@yield('header')
<div class="container">
    @yield('content')
</div>
@include('common.footer')
@include('common.script')
@yield('custom-script')
</body>
</html>