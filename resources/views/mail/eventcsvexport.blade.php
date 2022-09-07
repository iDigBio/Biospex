@component('mail::message')
# {{ t('Event Export CSV') }}

{{ t('Your export is completed. If a report was generated, you may click the download button to download the file. If no button is included, it is due to no records being located for the export. Some records require overnight processing before they are available.') }}
{{ t('If you believe this is an error, please contact the Administration.') }}

@isset($url)
@component('mail::button', ['url' => $url])
{{ t('Download Event CSV') }}
@endcomponent
{{ t('If clicking button does not work, right click and open in new window.') }}
@endisset

{{ t('Thank you') }},
{{ config('app.name') }}
@endcomponent
