@if (Session::has('success'))
<div class="alert alert-success alert-dismissable">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
    @if (is_array(Session::get('success')))
        @foreach (Session::get('success') as $message)
        <p><strong>@lang('pages.success'):</strong> {{ $message }}</p>
        @endforeach
        @else
    <p><strong>@lang('pages.success'):</strong> {{ Session::get('success') }}</p>
    @endif
</div>
{{ Session::forget('success') }}
@endif

@if (Session::has('error'))
 <?php dd(Session::get('error')); ?>
<div class="alert alert-danger alert-dismissable">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
    @if (is_array(Session::get('error')))
        @foreach (Session::get('error') as $message)
        <p><strong>@lang('pages.error'):</strong> {{ $message }}</p>
        @endforeach
    @else
        <p><strong>@lang('pages.error'):</strong> {{ Session::get('error') }}</p>
    @endif
</div>
{{ Session::forget('error') }}
@endif

@if (Session::has('warning'))
<div class="alert alert-warning alert-dismissable">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
    @if (is_array(Session::get('warning')))
        @foreach (Session::get('warning') as $message)
        <p><strong>@lang('pages.warning'):</strong> {{ $message }}</p>
        @endforeach
    @else
    <p><strong>@lang('pages.warning'):</strong> {{ Session::get('warning') }}</p>
    @endif
</div>
{{ Session::forget('warning') }}
@endif

@if (Session::has('info'))
<div class="alert alert-info alert-dismissable">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
    @if (is_array(Session::get('info')))
        @foreach (Session::get('info') as $message)
        <p><strong>@lang('pages.fyi'):</strong> {{ $message }}</p>
        @endforeach
    @else
    <p><strong>@lang('pages.fyi'):</strong> {{ Session::get('info') }}</p>
    @endif
</div>
{{ Session::forget('info') }}
@endif
