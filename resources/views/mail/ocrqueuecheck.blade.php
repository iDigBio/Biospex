@component('mail::message')
# @lang('messages.ocr_queue_check_title')

{!! $message !!}

Thank you,<br>
{{ config('app.name') }}
@endcomponent
