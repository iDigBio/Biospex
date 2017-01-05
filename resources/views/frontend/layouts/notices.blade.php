@if (null !== $notices)
    <div class="alert alert-warning alert-dismissable">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        @foreach ($notices as $notice)
            <p><strong>@lang('pages.warning'):</strong> {!! $notice->message !!}</p>
        @endforeach
    </div>
@endif

@if ($messages = Session::get('success'))
<div class="alert alert-success alert-dismissable">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
    @if (is_array($messages))
        @foreach ($messages as $message)
        <p><strong>@lang('pages.success'):</strong> {!! $message !!}</p>
        @endforeach
    @else
    <p><strong>@lang('pages.success'):</strong> {!! $messages !!}</p>
    @endif
</div>
{{ Session::forget('status') }}
@endif

@if ($messages = Session::get('status'))
    <div class="alert alert-success alert-dismissable">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        @if (is_array($messages))
            @foreach ($messages as $message)
                <p><strong>@lang('pages.success'):</strong> {!! $message !!}</p>
            @endforeach
        @else
            <p><strong>@lang('pages.success'):</strong> {!! $messages !!}</p>
        @endif
    </div>
    {{ Session::forget('status') }}
@endif

@if ($messages = Session::get('error'))
<div class="alert alert-danger alert-dismissable">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
    @if (is_array($messages))
        @foreach ($messages as $message)
        <p><strong>@lang('pages.error'):</strong> {!! $message !!}</p>
        @endforeach
    @else
        <p><strong>@lang('pages.error'):</strong> {{ $messages }}</p>
    @endif
</div>
{{ Session::forget('error') }}
@endif

@if ($messages = Session::get('warning'))
<div class="alert alert-warning alert-dismissable">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
    @if (is_array($messages))
        @foreach ($messages as $message)
        <p><strong>@lang('pages.warning'):</strong> {!! $message !!}</p>
        @endforeach
    @else
    <p><strong>@lang('pages.warning'):</strong> {{ $messages }}</p>
    @endif
</div>
{{ Session::forget('warning') }}
@endif

@if ($messages = Session::get('info'))
<div class="alert alert-info alert-dismissable">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
    @if (is_array($messages))
        @foreach ($messages as $message)
        <p><strong>@lang('pages.fyi'):</strong> {!! $message !!}</p>
        @endforeach
    @else
    <p><strong>@lang('pages.fyi'):</strong> {{ $messages }}</p>
    @endif
</div>
{{ Session::forget('info') }}
@endif
