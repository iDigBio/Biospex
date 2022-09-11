@component('mail::message')
# {{ t('A new project implementing the NfnPanoptes workflow has been submitted to Biospex.') }}


{{ $contact }}

{{ $email }}

{{ $title }}

{{ $description }}

{{ t('Thank you') }},
{{ config('app.name') }}
@endcomponent
