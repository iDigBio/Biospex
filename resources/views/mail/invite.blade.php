@component('mail::message')
# @lang('emails.welcome')

@lang('emails.group_invite_message', ['title' => $title])

@component('mail::button', ['url' => $url])
Join Now
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
