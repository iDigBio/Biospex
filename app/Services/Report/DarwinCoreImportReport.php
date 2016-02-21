<?php namespace App\Services\Report;

class DarwinCoreImportReport extends Report
{
    /**
     * Send report for completed subject import
     *
     * @param $email
     * @param $title
     * @param $duplicates
     * @param $rejects
     */
    public function complete($email, $title, $duplicates, $rejects)
    {
        $duplicated = ! empty($duplicates) ? $this->createAttachment($duplicates, 'duplicated') : [];
        $rejected = ! empty($rejects) ? $this->createAttachment($rejects, 'rejected') : [];

        $attachments = array_merge($duplicated, $rejected);

        $data = [
            'importMessage'    => trans('emails.import_subject_complete', ['project' => $title]),
            'csvMessage'       => trans('emails.import_dup_rej_message'),
            'ocrImportMessage' => trans('emails.import_ocr_message'),
        ];
        $subject = trans('emails.import_subject_subject');
        $view = 'frontend.emails.report-import';

        $this->fireEvent($email, $subject, $view, $data, $attachments);
    }

    /**
     * Send error during subject import
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
        $view = 'frontend.emails.reporterror';

        $this->fireEvent($email, $subject, $view, $data);
    }
}

