@component('mail::message')
# {{ t('An error occurred while importing the Darwin Core Archive.') }}

{{ t('Project') }}: {{ $title }}

{{ t('ID') }}: {{ $id }}

{!! $message !!}

{{ t('The Biospex Administration has been notified and will investigate the issue. Please do not attempt to restart or perform other functions on this project.') }}

{{ t('Thank you') }},
{{ config('app.name') }}
@endcomponent
