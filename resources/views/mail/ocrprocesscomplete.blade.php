@component('mail::message')
## {{ $title }}

@lang('emails.ocr_complete_message')<br>

Thank you,<br>
{{ config('app.name') }}
@endcomponent