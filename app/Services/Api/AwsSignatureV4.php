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

namespace App\Services\Api;

use DateTime;
use JetBrains\PhpStorm\ArrayShape;

/**
 * Class AwsSignatureV4
 *
 * @package App\Services\Api
 */
class AwsSignatureV4
{

    protected array $config;
    protected string $terminationString = 'aws4_request';
    protected string $algorithm = 'AWS4-HMAC-SHA256';
    protected string $phpAlgorithm = 'sha256';
    protected string $signedHeaders = 'content-type;host;x-amz-date';
    protected string $reqDate;
    protected string $reqDateTime;
    protected string $kSigning;
    protected string $canonicalHeadersStr;
    protected string $requestHashedPayload;
    protected string $requestHashedCanonicalRequest;
    protected string $credentialScopeStr;
    protected string $signature;
    protected string $authorizationHeaderStr;
    protected array $requestHeaders;

    /**
     * Set config values.
     *
     * $config[
     * 'host' => 'lambda.us-east-2.amazonaws.com',
     * 'uri' => '/2015-03-31/functions/imageExportProcess/invocations',
     * 'queryString' => '',
     * 'accessKey' => 'aws_key',
     * 'secretKey' => 'aws_secret',
     * 'region' => 'aws_region',
     * 'service' => 'lambda',
     * 'httpRequestMethod' => 'GET|POST|PUT|PATCH',
     * 'data' => '{}',
     * 'debug' => 'true|false'
     *
     * @param array $config
     * @return void
     */
    public function setConfig(array $config)
    {
        $this->config = $config;
    }

    /**
     * Create signature for requested configuration.
     */
    public function createAwsSignature()
    {
        $this->setDate();
        $this->createSigningKey();
        $this->createCanonicalHeaders();
        $this->createRequestPayload();
        $this->createCanonicalRequest();
        $this->createScope();
        $this->createSignature();
        $this->createAuthorizationHeader();
        $this->setRequestHeaders();
    }

    /**
     * Get request headers.
     *
     * @return array
     */
    public function getRequestHeaders(): array
    {
        return $this->requestHeaders;
    }

    /**
     * Return result url.
     *
     * @return string
     */
    public function getRequestUrl(): string
    {
        return 'https://' . $this->config['host'] . $this->config['uri'];
    }

    /**
     * Set date for signing.
     *
     * @return void
     */
    private function setDate()
    {
        $currentDateTime = new DateTime('UTC');
        $this->reqDate = $currentDateTime->format('Ymd');
        $this->reqDateTime = $currentDateTime->format('Ymd\THis\Z');
    }

    /**
     * Create signing key.
     *
     * @return void
     */
    private function createSigningKey()
    {
        $kSecret = $this->config['secretKey'];
        $kDate = hash_hmac($this->phpAlgorithm, $this->reqDate, 'AWS4'.$kSecret, true);
        $kRegion = hash_hmac($this->phpAlgorithm, $this->config['region'], $kDate, true);
        $kService = hash_hmac($this->phpAlgorithm, $this->config['service'], $kRegion, true);
        $this->kSigning = hash_hmac($this->phpAlgorithm, $this->terminationString, $kService, true);
    }

    /**
     * Create canonical headers.
     *
     * @return void
     */
    private function createCanonicalHeaders()
    {
        $canonicalHeaders = [];
        $canonicalHeaders[] = 'content-type:application/json';
        $canonicalHeaders[] = 'host:' . $this->config['host'];
        $canonicalHeaders[] = 'x-amz-date:' . $this->reqDateTime;
        $this->canonicalHeadersStr = implode("\n", $canonicalHeaders);
    }

    /**
     * Create request payload.
     *
     * @return void
     */
    private function createRequestPayload()
    {
        $this->requestHashedPayload = hash($this->phpAlgorithm, $this->config['data']);
    }

    /**
     * Create canonical request
     *
     * @return void
     */
    private function createCanonicalRequest()
    {
        $canonicalRequest = [];
        $canonicalRequest[] = $this->config['httpRequestMethod'];
        $canonicalRequest[] = $this->config['uri'];
        $canonicalRequest[] = $this->config['queryString'];
        $canonicalRequest[] = $this->canonicalHeadersStr."\n";
        $canonicalRequest[] = $this->signedHeaders;
        $canonicalRequest[] = $this->requestHashedPayload;
        $requestCanonicalRequest = implode("\n", $canonicalRequest);
        $this->requestHashedCanonicalRequest = hash($this->phpAlgorithm, utf8_encode($requestCanonicalRequest));

        if ($this->config['debug']) {
            echo "Canonical to string" . PHP_EOL;
            echo PHP_EOL;
            echo $requestCanonicalRequest . PHP_EOL;
            echo PHP_EOL;
        }
    }

    /**
     * Create scope.
     *
     * @return void
     */
    private function createScope()
    {
        $credentialScope = [];
        $credentialScope[] = $this->reqDate;
        $credentialScope[] = $this->config['region'];
        $credentialScope[] = $this->config['service'];
        $credentialScope[] = $this->terminationString;
        $this->credentialScopeStr = implode('/', $credentialScope);
    }

    /**
     * Create string for signing and create signature.
     *
     * @return void
     */
    private function createSignature()
    {
        // Create string to signing
        $stringToSign = [];
        $stringToSign[] = $this->algorithm;
        $stringToSign[] = $this->reqDateTime;
        $stringToSign[] = $this->credentialScopeStr;
        $stringToSign[] = $this->requestHashedCanonicalRequest;
        $stringToSignStr = implode("\n", $stringToSign);

        if ($this->config['debug']) {
            echo "String to Sign" . PHP_EOL;
            echo PHP_EOL;
            echo $stringToSignStr . PHP_EOL;
            echo PHP_EOL;
        }

        // Create signature
        $this->signature = hash_hmac($this->phpAlgorithm, $stringToSignStr, $this->kSigning);
    }

    /**
     * Create authorization header.
     *
     * @return void
     */
    private function createAuthorizationHeader()
    {
        $authorizationHeader = [];
        $authorizationHeader[] = 'Credential='.$this->config['accessKey'].'/'.$this->credentialScopeStr;
        $authorizationHeader[] = 'SignedHeaders='.$this->signedHeaders;
        $authorizationHeader[] = 'Signature='.($this->signature);
        $this->authorizationHeaderStr = $this->algorithm.' '.implode(', ', $authorizationHeader);
    }

    /**
     * Create request headers.
     */
    private function setRequestHeaders()
    {
        $this->requestHeaders = [
            'Authorization' => $this->authorizationHeaderStr,
            'Content-length' => strlen($this->config['data']),
            'Content-Type' => 'application/json',
            'Cost' => $this->config['host'],
            'X-amz-date' => $this->reqDateTime,
            'X-Amz-Invocation-Type' => 'Event'
        ];
    }
}