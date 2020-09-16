@component('mail::message')
# {{ t('RAPID Export Completed') }}
{{ t('The RAPID Export is complete and may be downloaded by clicking the button below. You must be logged in to access the download.') }}

 @component('mail::button', ['url' => $downloadUrl])
 {{ t('Download') }}
 @endcomponent

 {{ t('Thank you') }},
 {{ config('app.name') }}
@endcomponent
