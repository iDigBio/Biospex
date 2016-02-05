<?php namespace Biospex\Services\Report;


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
        $view = 'front.emails.report-import';

        $this->fireEvent($email, $subject, $view, $data, $attachments);

        return;
    }

    /**
     * Send error during transcription import
     *
     * @param $id
     * @param $email
     * @param $title
     */
    public function error($id, $email, $title)
    {
        $subject = trans('emails.error_import');
        $data = [
            'importId'     => $id,
            'projectTitle' => $title,
            'errorMessage' => print_r($this->messages->get('error'), true)
        ];
        $view = 'front.emails.reporterror';

        $this->fireEvent($email, $subject, $view, $data);
    }
}
