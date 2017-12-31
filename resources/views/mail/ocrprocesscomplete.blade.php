@component('mail::message')
## {{ $title }}

@lang('emails.ocr_complete_message')<br>

Thanks,<br>
{{ config('app.name') }}
@endcomponent