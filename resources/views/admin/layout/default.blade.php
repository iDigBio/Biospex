<!DOCTYPE html>
<html lang="en">
<head>
    @include('common.head')
    @yield('custom-style')
</head>
<body>
<header>
    <nav class="header-admin navbar navbar-expand-md box-shadow">
        <a href="/"><img src="/images/biospex_logo.svg" alt="BIOSPEX"
                         class="my-0 mr-md-auto top-logo font-weight-normal"/></a>
        @include('common.nav')
    </nav>
</header>
<div class="container mb-4">
    @yield('content')
</div>
@include('common.admin-footer')
@include('common.script')
@yield('custom-script')
</body>
</html>