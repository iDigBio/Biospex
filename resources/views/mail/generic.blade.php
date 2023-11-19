@component('mail::message')
# {{ $subject }}

{!! $message !!}

@isset($buttons)
@foreach($buttons as $button)
@component('mail::button', ['url' => $button['url'], 'color' => $button['color'] ?? 'primary'])
{{ $button['label'] }}
@endcomponent
@endforeach

{{ t('If clicking a button does not work, right click and open in new window.') }}

@endisset

{{ t('Thank you') }},
{{ config('app.name') }}
@endcomponent
