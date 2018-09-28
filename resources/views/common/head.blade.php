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
@include('common.style')
<style>
    .carousel-inner {
        width: 500px;
        height: 500px;
        background-color: #ffffff;
        border: 2px solid #8cc640;
        background-size: cover;
        border-radius: 50%;
        margin: 0 auto;
        left: 0;
        right: 0;
    }
    @media only screen and (max-width: 768px) {
        .carousel-inner {
            overflow: hidden;
            width: 400px;
            height: 400px;
        }
    }

    .carousel-item {
        height: 100%;
    }

    #externalIndicators > li {
        width: 70px;
        height: 70px;
        font-size: 30px;
        text-align-all: center;
        color: #9BA2AB;
        border: 2px solid #8cc640;
        border-radius: 50%;
        background-color: #ffffff;
        box-shadow: 0 0 10px rgba(0, 0, 0, .25);
        padding-top: 8px;
    }

    #externalIndicators > li:hover {
        background-color: #8cc640;
        color: #ffffff;
        cursor: pointer;
    }

    #externalIndicators .active {
        background-color: #8cc640;
        color: #ffffff;
    }

    .smallertext{
        padding-top: 5px;
        font-size: 14px;
        color: #9BA2AB;
    }

    .jumbotron .container {
        max-width: 70rem;
    }

    li.nav-btn {
        background-color: transparent;
        border: 1px solid #dc3545;
        color: white;
        -webkit-transition: .25s ease-out;
        -moz-transition: .25s ease-out;
        -ms-transition: .25s ease-out;
        -o-transition: .25s ease-out;
        transition: .25s ease-out;
        cursor: pointer;
    }

    li.nav-btn:hover {
        background-color: #e83f29;
        color: white;
    }

    li.nav-btn.show {
        background-color: #bd2130;
        border-color: #b21f2d;
    }

    @media only screen and (max-width:768px) {
        li.nav-btn {
            margin: 8px auto;
            left: 0;
            right: 0;
            width: 200px;
            display: block;
        }

        li.nav-btn:hover {
            background-color: #e83f29; /*red -- link or call to action color */
            border-color: #e83f29;
            color: white;
        }
    }


</style>