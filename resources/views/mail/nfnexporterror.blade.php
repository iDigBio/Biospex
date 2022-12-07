@component('mail::message')
# {{ t('Error Exporting For NfnPanoptes') }}

{{ t('An error occurred while exporting.') }}
{{ t('The Biospex Administration has been notified and will investigate the issue. Please do not attempt to restart export or perform other functions on this project.') }}

{{ t('Expedition') }}: {{ $title }}
{{ t('ID') }}: {{ $id }}
{{ t('Message') }}: {!! $message !!}

{{ t('Thank you') }},
{{ config('app.name') }}
@endcomponent
