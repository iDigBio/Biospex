@component('mail::message')
# {{ t('A new Expedition implementing the Zooniverse workflow has been submitted to Biospex.') }}


{{ $contact }}
{{ $email }}

{{ $projectTitle }}

{{ $expeditionTitle }}
{{ $expeditionDescription }}

{{ t('Thank you') }},
{{ config('app.name') }}
@endcomponent
