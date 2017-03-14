<?php namespace App\Services\Report;


class TranscriptionImportReport extends Report
{
    /**
     * Send transcription report.
     *
     * @param $email
     * @param $title
     * @param $csv
     */
    public function complete($email, $title, $csv)
    {
        $attachments = ! empty($csv) ? $this->createAttachment($csv, 'rejected') : [];
        $data = [
            'importMessage'    => trans('emails.import_transcription_complete', ['project' => $title]),
            'csvMessage'       => trans('emails.import_dup_rej_message'),
            'ocrImportMessage' => '',
        ];
        $subject = trans('emails.import_transcription_subject');
        $view = 'frontend.emails.report-import';

        $this->fireReportEvent($email, $subject, $view, $data, $attachments);

    }
}
