@component('mail::message')
# {{ $subject }}

{!! $message !!}

@if(!empty($buttons))
@foreach($buttons as $label => $button)
@component('mail::button', ['url' => $button['url'], 'color' => $button['color'] ?? 'primary'])
{{ $label }}
@endcomponent
@endforeach

{{ t('If clicking a button does not work, right click and open in new window.') }}

@endif

{{ t('Thank you') }},

{{ config('app.name') }}
@endcomponent
