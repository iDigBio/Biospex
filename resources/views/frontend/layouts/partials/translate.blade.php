<ul class="nav navbar-nav">
    <li class="dropdown"><a class="dropdown-toggle" data-toggle="dropdown"
                            href="#">{{ trans('pages.translate') }} <b class="caret"></b></a>
        <ul class="dropdown-menu">
            @foreach (config('supportedLocales') as $key => $lang)
                @include('frontend.layouts.partials.local')
            @endforeach
        </ul>
    </li>
</ul>