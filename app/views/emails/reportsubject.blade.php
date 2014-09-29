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
    {{{ trans('emails.duplicates') }}}: {{{ $duplicateCount }}}<br />
    {{{ trans('emails.rejected') }}}: {{{ $rejectedCount }}}
    </blockquote>
</p>
<p>{{ trans('emails.explain_duplicates') }}</p>
<p>{{ trans('emails.explain_rejected') }}</p>
<p>{{ trans('emails.thank_you') }}, <br />
    ~{{ trans('emails.signature') }}</p>
</body>
</html>