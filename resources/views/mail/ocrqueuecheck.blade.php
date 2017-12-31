@component('mail::message')
# @lang('errors.ocr_queue_check_title')

{!! $message !!}

Thanks,<br>
{{ config('app.name') }}
@endcomponent
