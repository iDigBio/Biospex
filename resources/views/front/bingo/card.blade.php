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
    <script src="https://kit.fontawesome.com/c840411e54.js" crossorigin="anonymous" SameSite="none Secure"></script>
    <link href="{{ mix('/css/front.css') }}" rel="stylesheet" type="text/css"/>
</head>
<body>
<div class="container">
    <canvas id="bingo-conffeti" class="collapse" style="z-index: -1; position:fixed; top:0;left:0"></canvas>
    <div class="row mb-0">
        <div class="bingo m-auto">
            @include('front.bingo.partials.card-rows', ['project' => $bingo->project, 'rows' => $rows])
        </div>
    </div>
    <div class="row my-0">
        <div class="col-sm-10 mx-auto mt-8">
            <div id="bingodiv" class="d-flex border-info-light" style="width:100%; height: 500px"></div>
        </div>
    </div>
    @include('front.bingo.partials.bingo-modal')
</div>
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
<script>
    let bingoConfetti = new ConfettiGenerator({target: 'bingo-conffeti'});
    bingoConfetti.render();
</script>
</body>
</html>