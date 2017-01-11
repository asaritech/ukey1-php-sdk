<?php

/** 
 * The MIT License
 *
 * Copyright 2017 Asari Technologies Ltd.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace Ukey1\ApiClient;

use Ukey1\App;
use Ukey1\Exceptions\EndpointException;
use GuzzleHttp;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\TransferException;

/**
 * An instance of API request
 * 
 * @package Ukey1
 * @author  Zdenek Hofler <developers@asaritech.com>
 */
class Request
{
    /**
     * GET method
     */
    const GET = "GET";
    
    /**
     * POST method
     */
    const POST = "POST";
    
    /**
     * Request timeout
     */
    const TIMEOUT = 10;
    
    /**
     * User-Agent prefix
     */
    const USER_AGENT = "ukey1-php-sdk/";
    
    /**
     * Request method
     *
     * @var string
     */
    private $method;
    
    /**
     * API host
     *
     * @var string 
     */
    private $host;
    
    /**
     * API version
     *
     * @var string
     */
    private $version;
    
    /**
     * Endpoint
     *
     * @var string
     */
    private $endpoint;
    
    /**
     * Your App ID
     *
     * @var string
     */
    private $appId;
    
    /**
     * Your secret key
     * 
     * @var string
     */
    private $secretKey;
    
    /**
     * User's access token
     *
     * @var string
     */
    private $accessToken;
    
    /**
     * An instance of \GuzzleHttp\Client
     *
     * @var \GuzzleHttp\Client
     */
    private $httpClient;
    
    /**
     * Creates an instance of a API request
     * 
     * @param string $method Request method
     */
    public function __construct($method = self::GET)
    {
        $this->method = $method;
    }
    
    /**
     * Sets an API host
     * 
     * @param string $host Host
     * 
     * @return \Ukey1\ApiClient\Request
     */
    public function setHost($host)
    {
        $this->host = $host;
        return $this;
    }
    
    /**
     * Sets an API version
     * 
     * @param string $version Version (e.g. /v1)
     * 
     * @return \Ukey1\ApiClient\Request
     */
    public function setVersion($version)
    {
        $this->version = $version;
        return $this;
    }
    
    /**
     * Sets an endpoint
     * 
     * @param string $endpoint Endpoint
     * 
     * @return \Ukey1\ApiClient\Request
     */
    public function setEndpoint($endpoint)
    {
        $this->endpoint = $endpoint;
        return $this;
    }
    
    /**
     * Sets App credentials
     * 
     * @param string $appId     App ID
     * @param string $secretKey Secret key
     * 
     * @return \Ukey1\ApiClient\Request
     */
    public function setCredentials($appId, $secretKey)
    {
        $this->appId = $appId;
        $this->secretKey = $secretKey;
        return $this;
    }
    
    /**
     * Sets a user's access token
     * 
     * @param string $accessToken Access token
     * 
     * @return \Ukey1\ApiClient\Request
     */
    public function setAccessToken($accessToken) 
    {
        $this->accessToken = $accessToken;
        return $this;
    }
    
    /**
     * Sends a HTTP request
     * 
     * @param array|null $body           Array of parameters that will be send in JSON body
     * @param int        $expectedStatus Expected HTTP status
     * 
     * @return \Ukey1\ApiClient\Result
     * @throws \Ukey1\Exceptions\EndpointException
     */
    public function send(array $body = null, $expectedStatus = 200)
    {
        $json = $this->createJsonBody($body);
        $signature = $this->createSignature($json);
        
        $headers = $options = [];
        $headers["User-Agent"] = self::prepareUserAgent();
        
        if ($body) {
            $headers["Content-Type"] = "application/json";
            $headers["Content-Length"] = strlen($json);
            $options["body"] = $json;
        }
        
        $options["headers"] = $this->prepareHeaders($signature, $headers);
        
        try {
            $this->httpClient = new Client(
                [
                    "base_uri" => $this->host,
                    "timeout" => self::TIMEOUT,
                    "allow_redirects" => false
                ]
            );
            
            return new Result(
                $this->httpClient->request(
                    $this->method, 
                    $this->version . $this->endpoint, 
                    $options
                ), 
                $expectedStatus
            );
        } catch (TransferException $e) {
            throw new EndpointException($e->getMessage(), $e->getCode(), $e);
        }
    }
    
    /**
     * Creates a JSON string
     * 
     * @param array|null $body Array of request parameters
     * 
     * @return string|null
     */
    private function createJsonBody(&$body) 
    {
        if ($body && $this->method != self::GET) {
            return GuzzleHttp\json_encode($body);
        }
        
        return;
    }
    
    /**
     * Creates a request signature
     * 
     * @param string|null $json JSON string
     * 
     * @return string
     * @throws \Ukey1\Exceptions\EndpointException
     */
    private function createSignature(&$json) 
    {
        if (!function_exists("password_hash")) {
            throw new EndpointException("Required function password_hash() doesn't exist");
        }
        
        $password = $this->version
         . $this->endpoint
         . $this->method
         . $this->appId
         . $this->secretKey;
        
        if ($json) {
            $password .= $json;
        }
        
        if ($this->accessToken) {
            $password .= $this->accessToken;
        }
        
        return password_hash($password, PASSWORD_BCRYPT);
    }
    
    /**
     * Prepares request headers
     * 
     * @param string $signature Request signature
     * @param array  $headers   Array of predefined headers
     * 
     * @return array
     */
    private function prepareHeaders(&$signature, array $headers = []) 
    {
        $headers["x-ukey1-app"] = $this->appId;
        $headers["x-ukey1-signature"] = $signature;
        
        if ($this->accessToken) {
            $headers["Authorization"] = "UKEY1 " . $this->accessToken;
        }
        
        return $headers;
    }
    
    /**
     * Prepares a value of User-Agent header
     * 
     * @return string
     */
    private static function prepareUserAgent() 
    {
        return self::USER_AGENT . App::SDK_VERSION . " " . GuzzleHttp\default_user_agent();
    }
}