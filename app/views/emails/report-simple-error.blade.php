<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="utf-8">
</head>
<body>
<h2>{{ trans('errors.error') }}</h2>

<p>
<blockquote>
    {{ trans('errors.error_message') }}:<br />
    {{{ $errorMessage }}}
</blockquote>
</p>
<p>{{ trans('emails.thank_you') }}, <br />
    ~{{ trans('emails.signature') }}</p>
</body>
</html>