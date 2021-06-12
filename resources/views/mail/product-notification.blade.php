@component('mail::message')
# {{ t('RAPID Product DWC Completed') }}
{{ t('The RAPID Product DWC you requested is complete. You may download the file by clicking the button below. You must be logged in to access the download.') }}

 @component('mail::button', ['url' => $downloadUrl])
 {{ t('Download') }}
 @endcomponent

 {{ t('Thank you') }},
 {{ config('app.name') }}
@endcomponent
