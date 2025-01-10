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

namespace App\Services\Helpers;

use App\Models\PanoptesTranscription;
use App\Models\PusherTranscription;

/**
 * Class TranscriptionMapService
 */
class TranscriptionMapService
{
    /**
     * TranscriptionMapService construct
     */
    public function __construct(protected array $reservedEncoded, protected array $mappedTranscriptionFields) {}

    /**
     * Map transcription fields that are varied in database.
     *
     * @return mixed|string
     */
    public function mapTranscriptionField(
        string $type,
        PanoptesTranscription $panoptesTranscription,
        ?PusherTranscription $pusherTranscription = null
    ): mixed {
        foreach ($this->mappedTranscriptionFields[$type] as $value) {
            $encodedValue = $this->decodeTranscriptionField($value);
            if (isset($panoptesTranscription->{$encodedValue})) {
                return $panoptesTranscription->{$encodedValue};
            }
        }

        if ($pusherTranscription === null) {
            return '';
        }

        return $type === 'taxon' ? $pusherTranscription->taxon : $pusherTranscription->transcriptionContent[$type];
    }

    /**
     * Encode transcription an reconcile fields.
     */
    public function encodeTranscriptionField(string $field): string
    {
        if (str_contains($field, 'subject_') || in_array($field, $this->reservedEncoded)) {
            return $field;
        }

        return $this->base64UrlEncode($field);
    }

    /**
     * Base encode string.
     */
    public function base64UrlEncode(string $bin): string
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($bin));
    }

    /**
     * Decode transcription an reconcile fields.
     */
    public function decodeTranscriptionField(string $field): string
    {
        if (str_contains($field, 'subject_') || in_array($field, $this->reservedEncoded)) {
            return $field;
        }

        return $this->base64UrlDecode($field);
    }

    /**
     * Base decode string.
     */
    public function base64UrlDecode(string $str): string
    {
        return base64_decode(str_replace(['-', '_'], ['+', '/'], $str));
    }
}
