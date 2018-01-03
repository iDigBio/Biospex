@component('mail::message')
# @lang('errors.ocr_queue_check_title')

{!! $message !!}

Thank you,<br>
{{ config('app.name') }}
@endcomponent
