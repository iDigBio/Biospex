@component('mail::message')
# @lang('pages.ocr_queue_check_title')

{!! $message !!}

Thank you,<br>
{{ config('app.name') }}
@endcomponent
