<?php
/**
 * ImportCommand.php
 *
 * @package    Biospex Package
 * @version    1.0
 * @author     Robert Bruhn <79e6ef82@opayq.com>
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

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Maatwebsite\Excel\Excel;
use Biospex\Repo\Import\ImportInterface;
use Biospex\Repo\Subject\SubjectInterface;
use Biospex\Repo\SubjectDoc\SubjectDocInterface;

class SubjectImport extends Command {
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'subject:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Import darwin core files";

    /**
     * Directory where darwin core files are stored
     *
     * @var string
     */
    protected $dataDir;

    /**
     * Tmp directory for extracted files
     * @var string
     */
    protected $dataTmp;

    /**
     * Validation rules
     *
     * @var Array
     */
    protected $rules = array();

    /**
     * Core file from meta.xml file
     *
     * @var
     */
    protected $coreFile;

    /**
     * Extension file from meta.xml file
     * @var
     */
    protected $extFile;

    /**
     * Create a new Import instance.
     *
     * @param  ProjectInterface  $project
     * @return void
     */
    public function __construct(
        ImportInterface $import,
        SubjectInterface $subject,
        SubjectDocInterface $subjectdoc,
        Excel $excel,
        Filesystem $filesystem
    )
    {
        parent::__construct();

        $this->import = $import;
        $this->subject = $subject;
        $this->subjectdoc = $subjectdoc;
        $this->excel = $excel;
        $this->filesystem = $filesystem;
        $this->dataDir = Config::get('config.dataDir');
        $this->dataTmp = Config::get('config.dataTmp');
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
/*    public function fire()
    {
        $imports = $this->import->all();

        foreach ($imports as $import)
        {
            $this->makeTmp();

            $file = $this->dataDir . '/' . $import->file;
            $fileTmp = $this->dataTmp . '/' . $import->file;

            $this->filesystem->move($file, $fileTmp);

            $this->unzip($fileTmp);

            $core = $this->loadCsv('core.txt');
            $extension = $this->loadCsv('occurence.txt');

            $subjects = $this->buildSubjectExtensionArray($core, $extension, $import->project_id);

            $this->insertDocs($subjects);

            $this->destroyTmp();

            echo "Subject imports completed" . PHP_EOL;
        }

    }*/

    public function fire()
    {
        /** temp for testing */
        $import = new stdClass();
        $import->file = 'example.zip';

        $this->makeTmp();

        $file = $this->dataDir . '/' . $import->file;
        $fileTmp = $this->dataTmp . '/' . $import->file;

        $this->filesystem->move($file, $fileTmp);

        $this->unzip($fileTmp);

        $this->setFiles();

        $core = $this->loadCsv($this->coreFile);
        $extension = $this->loadCsv($this->coreFile);

        $subjects = $this->buildSubjectExtensionArray($core, $extension, $import->project_id);

        $this->insertDocs($subjects);

        $this->destroyTmp();

        echo "Subject imports completed" . PHP_EOL;

    }


    /**
     * Set core and ext file from meta.xml
     */
    protected function setFiles()
    {
        $xml = simplexml_load_file($this->dataTmp . '/' . 'meta.xml');
        $this->coreFile = $xml->core->files->location;

        exit;
    }

    /**
     * Build subject array and insert extension
     *
     * @param $core
     * @param $extension
     * @param $projectId
     * @return array
     */
    protected function buildSubjectExtensionArray($core, $extension, $projectId)
    {
        // Set key to coreid for easy search and join to subjects
        $instance = array();
        foreach ($extension as $key => $row)
        {
            $instance[$row['coreid']] = $row;
            unset($extension[$key]);
        }

        $subjects = array();
        foreach ($core as $key => $subject)
        {
            $subjects[$key] = array_merge(array(
                'project_id' => $projectId,
                'extension' => $instance[$subject['id']]
            ), $subject);
        }

        return $subjects;
    }

    /**
     * Insert Doc
     *
     * @param $core
     * @param $import
     */
    protected function insertDocs($subjects)
    {
        foreach ($subjects as $subject)
        {
            if ( ! $this->validateDoc($subject))
                continue;

            $result = $this->subjectdoc->create($subject);

            $this->subject->create(array(
                'mongo_id' => $result['_id'],
                'project_id' => $result['project_id'],
                'object_id' => $result['id']
            ));
        }
    }

    /**
     * Validate if subject exists using project_id and id
     *
     * @param $subject
     * @return bool
     */
    protected function validateDoc($subject)
    {
        $rules = array('project_id' => 'unique_with:subjects,id');
        $values = array('project_id' => $subject['project_id'], 'id' => $subject['id']);

        $validator = Validator::make($values, $rules);
        $validator->getPresenceVerifier()->setConnection('mongodb');

        return $validator->fails() ? false : true;
    }

    /**
     * Load csv file
     *
     * @param $file
     * @return array
     */
    protected function loadCsv($file)
    {
        return $this->excel->load($this->dataTmp . '/' . $file, true)->toArray();
    }

    /**
     * Extract files from zip
     *
     * @param $file
     */
    public function unzip($file)
    {
        $zip = new ZipArchive;
        $res = $zip->open($file);
        if ($res === true) {
            $zip->extractTo($this->dataTmp);
            $zip->close();
        } else {
            echo 'Failed to extract' . PHP_EOL;
        }
    }

    /**
     * Create tmp dataDir
     */
    protected function makeTmp()
    {
        if ( ! $this->filesystem->isDirectory($this->dataTmp))
            $this->filesystem->makeDirectory($this->dataTmp);

        if ( ! $this->filesystem->isWritable($this->dataTmp))
            chmod($this->dataTmp, 0777);
    }

    /**
     * Iterate over tmp dataDir and destroy
     */
    protected function destroyTmp()
    {
        $it = new RecursiveDirectoryIterator($this->dataTmp, RecursiveDirectoryIterator::SKIP_DOTS);
        $files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
        foreach($files as $file) {
            if ($file->getFilename() === '.' || $file->getFilename() === '..') {
                continue;
            }
            if ($file->isDir()){
                rmdir($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }
        rmdir($this->dataTmp);
    }
}