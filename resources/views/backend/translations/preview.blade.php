<!DOCTYPE html>
<html lang="en">

@section('htmlheader')
    @include('frontend.layouts.partials.htmlheader')
@show

<body class="{{ Route::currentRouteName() }}">
@include('frontend.layouts.navigation')
<div class="container-fluid">
    {!! html_entity_decode($translation->value) !!}
</div>
@include('frontend.layouts.footer')
<!-- ./ footer -->

@section('scripts')
    @include('frontend.layouts.partials.scripts')
@show

</body>
</html>