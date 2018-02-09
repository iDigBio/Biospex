@component('mail::message')
## @lang('messages.import_subject_complete', ['project' => $project])

@lang('messages.import_dup_rej_message')<br>

@lang('messages.import_ocr_message')<br>

Thank you,<br>
{{ config('app.name') }}
@endcomponent
