@component('mail::message')
## {{ __('pages.import_subject_complete', ['project' => $project]) }}

{{ __('pages.import_dup_rej_message') }}<br>

{{ __('pages.import_ocr_message') }}<br>

Thank you,<br>
{{ config('app.name') }}
@endcomponent
