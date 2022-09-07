@component('mail::message')
# {{ $file }}

{!! $message !!}

@isset($url)
@component('mail::button', ['url' => $url])
{{ t('Download Report CSV') }}
@endcomponent
{{ t('If clicking button does not work, right click and open in new window.') }}
@endisset

{{ t('Thank you') }},
{{ config('app.name') }}
@endcomponent
