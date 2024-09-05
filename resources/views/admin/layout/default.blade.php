<!DOCTYPE html>
<html lang="en">
@include('admin.layout.head')
<body>
<header>
    <nav class="header-admin navbar navbar-expand-md box-shadow">
        <a href="/"><img src="/images/page/biospex_logo.svg" alt="BIOSPEX"
                         class="my-0 mr-md-auto top-logo-admin font-weight-normal"/></a>
        @include('common.nav')
    </nav>
</header>
<div class="container mb-4">
    @include('common.notices')
    @yield('content')
    @include('common.wedigbio-progress-modal')
    @include('common.wedigbio-rate-modal')
    @include('common.process-modal')
    @include('common.modal')
</div>
@include('admin.layout.foot')
</body>
</html>