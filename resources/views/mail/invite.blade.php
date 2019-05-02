@component('mail::message')
# @lang('pages.welcome')

@lang('messages.group_invite_message', ['title' => $title])

@component('mail::button', ['url' => $url])
Join Now
@endcomponent

Thank you,<br>
{{ config('app.name') }}
@endcomponent
