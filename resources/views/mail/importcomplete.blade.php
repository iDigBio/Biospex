@component('mail::message')
## @lang('emails.import_subject_complete', ['project' => $project])

@lang('emails.import_dup_rej_message')<br>

@lang('emails.import_ocr_message')<br>

Thanks,<br>
{{ config('app.name') }}
@endcomponent
