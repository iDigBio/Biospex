<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="{{ _('FSU Department of Biological Science') }}">
    <meta name="csrf-param" content="_token">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="google-site-verification" content="DRVQlYZQo5OkUlUhNG8Re-CgYEB7ELA0I_3qJJlzb0U"/>
    <title>
        {{ _('BIOSPEX') }} | @yield('title')
    </title>
    @include('common.favicon')
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Raleway:700|Work+Sans">
    <script src="https://kit.fontawesome.com/c840411e54.js" crossorigin="anonymous"></script>
    <link href="{{ mix('/css/admin.css', '/admin') }}" rel="stylesheet" type="text/css"/>
    @yield('custom-style')
</head>
