<?php  namespace App\Services\Queue;

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

use App\Repositories\Contracts\Import;
use App\Services\Report\TranscriptionImportReport;
use App\Services\Process\NfnTranscription;

class NfnTranscriptionQueue extends QueueAbstract
{
    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var Import
     */
    protected $import;

    /**
     * @var NfnTranscription
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
     * Directory where transcriptions files are stored.
     *
     * @var string
     */
    protected $transcriptionImportDir;

    /**
     * Constructor.
     *
     * @param Filesystem $filesystem
     * @param Import $import
     * @param TranscriptionImportReport $report
     * @param NfnTranscription $transcription
     */
    public function __construct(
        \Filesystem $filesystem,
        Import $import,
        TranscriptionImportReport $report,
        NfnTranscription $transcription
    ) {
        $this->filesystem = $filesystem;
        $this->import = $import;
        $this->report = $report;
        $this->transcription = $transcription;

        $this->transcriptionImportDir = \Config::get('config.transcription_import_dir');
        if (! $this->filesystem->isDirectory($this->transcriptionImportDir)) {
            $this->filesystem->makeDirectory($this->transcriptionImportDir);
        }
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

        try {
            $csv = $this->transcription->process($file);
            $this->report->complete($import->user->email, $import->project->title, $csv);
            $this->filesystem->delete($file);
            $this->import->destroy($import->id);
        } catch (\Exception $e) {
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
}
