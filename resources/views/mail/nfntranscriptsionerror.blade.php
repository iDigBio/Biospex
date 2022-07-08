@component('mail::message')
# {{ t('Error Processing Classifications') }}

{{ t('An error occurred while processing the NfnPanoptes classifications.') }}
{{ t('The Biospex Administration has been notified and will investigate the issue. Please do not attempt to restart or perform other functions on this project.') }}

{{ t('Project') }}: {{ $title }}
{{ t('ID') }}: {{ $id }}
{{ t('Message') }}: {{ $message }}

{{ t('Thank you') }},
{{ config('app.name') }}
@endcomponent
