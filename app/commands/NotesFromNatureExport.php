<?php
/**
 * NotesFromNatureExport.php
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
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Contracts\MessageProviderInterface;
use Symfony\Component\Console\Input\InputArgument;
use Biospex\Repo\Expedition\ExpeditionInterface;
use Biospex\Services\Subject\Subject;


class NotesFromNatureExport extends Command {
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'nfn:export';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Exports expedition for Notes From Nature";

    /**
     * Data Directory
     *
     * @var string
     */
    protected $dataDir;

    /**
     * Full path to temp file director
     * @var
     */
    protected $tmpFileDir;

    /**
     * Meta xml from meta file
     * @var
     */
    protected $metaXml = null;

    /**
     * CSV header array associated with meta file
     * @var array
     */
    protected $metaHeader = array();

    /**
     * Remote image column from csv import
     * @var
     */
    protected $remoteImgColumn = null;

    /**
     * Hold original image url
     * @var array
     */
    protected $originalImgUrl = array();

    /**
     * Stores missing urls
     * @var array
     */
    protected $missingImgUrl = array();

    /**
     * Data array for images
     * @var array
     */
    protected $data = array();

    /**
     * Metadata array for images
     * @var array
     */
    protected $metadata = array();

    /**
     * Array to hold subjects and identifiers
     *
     * @var
     */
    protected $subjectArray;

    protected $messages;

    /**
     * Class constructor
     */
    public function __construct(
        Filesystem $filesystem,
        MessageProviderInterface $messages,
        ExpeditionInterface $expedition,
        Subject $subject
    )
    {
        $this->filesystem = $filesystem;
        $this->messages = $messages;
        $this->expedition = $expedition;
        $this->subject = $subject;
        $this->dataDir = Config::get('config.dataDir');
        $this->metaData = Config::get('config.metaData');
        $this->imgTypes = Config::get('config.imgTypes');
        $this->largeWidth = Config::get('config.nfnLrgImageWidth');
        $this->smallWidth = Config::get('config.nfnSmImageWidth');

        parent::__construct();
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return array(
            array('expeditionId', InputArgument::REQUIRED, 'Id of expedition being exported'),
        );
    }

    /**
     * Exceute shell commands
     * @param $cmd
     */
    protected function executeCommand($cmd)
    {
        shell_exec($cmd);

        return;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        $expeditionId = $this->argument('expeditionId');
        $expedition = $this->expedition->findWith($expeditionId, ['subject.subjectDoc']);

        if (empty($expedition))
        {
            $this->messages->add("error", "Expedition with id $expeditionId cannot be found.");
            $this->report();
        }

        $title = preg_replace('/[^a-zA-Z0-9]/', '', $expedition->title);
        $this->tmpFileDir = "{$this->dataDir}/$title";
        $this->createDir($this->tmpFileDir);

        $this->processExpedition($expedition);

        $this->processImages();

        $this->saveFile("{$this->tmpFileDir}/details.js", json_encode($this->metadata, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));

        $this->executeCommand("tar -czf {$this->dataDir}/$title.tar.gz -C {$this->dataDir} $title");

        // TODO create reports and error messaging for export code.
        $this->report();

        return;
    }

    /**
     * Process expedition for export
     * @param $expedition
     */
    protected function processExpedition($expedition)
    {
        $i = 0; // TODO Remove these. Only for example to process 3 images

        foreach ($expedition->subject as $subject)
        {
            $this->subjectArray[$subject->id] = $subject->object_id;
            $remoteImgColumn = $this->getRemoteImgColumn($subject->subjectDoc->meta_id);

            if (empty($subject->subjectDoc->{$remoteImgColumn}))
            {
                $this->missingImgUrl[] = array($subject->object_id);
                continue;
            }

            list($image, $ext) = $this->getImage($subject->subjectDoc->bestQualityAccessURI);
            $this->originalImgUrl[$subject->id.$ext] = $subject->subjectDoc->bestQualityAccessURI;
            $path = "{$this->tmpFileDir}/{$subject->id}{$ext}";
            $this->saveFile($path, $image);

            // TODO Remove these. Only for example to process 3 images
            $i++;
            if ($i % 3 === 0) break;
        }

        return;
    }

    /**
     * Process images for NfN for an expedition
     */
    protected function processImages()
    {
        $data = array();

        $it = new RecursiveDirectoryIterator($this->tmpFileDir, RecursiveDirectoryIterator::SKIP_DOTS);
        $files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);

        $lrgPath = "{$this->tmpFileDir}/large";
        $this->createDir($lrgPath);

        $smPath = "{$this->tmpFileDir}/small";
        $this->createDir($smPath);

        $this->metadata['sourceDir'] = $this->tmpFileDir;
        $this->metadata['targetDir'] = $this->tmpFileDir;
        $this->metadata['created_at'] = date('l jS F Y', time());
        $this->metadata['highResDir'] = $lrgPath;
        $this->metadata['lowResDir'] = $smPath;
        $this->metadata['highResWidth'] = $this->largeWidth;
        $this->metadata['lowResWidth'] = $this->smallWidth;

        $i = 0;
        foreach($files as $file) {
            list($width, $height, $type, $attr) = getimagesize($file->getRealPath()); // $width, $height, $type, $attr
            $info = pathinfo($file->getRealPath()); // $dirname, $basename, $extension, $filename

            $data['identifier'] = $this->subjectArray[$info['filename']];
            $data['original']['path'] = array($info['filename'], ".{$info['extension']}");
            $data['original']['name'] = $info['basename'];
            $data['original']['width'] = $width;
            $data['original']['height'] = $height;

            $lrgHeight = round(($height * $this->largeWidth) / $width);
            $lrgName = "{$info['filename']}.large.png";
            $lrgImgPath = "$lrgPath/$lrgName";
            $data['large']['name'] = "large/$lrgName";
            $data['large']['width'] = $this->largeWidth;
            $data['large']['height'] = $lrgHeight;

            $this->convertImage($file->getRealPath(), $this->largeWidth, $lrgHeight, $lrgImgPath);


            $smallHeight = round(($height * $this->smallWidth) / $width);
            $smName = "{$info['filename']}.small.png";
            $smImgPath = "$smPath/$smName";
            $data['small']['name'] = "small/{$info['filename']}.small.png";
            $data['small']['width'] = $this->smallWidth;
            $data['small']['height'] = $smallHeight;

            $this->convertImage($file->getRealPath(), $this->smallWidth, $smallHeight, $smImgPath);

            $this->metadata['images'][] = $data;

            $i++;
        }
        $this->metadata['total'] = $i * 3;

        return;
    }

    /**
     * Convert image and resize.
     *
     * @param $file
     * @param $width
     * @param $height
     * @param $newImgPath
     */
    protected function convertImage($file, $width, $height, $newImgPath)
    {
        $this->executeCommand("/usr/bin/convert $file -colorspace RGB -resize {$width}x{$height} $newImgPath");

        return;
    }

    /**
     * Retrieve image from url
     *
     * @param $url
     * @return array
     */
    protected function getImage($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        $image = curl_exec($ch);
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        curl_close($ch);

        return array($image, $this->imgTypes[$contentType]);
    }

    /**
     * Retrieve column name for remote image supplied in meta file during import.
     *
     * @param $metaId
     * @return null
     */
    protected function getRemoteImgColumn($metaId)
    {
        if (!is_null($this->remoteImgColumn))
            return $this->remoteImgColumn;

        $this->setMetaData($metaId);

        if ( ! $this->subject->loadDom($this->metaXml, true))
        {
            $this->messages->add("error", "Unable to load dom document using id $metaId.");
            $this->report();
        }

        if ( ! $node = $this->subject->getXpathQuery("//ns:field[contains(@term, '{$this->metaData['remoteImgUrl']}')]"))
        {
            $this->messages->add("error", "Unable to perform xpath query using id $metaId.");
            $this->report();
        }
        $index = $node->attributes->getNamedItem("index")->nodeValue;

        $this->remoteImgColumn = $this->metaHeader[$index];

        return $this->remoteImgColumn;
    }

    /**
     * Set xml and header from meta file
     *
     * @param $metaId
     */
    protected function setMetaData($metaId)
    {
        if (!is_null($this->metaXml))
            return;

        $meta = $this->subject->getMeta($metaId);
        if ( ! $meta)
        {
            $this->messages->add("error", "Unable to get metadata using id $metaId.");
            $this->report();
        }

        $this->metaXml = $meta->xml;
        $this->metaHeader = json_decode($meta->header);

        return;
    }

    /**
     * Save a file to destination path
     *
     * @param $path
     * @param $file
     */
    protected function saveFile($path, $file)
    {
        $this->filesystem->put($path, $file);
    }

    /**
     * Create directory
     */
    protected function createDir($dir)
    {
        if ( ! $this->filesystem->isDirectory($dir))
        {
            if ( ! $this->filesystem->makeDirectory($dir))
            {
                $this->messages->add("error", "Unable to create directory.");
                $this->report();
            }
        }

        if ( ! $this->filesystem->isWritable($dir))
        {
            if ( ! chmod($dir, 0777))
            {
                $this->messages->add("error", "Unable to make directory writable.");
                $this->report();
            }
        }
    }

    /**
     * Iterate over directory and destroy
     */
    protected function destroyDir($dir)
    {
        $it = new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS);
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
        rmdir($dir);
    }

    /**
     * Email report of errors and link to download file
     */
    protected function report()
    {
        dd($this->messages->getMessageBag());
    }
}