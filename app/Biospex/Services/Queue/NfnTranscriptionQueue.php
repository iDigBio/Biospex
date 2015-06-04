<?php  namespace Biospex\Services\Queue;
/**
 * NfnResultsService.php.php
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

use Illuminate\Support\Facades\Config;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Validator;
use Biospex\Repo\Import\ImportInterface;
use Biospex\Repo\Transcription\TranscriptionInterface;
use Biospex\Repo\Subject\SubjectInterface;
use Biospex\Services\Report\TranscriptionImportReport;
use Maatwebsite\Excel\Excel;
use Rhumsaa\Uuid\Console\Exception;

class NfnTranscriptionQueue extends QueueAbstract {

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var ImportInterface
     */
    protected $import;

    /**
     * @var TranscriptionInterface
     */
    protected $transcription;

    /**
     * @var TranscriptionImportReport
     */
    protected $report;

    /**
     * CSV array for collection unprocessed transcriptions.
     *
     * @var array
     */
    protected $csv = [];

    /**
     * @var Excel
     */
    protected $excel;

    /**
     * Directory where transcriptions files are stored.
     *
     * @var string
     */
    protected $transcriptionImportDir;

    /**
     * Constructor
     *
     * @param Filesystem $filesystem
     * @param ImportInterface $import
     * @param TranscriptionInterface $transcription
     * @param SubjectInterface $subject
     * @param TranscriptionImportReport $report
     * @param Excel $excel
     */
    public function __construct(
        Filesystem $filesystem,
        ImportInterface $import,
        TranscriptionInterface $transcription,
        SubjectInterface $subject,
        TranscriptionImportReport $report,
        Excel $excel
    )
    {
        $this->filesystem = $filesystem;
        $this->import = $import;
        $this->transcription = $transcription;
        $this->subject = $subject;
        $this->report = $report;
        $this->excel = $excel;

        $this->transcriptionImportDir = Config::get('config.transcriptionImportDir');
    }

    /**
     * Fire method
     * @param $job
     * @param $data
     */
    public function fire($job, $data)
    {
        $this->job = $job;
        $this->data = $data;

        $import = $this->import->findWith($this->data['id'], ['project', 'user']);
        $file = $this->transcriptionImportDir . '/' . $import->file;

        try
        {
            $data = $this->excel->load($file)->get()->toArray();
            $header = [];
            foreach($data as $key => $row)
            {
                list($header, $row) = $this->createHeader($header, $key, $row);
                if ($key == 0)
                    continue;

                $combined = array_combine($header, $row);

                // Check if fsu collection and search subjects by file name
                $subject = $this->getSubject($combined);

                if ( ! $subject)
                {
                    $this->csv[] = $combined;
                    continue;
                }

                $addArray = ['project_id' => (string) $subject->project_id, 'expedition_ids' => $subject->expedition_ids];
                $combined = $addArray + $combined;

                if ($this->validate($combined))
                    continue;

                $this->transcription->create($combined);
            }

            $this->report->complete($import->user->email, $import->project->title, $this->csv);
            $this->filesystem->delete($file);
            $this->import->destroy($import->id);
        }
        catch (Exception $e)
        {
            $import->error = 1;
            $import->save();
            $this->report->addError(trans('emails.error_import_process',
                ['id' => $import->id, 'message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]
            ));
            $this->report->error($import->id, $import->user->email, $import->project->title);

            return;
        }

        $this->delete();

        return;
    }

    /**
     * Validate transcription to prevent duplicates.
     *
     * @param $combined
     * @return mixed
     */
    public function validate($combined)
    {
        $rules = ['nfn_id' => 'unique:transcriptions'];
        $values = ['nfn_id' => $combined['nfn_id']];
        $validator = Validator::make($values, $rules);
        $validator->getPresenceVerifier()->setConnection('mongodb');

        // returns true if failed.
        $fail = $validator->fails();

        if ($fail)
            $this->csv[] = $combined;

        return $fail;
    }

    /**
     * Build header row.
     *
     * @param $header
     * @param $key
     * @param $row
     * @return array
     */
    public function createHeader(&$header, $key, $row)
    {
        if ($key != 0)
            return [$header, $row];

        $row[0] = 'nfn_id';
        $header = $row;
        $header = array_replace($header, array_fill_keys(array_keys($header, 'created_at'), 'create_date'));

        return [$header, $row];
    }

    /**
     * @param $combined
     * @return mixed
     */
    public function getSubject($combined)
    {
        if ($this->checkCollection($combined))
        {
            $filename = strtok(trim($combined['filename']), '.');
            $subject = $this->subject->findByFilename($filename);
        }
        else
        {
            $subject = $this->subject->find(trim($combined['subject_id']));
        }

        return $subject;
    }

    /**
     * Check if FSU collection.
     *
     * @param $combined
     * @return bool
     */
    public function checkCollection($combined)
    {
        return strtolower(trim($combined['collection'])) == 'fsu';
    }
}
