<!DOCTYPE html>
<html lang="en">
@include('layout.head')
<body>
<header>
    <nav class="header-admin navbar navbar-expand-md box-shadow">
        <a href="/"><img src="/images/page/biospex_logo.svg" alt="BIOSPEX"
                         class="my-0 mr-md-auto top-logo-admin font-weight-normal"/></a>
        @include('partials.nav')
    </nav>
</header>
<div class="container mb-4">
    @yield('content')
</div>
@include('layout.foot')
</body>
</html>