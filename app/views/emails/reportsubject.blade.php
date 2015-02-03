<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="utf-8">
</head>
<body>
<h2>{{ trans('emails.import_complete') }}</h2>

<p>{{ trans('emails.import_message') }}:</p>
<p>
    <blockquote>
    {{{ trans('projects.project') }}}: {{{ $projectTitle }}}<br />
    {{{ $importMessage }}}<br />
    </blockquote>
</p>
<p>{{ trans('emails.thank_you') }}, <br />
    ~{{ trans('emails.signature') }}</p>
</body>
</html>