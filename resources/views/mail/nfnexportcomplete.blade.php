@component('mail::message')
    # {{ t('Zooniverse Export Completed') }}

    {{ t('The export process for "%s" has been completed successfully. If a download file was created during this process, you may access the link on the Expedition view page. If there were errors, an attachment will be included with this email.', $title) }}

    {{ t('Thank you') }},
    {{ config('app.name') }}
@endcomponent
