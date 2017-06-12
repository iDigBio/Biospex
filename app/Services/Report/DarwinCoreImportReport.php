<?php

namespace App\Services\Report;

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

        $this->fireReportEvent($email, $subject, $view, $data, $attachments);
    }
}

