@component('mail::message')
# {{ t('RAPID Update Completed') }}

{{ t('The Rapid update has been completed. You will receive another email when the exported version file is completed.') }}

{{ t('File Name') }}: {{ $fileName }}

{{ t('Rows Updated') }}: {{ $recordsUpdated }}

{{ t('Updated Fields ') }}: {{ $fields }}


@if(!empty($downloadUrl))
{{ t('There were errors while updating some of the Rapid Records. Click the button below to download the columns not updated.') }}
@component('mail::button', ['url' => $downloadUrl])
{{ t('Download') }}
@endcomponent
@endif

{{ t('Thank you') }},
{{ config('app.name') }}
@endcomponent
