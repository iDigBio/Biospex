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
 * Class TranscriptionMapHelper
 *
 * @package App\Services\Helpers
 */
class TranscriptionMapHelper
{
    private array $reserved_encoded;

    /**
     * @param array $reserved_encoded
     */
    public function __construct(array $reserved_encoded)
    {
        $this->reserved_encoded = $reserved_encoded;
    }

    /**
     * Map state province for pusher classifications using transcript if it exists.
     *
     * @param \App\Models\PanoptesTranscription $panoptesTranscription
     * @param \App\Models\PusherTranscription|null $pusherTranscription
     * @return mixed
     */
    public function setStateProvince(PanoptesTranscription $panoptesTranscription, PusherTranscription $pusherTranscription = null): mixed
    {
        foreach ($this->reserved_encoded['state-province'] as $value) {
            if (isset($panoptesTranscription->{$value})) {
                return $panoptesTranscription->{$value};
            }
        }

        if ($pusherTranscription === null) {
            return '';
        }

        return $pusherTranscription->transcriptionContent['province'];
    }

    /**
     * Map collected by for pusher classifications using transcript if it exists.
     * @param \App\Models\PanoptesTranscription $panoptesTranscription
     * @param \App\Models\PusherTranscription|null $pusherTranscription
     * @return mixed|string
     */
    public function setCollectedBy(PanoptesTranscription $panoptesTranscription, PusherTranscription $pusherTranscription = null): mixed
    {
        foreach ($this->reserved_encoded['collected-by'] as $value) {
            if (isset($panoptesTranscription->{$value})) {
                return $panoptesTranscription->{$value};
            }
        }

        if ($pusherTranscription === null) {
            return '';
        }

        return $pusherTranscription->transcriptionContent['collector'];
    }

    /**
     * Map scientific name for pusher classifications using transcript if it exists.
     * @param \App\Models\PanoptesTranscription $panoptesTranscription
     * @param \App\Models\PusherTranscription|null $pusherTranscription
     * @return mixed|string
     */
    public function setScientificName(PanoptesTranscription $panoptesTranscription, PusherTranscription $pusherTranscription = null): mixed
    {
        foreach ($this->reserved_encoded['scientific-name'] as $value) {
            if (isset($panoptesTranscription->{$value})) {
                return $panoptesTranscription->{$value};
            }
        }

        if ($pusherTranscription === null) {
            return '';
        }

        return $pusherTranscription->taxon;
    }

    /**
     * Encode transcription an reconcile fields.
     *
     * @param string $field
     * @return string
     */
    public function encodeTranscriptionField(string $field): string
    {
        if (str_contains($field, 'subject_') ||
            in_array($field, $this->reserved_encoded) ||
            in_array($field, $this->reserved_encoded['state-province']) ||
            in_array($field, $this->reserved_encoded['collected-by']) ||
            in_array($field, $this->reserved_encoded['scientific-name'])
        ) {
            return $field;
        }

        return $this->base64UrlEncode($field);
    }

    /**
     * Base encode string.
     *
     * @param string $bin
     * @return string
     */
    public function base64UrlEncode(string $bin): string
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($bin));
    }

    /**
     * Decode transcription an reconcile fields.
     *
     * @param string $field
     * @return string
     */
    public function decodeTranscriptionField(string $field): string
    {
        if (str_contains($field, 'subject_') ||
            in_array($field, $this->reserved_encoded) ||
            in_array($field, $this->reserved_encoded['state-province']) ||
            in_array($field, $this->reserved_encoded['collected-by']) ||
            in_array($field, $this->reserved_encoded['scientific-name'])
        ) {
            return $field;
        }

        return $this->base64UrlDecode($field);
    }

    /**
     * Base decode string.
     *
     * @param string $str
     * @return string
     */
    public function base64UrlDecode(string $str): string
    {
        return base64_decode(str_replace(['-', '_'], ['+', '/'], $str));
    }
}