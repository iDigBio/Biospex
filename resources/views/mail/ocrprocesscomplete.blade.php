@component('mail::message')
## {{ $title }}

@lang('pages.ocr_complete_message')<br>

Thank you,<br>
{{ config('app.name') }}
@endcomponent