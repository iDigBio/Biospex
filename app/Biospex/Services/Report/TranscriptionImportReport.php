<?php  namespace Biospex\Services\Report;

/**
 * NfnTranscriptionImportReport.php
 *
 * @package    Biospex Package
 * @version    1.0
 * @author     Robert Bruhn <bruhnrp@gmail.com>
 * @license    GNU General Public License, version 3
 * @copyright  (c) 2014, Biospex
 * @link       http://biospex.org
 *
 * This file is part of Biospex.
 * Biospex is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Biospex is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Biospex.  If not, see <http://www.gnu.org/licenses/>.
 */

class TranscriptionImportReport extends Report {

    /**
     * Send transcription report.
     *
     * @param $email
     * @param $title
     * @param $csv
     */
    public function complete($email, $title, $csv)
    {
        $attachments = ! empty($csv) ? $this->createAttachment($csv, 'duplicates') : [];
        $data = [
            'importMessage' => trans('emails.import_transcription_complete', ['project' => $title]),
            'csvMessage' => trans('emails.import_dup_rej_message'),
            'ocrImportMessage' => '',
        ];
        $subject = trans('emails.import_transcription_subject');
        $view = 'emails.report-import';

        $this->fireEvent('user.sendreport', $email, $subject, $view, $data, $attachments);

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
            'importId' => $id,
            'projectTitle' => $title,
            'errorMessage' => print_r($this->messages->get('error'), true)
        ];
        $view = 'emails.reporterror';

        $this->fireEvent('user.sendreport', $email, $subject, $view, $data);
    }
}