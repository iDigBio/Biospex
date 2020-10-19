@component('mail::message')
# {{ t('RAPID Update Completed') }}

{{ t('File Name') }}: {{ $fileName }}

{{ t('Rows Updated') }}: {{ $recordsUpdated }}

{{ t('Update Fields ') }}: {{ $fields }}


@if(!empty($downloadUrl))
{{ t('There were errors while updating some of the Rapid Records. Click the button below to download the columns not updated.') }}
@component('mail::button', ['url' => $downloadUrl])
{{ t('Download') }}
@endcomponent
@endif

{{ t('Thank you') }},
{{ config('app.name') }}
@endcomponent
