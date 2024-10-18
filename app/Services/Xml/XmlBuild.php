<?php
/*
 * Copyright (C) 2015  Biospex
 * biospex@gmail.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Services\Xml;

use DOMDocument;

/**
 * Class XmlBuild
 */
class XmlBuild
{
    public function __contstuct() {}

    public function setDomDocument($version, $encoding)
    {
        return new DOMDocument($version, $encoding);
    }

    public function buildElementsFromArray($dom, $data)
    {
        if (empty($data['name'])) {
            return false;
        }

        // Create the element
        $element_value = (! empty($data['value'])) ? $data['value'] : null;
        $element = $dom->createElement($data['name'], $element_value);

        // Add any attributes
        if (! empty($data['attributes']) && is_array($data['attributes'])) {
            foreach ($data['attributes'] as $attribute_key => $attribute_value) {
                $element->setAttribute($attribute_key, $attribute_value);
            }
        }

        // Any other items in the data array should be child elements
        foreach ($data as $data_key => $child_data) {
            if (! is_numeric($data_key)) {
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
