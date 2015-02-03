<?php  namespace Biospex\Services\Report;
/**
 * SubjectImportReport.php
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

class SubjectImportReport extends Report {

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
		$duplicated = $this->createAttachment($duplicates, 'duplicated');
		$rejected = $this->createAttachment($rejects, 'rejected');

		$attachments = [$duplicated, $rejected];

		$data = array(
			'projectTitle' => $title,
			'importMessage' => trans('emails.import_complete_message'),
		);
		$subject = trans('emails.import_complete');
		$view = 'emails.reportsubject';

		$this->fireEvent('user.sendreport', $email, $subject, $view, $data, $attachments);
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
		$subject = trans('errors.error_import');
		$data = array(
			'importId' => $id,
			'projectTitle' => $title,
			'errorMessage' => print_r($this->messages->get('error'), true)
		);
		$view = 'emails.reporterror';

		$this->fireEvent('user.sendreport', $email, $subject, $view, $data);
	}
}