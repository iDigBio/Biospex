<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="utf-8">
</head>
<body>
<h2>{{ trans('emails.welcome') }}</h2>

<p>{{ trans('emails.group_invite_message', ['group' => $group]) }}</p>
<p><a href="{{ URL::action('UsersController@register', [$code]) }}">{{ URL::action('UsersController@register', ['code' => $code]) }}</a></p>

<p>{{ trans('emails.thank_you') }}, <br />
    ~{{ trans('emails.signature') }}</p>
</body>
</html>