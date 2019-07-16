@component('mail::message')
## {{ __('messages.import_subject_complete', ['project' => $project]) }}

{{ __('messages.import_dup_rej_message') }}<br>

{{ __('messages.import_ocr_message') }}<br>

Thank you,<br>
{{ config('app.name') }}
@endcomponent
