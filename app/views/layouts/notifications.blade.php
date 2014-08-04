@if (Session::has('success'))
<div class="alert alert-success alert-dismissable">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
    @foreach (Session::get('success') as $message)
    <p><strong>@lang('pages.success'):</strong> {{ $message }}</p>
    @endforeach
</div>
{{ Session::forget('success') }}
@endif

@if (Session::has('error'))
<div class="alert alert-danger alert-dismissable">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
    @foreach (Session::get('error') as $message)
    <p><strong>@lang('pages.error'):</strong> {{ $message }}</p>
    @endforeach
</div>
{{ Session::forget('error') }}
@endif

@if (Session::has('warning'))
<div class="alert alert-warning alert-dismissable">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
    @foreach (Session::get('warning') as $message)
    <p><strong>@lang('pages.warning'):</strong> {{ $message }}</p>
    @endforeach
</div>
{{ Session::forget('warning') }}
@endif

@if (Session::has('info'))
<div class="alert alert-info alert-dismissable">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
    @foreach (Session::get('info') as $message)
    <p><strong>@lang('pages.fyi'):</strong> {{ $message }}</p>
    @endforeach
</div>
{{ Session::forget('info') }}
@endif
