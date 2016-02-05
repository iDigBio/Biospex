<?php namespace Biospex\Services\Actor\NotesFromNature2;

use Biospex\Services\Actor\ActorAbstract;
use Biospex\Services\Actor\ActorInterface;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Config\Repository as Config;
use Biospex\Repositories\Contracts\Download;
use League\Csv\Writer;
use Biospex\Services\Xml\XmlBuild;
use Biospex\Services\Manifest\Manifest;
use Biospex\Services\Report\Report;
use Biospex\Repositories\Contracts\Expedition;
use Biospex\Repositories\Contracts\Header;
use Biospex\Repositories\Contracts\Property;

class NotesFromNature2Export extends ActorAbstract implements ActorInterface
{
    protected $actor;
    protected $expeditionId;
    protected $nfnExportDir;
    protected $record;
    protected $folder;
    protected $buildDirectory;
    protected $states;

    /**
     * @var XmlBuild
     */
    protected $xmlBuild;

    /**
     * @var Manifest
     */
    protected $manifest;

    /**
     * @var Report
     */
    protected $report;

    /**
     * @var ExpeditionInterface
     */
    protected $expedition;

    /**
     * @var HeaderInterface
     */
    protected $header;

    /**
     * @var PropertyInterface
     */
    protected $property;

    protected $headers;

    public function __construct(
        Filesystem $filesystem,
        Download $download,
        Config $config,
        XmlBuild $xmlBuild,
        Manifest $manifest,
        Report $report,
        Expedition $expedition,
        Header $header,
        Property $property
    ) {
        $this->filesystem = $filesystem;
        $this->download = $download;
        $this->config = $config;
        $this->xmlBuild = $xmlBuild;
        $this->manifest = $manifest;
        $this->report = $report;
        $this->expedition = $expedition;
        $this->header = $header;
        $this->property = $property;

        $this->scratchDir = $config->get('config.scratch_dir');
        $this->nfnExportDir = $config->get('config.nfn_export_dir');
    }

    public function process($actor)
    {
        $this->createDir($this->nfnExportDir);
        $this->expeditionId = $actor->pivot->expedition_id;

        $this->expedition->cached(true);
        $this->record = $this->expedition->findWith($this->expeditionId, ['project.group', 'subjects']);

        if (empty($this->record)) {
            $this->report->addError(trans('emails.error_process', ['id' => $this->expeditionId]));
            $this->report->reportSimpleError($this->record->project->group->id);

            return;
        }

        $this->folder = "{$actor->id}-" . md5($this->record->title);

        $this->setBuildDirectory();

        $this->buildMetaFile();

        $this->buildCsvFile($this->buildCsvArray());

        $zipFile = $this->zipDarwinCoreArchive();

        $file = $this->nfnExportDir . '/' . $this->folder . '.zip';

        $this->moveFile($zipFile, $file);

        $download = $this->makeDownload($file, $actor);

        $variables = $this->buildManifest($download);

        $this->makeDownload($variables, $actor);

        $this->filesystem->deleteDirectory($this->buildDirectory);

        $actor->pivot->state = $actor->pivot->state + 1;
        $actor->pivot->queued = 0;
        $actor->pivot->completed = 1;
        $actor->pivot->save();

        $this->processComplete($this->record->project->group_id, $this->record->title);

        return;
    }

    /**
     * Set tmp directory used.
     */
    public function setBuildDirectory()
    {
        $this->buildDirectory = $this->scratchDir . '/' . $this->folder;
        $this->createDir($this->buildDirectory);
        $this->writeDir($this->buildDirectory);

        return;
    }

    public function buildManifest($download)
    {
        $vars = [
            'requestType' => 'transcription',
            'subjectType' => 'Herbarium', // TODO Placeholder for NfN to tell us what they need.
            'packageType' => 'expedition'
        ];

        $this->manifest->setVariables($vars);
        $variables = $this->manifest->mapManifestVariables($this->record, $download);

        return $variables;
    }

    public function buildMetaFile()
    {
        $headers = $this->getHeader();
        $headerFields = $this->getHeaderFields($headers);

        $dom = $this->xmlBuild->setDomDocument('1.0', 'UTF-8');
        $child = $this->xmlBuild->buildElementsFromArray($dom, $this->xmlMetaData($headerFields));
        if ($child) {
            $dom->appendChild($child);
        }
        $dom->formatOutput = true; // Add whitespace to make easier to read XML
        $dom->save($this->buildDirectory . '/meta.xml');

        return;
    }

    public function buildCsvArray()
    {
        $header = $this->getHeaderFlipped();

        $csvArray = [];

        foreach ($this->record->subjects as $subject) {
            $subject = $subject->toArray();
            $csvArray['image'][] = array_intersect_key($subject, $header['image']);
            $csvArray['occurrence'][] = array_intersect_key($subject['occurrence'], $header['occurrence']);
        }

        return $csvArray;
    }

    public function buildCsvFile($csvArray)
    {
        $writer = Writer::createFromPath($this->buildDirectory . '/images.csv', 'w');
        if ( ! $writer) {
            throw new \Exception(trans('emails.error_csv_creation', [
                'id'      => $this->expeditionId,
                'message' => 'NfN images.csv'
            ]));
        }
        $writer->insertOne(array_keys($csvArray['image'][0]));
        $writer->insertAll($csvArray['image']);

        $writer = Writer::createFromPath($this->buildDirectory . '/occurrence.csv', 'w');
        if ( ! $writer) {
            throw new \Exception(trans('emails.error_csv_creation', [
                'id'      => $this->expeditionId,
                'message' => 'NfN images.csv'
            ]));
        }
        $writer->insertOne(array_keys($csvArray['occurrence'][0]));
        $writer->insertAll($csvArray['occurrence']);

        return;
    }

    public function zipDarwinCoreArchive()
    {
        $zip = new \ZipArchive();
        $destination = $this->buildDirectory . '/' . $this->folder . '.zip';

        if ($zip->open($destination, true) !== true) {
            throw new \Exception(trans('emails.error_zip_creation', ['id' => $this->expeditionId]));
        }

        $zip->addFile($this->buildDirectory . '/images.csv', 'images.csv');
        $zip->addFile($this->buildDirectory . '/occurrence.csv', 'occurrence.csv');
        $zip->addFile($this->buildDirectory . '/meta.xml', 'meta.xml');
        $zip->close();

        if ( ! file_exists($destination)) {
            throw new \Exception(trans('emails.error_dwc_creation', ['id' => $this->expeditionId]));
        }

        return $destination;
    }

    /**
     * Add download files to downloads table.
     *
     * @param $file
     * @param $actor
     * @param bool $view
     * @return
     */
    public function makeDownload($file, $actor)
    {
        if ( ! is_array($file)) {
            $baseName = pathinfo($file, PATHINFO_BASENAME);
            $data = null;
        } else {
            $baseName = $this->folder . '.json';
            $data = serialize($file);
        }

        $download = $this->createDownload($this->record->id, $actor->id, $baseName, $data);

        return $download;
    }

    private function getHeader() {
        if (empty($this->headers)) {
            $result = $this->header->getByProjectId($this->record->project->id);
            $this->headers = $result->header;
        }

        return $this->headers;
    }

    private function getHeaderFlipped() {
        $header = $this->getHeader();
        $header['image'] = array_flip($header['image']);
        $header['occurrence'] = array_flip($header['occurrence']);

        return $header;
    }

    private function getHeaderFields($header)
    {
        $headerFields = [];
        foreach ($header as $type => $fields) {
            $headerFields[$type] = $this->buildMetaFields($fields);
        }

        return $headerFields;
    }

    private function buildMetaFields($fields)
    {
        $fieldArray = [];
        foreach ($fields as $index => $field) {
            $fieldArray[] = $this->buildFieldArray($index, $field);
        }

        return $fieldArray;
    }

    private function buildFieldArray($index, $field)
    {
        if ($field == 'id' || $field == 'coreid') {
            return [
                'name'       => $field,
                'attributes' => [
                    'index' => $index,
                ]
            ];
        }

        $term = $this->property->findByShort($field);

        return [
            'name'       => 'field',
            'attributes' => [
                'index' => $index,
                'term'  => $term->qualified,
            ]
        ];
    }

    private function xmlMetaData($headerFields)
    {
        $xmlArchive = [
            'name'       => 'archive',
            'attributes' => [
                'xmnls'              => 'http://rs.tdwg.org/dwc/text/',
                'metadata'           => 'eml.xml',
                'xmlns:xsi'          => 'http://www.w3.org/2001/XMLSchema-instance',
                'xsi:schemaLocation' => 'http://rs.tdwg.org/dwc/text/ http://rs.tdwg.org/dwc/text/tdwg_dwc_text.xsd'
            ]
        ];

        $xmlCore[] = array_merge([
            'name'       => 'core', // child under root
            // 'value' => 'Can be some value',
            'attributes' => [
                'encoding'           => 'UTF-8',
                'fieldsTerminatedBy' => ',',
                'linesTerminatedBy'  => '\n',
                'fieldsEnclosedBy'   => '&quot;',
                'ignoreHeaderLines'  => '1',
                'rowType'            => 'http://rs.tdwg.org/dwc/terms/Occurrence'
            ],
            [
                'name' => 'files',
                [
                    'name'  => 'location',
                    'value' => 'occurrences.csv'
                ],
            ],
        ], $headerFields['occurrence']);

        $xmlExtension[] = array_merge([
            'name'       => 'extension',
            'attributes' => [
                'encoding'           => 'UTF-8',
                'fieldsTerminatedBy' => ',',
                'linesTerminatedBy'  => '\n',
                'fieldsEnclosedBy'   => "&quot;",
                'ignoreHeaderLines'  => 1,
                'rowType'            => 'http://rs.gbif.org/terms/1.0/Image'
            ],
            [
                'name' => 'files',
                [
                    'name'  => 'location',
                    'value' => 'images.csv'
                ],
            ],
        ], $headerFields['image']);

        return array_merge($xmlArchive, $xmlCore, $xmlExtension);
    }

    /**
     * Report complete process.
     *
     * @param $group_id
     * @param $title
     */
    protected function processComplete($group_id, $title)
    {
        $this->report->processComplete($group_id, $title);
    }
}


