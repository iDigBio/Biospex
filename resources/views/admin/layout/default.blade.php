<!DOCTYPE html>
<html lang="en">
@include('common.head')
<body>
<header>
    <nav class="header-admin navbar navbar-expand-md box-shadow">
        <a href="/"><img src="/images/biospex_logo.svg" alt="BIOSPEX"
                         class="my-0 mr-md-auto top-logo font-weight-normal"/></a>
        @include('common.nav')
    </nav>
</header>
@yield('content')
@include('common.admin-footer')
@include('common.script')
</body>
</html>