@if (null !== $notices)
    <div class="alert alert-warning alert-dismissable text-center" style="margin-bottom: 0;">
        <button type="button" class="close" data-dismiss="alert" >&times;</button>
        @foreach ($notices as $notice)
            <p><strong>{{ t('Warning') }}: </strong> {!! $notice->message !!}</p>
        @endforeach
    </div>
@endif