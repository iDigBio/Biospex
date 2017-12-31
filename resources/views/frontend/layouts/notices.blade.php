@if (null !== $notices)
    <div class="alert alert-warning alert-dismissable">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        @foreach ($notices as $notice)
            <p><strong>@lang('pages.warning'):</strong> {!! $notice->message !!}</p>
        @endforeach
    </div>
@endif