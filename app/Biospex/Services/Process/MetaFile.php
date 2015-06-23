<?php  namespace Biospex\Services\Process;

/**
 * Class MetaFile
 * @package Biospex\Services\Process
 */
class MetaFile {

    private $coreType;
    private $coreFile;
    private $coreDelimiter;
    private $coreEnclosure;
    private $extDelimiter;
    private $extEnclosure;
    private $mediaIsCore;
    private $extensionFile;
    private $coreXpathQuery;
    private $extXpathQuery;
    private $metaFields;

    /**
     * Constructor
     *
     * @param Xml $xml
     */
    public function __construct(Xml $xml)
    {
        $this->xml = $xml;
    }

    /**
     * Process meta file.
     *
     * @param $dir
     * @return string
     * @throws \Exception
     */
    public function process ($dir)
    {
        $xml = $this->xml->load($dir . '/meta.xml');

        $this->setCoreType();
        $this->setCoreFile();
        $this->setMediaIsCore();
        $this->setExtensionFile();
        $this->setCoreCsvSettings();
        $this->setExtensionCsvSettings();
        $this->setCoreXpathQuery();
        $this->setExtXpathQuery();
        $this->setCoreMetaFields();
        $this->setExtMetaFields();

        return $xml;
    }

    /**
     * Set core type.
     *
     * @throws \Exception
     */
    private function setCoreType()
    {
        $this->coreType = $this->xml->getDomTagAttribute('core', 'rowType');
        if (empty($this->coreType))
            throw new \Exception(trans('emails.error_core_type'));

        return;
    }

    /**
     * Set core file.
     *
     * @throws \Exception
     */
    private function setCoreFile()
    {
        $this->coreFile = $this->xml->getElementByTag('core');
        if (empty($this->coreFile))
            throw new \Exception(trans('emails.error_core_file_missing'));

        return;
    }

    /**
     * Set csv settings for core file.
     *
     * @throws \Exception
     */
    private function setCoreCsvSettings()
    {
        $delimiter = $this->xml->getDomTagAttribute('core', 'fieldsTerminatedBy');
        $this->coreDelimiter = ($delimiter == "\\t") ? "\t" : $delimiter;
        $this->coreEnclosure = $this->xml->getDomTagAttribute('core', 'fieldsEnclosedBy');

        if (empty($this->coreDelimiter))
            throw new \Exception(trans('emails.error_csv_core_delimiter'));
    }

    /**
     * Set csv settings for extension file.
     *
     * @throws \Exception
     */
    private function setExtensionCsvSettings()
    {
        $delimiter = $this->xml->getDomTagAttribute('extension', 'fieldsTerminatedBy');
        $this->extDelimiter = ($delimiter == "\\t") ? "\t" : $delimiter;
        $this->extEnclosure = $this->xml->getDomTagAttribute('extension', 'fieldsEnclosedBy');

        if (empty($this->extDelimiter))
            throw new \Exception(trans('emails.error_csv_ext_delimiter'));
    }

    /**
     * Set if multimedia is the core.
     */
    private function setMediaIsCore()
    {
        $this->mediaIsCore = preg_match('/occurrence/i', $this->coreType) ? false : true;
    }

    /**
     * Set extension file.
     */
    private function setExtensionFile ()
    {
        $extension = $this->mediaIsCore ? 'occurrence' : 'multimedia';

        $query = "//ns:archive//ns:extension[contains(php:functionString('strtolower', @rowType), '$extension')]";
        $result = $this->xml->xpathQuery($query, true);

        $this->extensionFile = empty($result->nodeValue) ? false : $result->nodeValue;

        return;
    }

    /**
     * Set core xpath query.
     */
    private function setCoreXpathQuery()
    {
        $this->coreXpathQuery = $this->mediaIsCore ?
            "//ns:archive/ns:core[contains(php:functionString('strtolower', @rowType), 'multimedia')]/ns:field" :
            "//ns:archive/ns:core[contains(php:functionString('strtolower', @rowType), 'occurrence')]/ns:field";

        return;
    }

    /**
     * Set extension xpath query.
     */
    private function setExtXpathQuery()
    {
        if ( ! $this->extensionFile)
            return;

        $this->extXpathQuery = $this->mediaIsCore ?
            "//ns:archive/ns:extension[contains(php:functionString('strtolower', @rowType), 'occurrence')]/ns:field" :
            "//ns:archive/ns:extension[contains(php:functionString('strtolower', @rowType), 'multimedia')]/ns:field";

        return;
    }

    /**
     * Set core meta fields.
     */
    private function setCoreMetaFields()
    {
        $this->metaFields['core'][0] = 'id';
        foreach ($this->xml->xpathQuery($this->coreXpathQuery) as $child)
        {
            $index = $child->attributes->getNamedItem("index")->nodeValue;
            $qualified = $child->attributes->getNamedItem("term")->nodeValue;
            $this->metaFields['core'][$index] = $qualified;
        }

        return;
    }

    /**
     * Set extension meta fields.
     */
    private function setExtMetaFields()
    {
        if ( ! $this->extensionFile)
            return;

        $this->metaFields['extension'][0] = 'id';
        foreach ($this->xml->xpathQuery($this->extXpathQuery) as $child)
        {
            $index = $child->attributes->getNamedItem("index")->nodeValue;
            $qualified = $child->attributes->getNamedItem("term")->nodeValue;
            $this->metaFields['extension'][$index] = $qualified;
        }

        return;
    }

    /**
     * @return mixed
     */
    public function getCoreType()
    {
        return $this->coreType;
    }

    /**
     * @return mixed
     */
    public function getCoreFile()
    {
        return $this->coreFile;
    }

    /**
     * @return mixed
     */
    public function getCoreDelimiter()
    {
        return $this->coreDelimiter;
    }

    /**
     * @return mixed
     */
    public function getCoreEnclosure()
    {
        return $this->coreEnclosure;
    }

    /**
     * @return mixed
     */
    public function getExtensionFile()
    {
        return $this->extensionFile;
    }

    /**
     * @return mixed
     */
    public function getExtDelimiter()
    {
        return $this->extDelimiter;
    }

    /**
     * @return mixed
     */
    public function getExtEnclosure()
    {
        return $this->extEnclosure;
    }

    /**
     * @return mixed
     */
    public function getMediaIsCore()
    {
        return $this->mediaIsCore;
    }

    /**
     * @param null $type
     * @return mixed
     */
    public function getMetaFields($type = null)
    {
        return is_null($type) ? $this->metaFields : $this->metaFields[$type];
    }

}
