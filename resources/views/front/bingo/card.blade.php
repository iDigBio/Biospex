<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="{{ _('pages.default_header') }}">
    <meta name="csrf-param" content="_token">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="google-site-verification" content="DRVQlYZQo5OkUlUhNG8Re-CgYEB7ELA0I_3qJJlzb0U"/>
    <title>
        {{ _('BIOSPEX') }} | {{ __('pages.bingo') }}
    </title>
    @include('common.favicon')
    <link href="https://fonts.googleapis.com/css?family=Raleway:400,700|Work+Sans:400,700" rel="stylesheet">
    <script src="https://kit.fontawesome.com/c840411e54.js" crossorigin="anonymous"></script>
    <link href="{{ mix('/css/front.css') }}" rel="stylesheet" type="text/css"/>
</head>
<body>
<div id="bingodiv" style="height: 100%;"></div>
@include('common.php-vars-javascript')
<script src="{{ mix('/js/manifest.js') }}"></script>
<script src="{{ mix('/js/vendor.js') }}"></script>
<script src="{{ mix('/js/front.js') }}"></script>
<script src="//www.amcharts.com/lib/4/core.js"></script>
<script src="//www.amcharts.com/lib/4/charts.js"></script>
<script src="//www.amcharts.com/lib/4/themes/animated.js"></script>
<script src="//www.amcharts.com/lib/4/maps.js"></script>
<script src="https://www.amcharts.com/lib/4/geodata/worldLow.js"></script>
<script src="{{ asset('js/amChartBingo.min.js')}}"></script>
</body>
</html>