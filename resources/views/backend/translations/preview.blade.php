<!DOCTYPE html>
<html lang="en">

@section('htmlheader')
    @include('front.layouts.partials.htmlheader')
@show

<body class="{{ Route::currentRouteName() }}">
@include('front.layouts.navigation')
<div class="container-fluid">
    {!! html_entity_decode($translation->value) !!}
</div>
@include('front.layouts.footer')
<!-- ./ footer -->

@section('scripts')
    @include('front.layouts.partials.scripts')
@show

</body>
</html>