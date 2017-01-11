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
 * API endpoint /auth/token/refresh
 * 
 * @package Ukey1
 * @author  Zdenek Hofler <developers@asaritech.com>
 */
class RefreshToken extends Endpoint
{
    /**
     * Endpoint
     */
    const ENDPOINT = "/auth/token/refresh";
    
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
    private $accesssTokenExpiration;
    
    /**
     * Token for getting a new access token
     *
     * @var string
     */
    private $refreshToken;
    
    /**
     * Array of granted permissions
     *
     * @var array
     */
    private $grantedScope;
    
    /**
     * Sets a refresh token
     * 
     * @param string $refreshToken Refresh token
     * 
     * @return \Ukey1\Endpoints\Authentication\RefreshToken
     */
    public function setRefreshToken($refreshToken)
    {
        $this->refreshToken = $refreshToken;
        return $this;
    }
    
    /**
     * Executes an API request
     * 
     * @throws \Ukey1\Exceptions\EndpointException
     */
    public function execute()
    {
        if (!$this->refreshToken) {
            throw new EndpointException("No refresh token was provided");
        }
        
        $request = new Request(Request::POST);
        $request->setHost($this->app->host())
            ->setVersion(self::API_VERSION)
            ->setEndpoint(self::ENDPOINT)
            ->setCredentials($this->app->appId(), $this->app->secretKey());
        
        $result = $request->send(
            [
                "refresh_token" => $this->refreshToken
            ]
        );
        
        $data = $result->getData();
        
        if (!(isset($data["access_token"]) && isset($data["expiration"]) && isset($data["scope"]))) {
            throw new EndpointException("Invalid result structure: " . $result->getBody());
        }
        
        $this->accessToken = $data["access_token"];
        $this->accesssTokenExpiration = $data["expiration"];
        $this->grantedScope = $data["scope"];
        
        if (isset($data["refresh_token"])) {
            $this->refreshToken = $data["refresh_token"];
        } else {
            $this->refreshToken = null;
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
        return $this->accesssTokenExpiration;
    }

    /**
     * Returns a refresh token
     * 
     * @return string
     */
    public function getRefreshToken()
    {
        return $this->refreshToken;
    }

    /**
     * Returns an array of granted permissions
     * 
     * @return string
     */
    public function getScope()
    {
        return $this->grantedScope;
    }
}