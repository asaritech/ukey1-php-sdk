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

namespace Ukey1\Endpoints\Authentication;

use Ukey1\Endpoint;
use Ukey1\ApiClient\Request;
use Ukey1\Exceptions\EndpointException;

/**
 * API endpoint /auth/v2/token
 * 
 * @package Ukey1
 * @author  Zdenek Hofler <developers@asaritech.com>
 */
class AccessToken extends Endpoint
{
    /**
     * Endpoint
     */
    const ENDPOINT = "/auth/v2/token";
    
    /**
     * Ukey1 GET param
     */
    const UKEY1_GET_PARAM = "_ukey1";
    
    /**
     * Gateway status - user canceled their request
     */
    const STATUS_CANCELED = "canceled";
    
    /**
     * Gateway status - user authorized your app
     */
    const STATUS_AUTHORIZED = "authorized";
    
    /**
     * Your reference ID 
     * (it should be unique but it's not strict)
     *
     * @var string|int
     */
    private $requestId;
    
    /**
     * Ukey1 reference ID
     *
     * @var string
     */
    private $connectId;
    
    /**
     * Array of Ukey1 GET params
     *
     * @var array
     */
    private $getParams;
    
    /**
     * Access token
     *
     * @var string
     */
    private $accessToken;
    
    /**
     * Access token expiration
     *
     * @var string
     */
    private $accessTokenExpiration;
    
    /**
     * Array of granted permissions
     *
     * @var array
     */
    private $grantedScope;
    
    /**
     * Sets your reference ID
     * 
     * @param string|int $requestId Your reference ID
     * 
     * @return \Ukey1\Endpoints\Authentication\AccessToken
     */
    public function setRequestId($requestId)
    {
        $this->requestId = $requestId;
        return $this;
    }
    
    /**
     * Sets Ukey1 reference ID
     * 
     * @param string|int $connectId Ukey1 reference ID
     * 
     * @return \Ukey1\Endpoints\Authentication\AccessToken
     */
    public function setConnectId($connectId)
    {
        $this->connectId = $connectId;
        return $this;
    }
    
    /**
     * Executes an API request
     * 
     * @return boolean
     * @throws \Ukey1\Exceptions\EndpointException
     */
    public function execute()
    {
        if ($this->executed) {
            return true;
        }
      
        $check = $this->checkInputs();
        
        if (!$check) {
            return false;
        }
        
        $request = new Request(Request::POST);
        $request->setHost($this->app->host())
            ->setEndpoint(self::ENDPOINT)
            ->setCredentials($this->app->appId(), $this->app->secretKey());
        
        $result = $request->send(
            [
                "request_id" => $this->requestId,
                "connect_id" => $this->connectId,
                "auth_code" => $this->getParam("code")
            ]
        );
        
        $data = $result->getData();
        
        if (!(isset($data["access_token"]) && isset($data["expiration"]) && isset($data["scope"]))) {
            throw new EndpointException("Invalid result structure: " . $result->getBody());
        }
        
        $this->accessToken = $data["access_token"];
        $this->accessTokenExpiration = $data["expiration"];
        $this->grantedScope = $data["scope"];
        $this->executed = true;
        
        return true;
    }
    
    /**
     * Returns true if user authorized the request
     * 
     * @return bool
     */
    public function check()
    {
      return $this->execute();
    }
    
    /**
     * Checks given inputs
     * 
     * @return boolean
     * @throws \Ukey1\Exceptions\EndpointException
     */
    private function checkInputs()
    {
        if (!($this->requestId && $this->connectId)) {
            throw new EndpointException("No request ID or connect ID were provided");
        }
        
        $requestId = $this->getParam("request_id");
        $connectId = $this->getParam("connect_id");
        $code = $this->getParam("code");
        $signature = $this->getParam("signature");
        $status = $this->getParam("result");
        
        if ($this->requestId != $requestId) {
            throw new EndpointException("Invalid request ID");
        }
        
        if ($this->connectId != $connectId) {
            throw new EndpointException("Invalid connect ID");
        }
        
        if ($status == self::STATUS_AUTHORIZED) {
          if (!$code) {
            throw new EndpointException("No auth code");
          }
        }
        
        $this->checkSignature(base64_decode($signature, true), $status, $code);
        
        return ($status == self::STATUS_AUTHORIZED);
    }
    
    /**
     * Gets a Ukey1 param
     * 
     * @param string $key The param key
     * 
     * @return string
     */
    private function getParam($key)
    {
        if (!$this->getParams) {
            $this->getParams = filter_input(INPUT_GET, self::UKEY1_GET_PARAM, FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        }
        
        if (isset($this->getParams[$key]) && $this->getParams[$key] && (is_string($this->getParams[$key]) || is_numeric($this->getParams[$key]))) {
            return $this->getParams[$key];
        } else {
            return "";
        }
    }
    
    /**
     * Checks a given signature
     * 
     * @param string $signature Security signature
     * @param string $status    Status
     * @param string $code      Auth code
     * 
     * @throws \Ukey1\Exceptions\EndpointException
     */
    private function checkSignature($signature, $status, $code)
    {
        if (!$signature) {
            throw new EndpointException("Invalid signature");
        }
      
        $data = $this->app->appId() . $this->requestId . $this->connectId . $code . $status;
        $result = openssl_verify($data, $signature, $this->app->secretKey(), OPENSSL_ALGO_SHA512);
        
        if ($result < 1) {
            $errstr = openssl_error_string();

            throw new EndpointException("Invalid signature or an openssl error (" . ($errstr ? $errstr : "-") . ")");
        }
    }

    /**
     * Returns an access token
     * 
     * @return string
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }
    
    /**
     * Returns an expiration of the access token
     * 
     * @return string
     */
    public function getAccessTokenExpiration()
    {
        return $this->accessTokenExpiration;
    }

    /**
     * Returns an array of granted permissions
     * 
     * @return array
     */
    public function getScope()
    {
        return $this->grantedScope;
    }
}