@component('mail::message')
# {{ t('Zooniverse Export Completed') }}

{{ t('The export process for "%s" has been completed successfully.', $title) }}
{{ t('If a download file was created during this process, you may access the link on the Expedition view page.) }}

@isset($url)
{{ t('Errors detected during export can be downloaded by clicking the button below.') }}
@component('mail::button', ['url' => $url])
{{ t('Download Export Errors') }}
@endcomponent
@endisset

{{ t('Thank you') }},
{{ config('app.name') }}
@endcomponent
