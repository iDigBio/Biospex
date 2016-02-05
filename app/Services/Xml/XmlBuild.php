<?php namespace Biospex\Services\Xml;

class XmlBuild
{
    public function __contstuct()
    {

    }

    public function setDomDocument($version, $encoding)
    {
        return new \DOMDocument($version, $encoding);
    }

    public function buildElementsFromArray($dom, $data)
    {
        if (empty($data['name'])) {
            return false;
        }

        // Create the element
        $element_value = ( ! empty($data['value'])) ? $data['value'] : null;
        $element = $dom->createElement($data['name'], $element_value);

        // Add any attributes
        if ( ! empty($data['attributes']) && is_array($data['attributes'])) {
            foreach ($data['attributes'] as $attribute_key => $attribute_value) {
                $element->setAttribute($attribute_key, $attribute_value);
            }
        }

        // Any other items in the data array should be child elements
        foreach ($data as $data_key => $child_data) {
            if ( ! is_numeric($data_key)) {
                continue;
            }

            $child = $this->buildElementsFromArray($dom, $child_data);
            if ($child) {
                $element->appendChild($child);
            }
        }

        return $element;
    }
}