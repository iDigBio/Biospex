@component('mail::message')
# {{ $subject }}

{!! $message !!}

@isset($url)
@component('mail::button', ['url' => $url])
{{ t('Download File') }}
@endcomponent
{{ t('If clicking button does not work, right click and open in new window.') }}
@endisset

{{ t('Thank you') }},
{{ config('app.name') }}
@endcomponent
