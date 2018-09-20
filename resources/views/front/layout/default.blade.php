<!DOCTYPE html>
<html lang="en">
@include('common.head')
<body>
@yield('header')

<div class="container">
@yield('content')
</div>
@yield('footer')
@include('common.script')
</body>
</html>