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
 * API endpoint /auth/v2/system/scopes
 * 
 * @package Ukey1
 * @author  Zdenek Hofler <developers@asaritech.com>
 */
class SystemScopes extends Endpoint
{
    /**
     * Endpoint
     */
    const ENDPOINT = "/auth/v2/system/scopes";
    
    /**
     * Access token
     *
     * @var string
     */
    private $accessToken;
    
    /**
     * Available permissions
     *
     * @var array
     */
    private $scopes;
    
    /**
     * Rejected permissions
     *
     * @var array
     */
    private $rejected;
    
    /**
     * Sets an access token
     * 
     * @param string $accessToken Access token
     * 
     * @return \Ukey1\Endpoints\Authentication\User
     */
    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;
        return $this;
    }
    
    /**
     * Executes an API request
     * 
     * @throws \Ukey1\Exceptions\EndpointException
     */
    public function execute()
    {
        if ($this->executed) {
            return;
        }
        
        $request = new Request(Request::GET);
        $request->setHost($this->app->host())
            ->setEndpoint(self::ENDPOINT)
            ->setCredentials($this->app->appId(), $this->app->secretKey());
        
        if ($this->accessToken) {
            $request->setAccessToken($this->accessToken);
        }
        
        $result = $request->send();
        $data = $result->getData();
        
        if (!(isset($data["permissions"]))) {
            throw new EndpointException("Invalid result structure: " . $result->getBody());
        }
        
        $this->scopes = $data["permissions"];
        $this->executed = true;
        
        if (isset($data["rejected-permissions"])) {
          $this->rejected = $data["rejected-permissions"];
        }
    }
    
    /**
     * Returns available permissions
     * 
     * @return string
     */
    public function getAvailablePermissions()
    {
        $this->execute();

        return $this->scopes;
    }
    
    /**
     * Returns rejected permissions
     * 
     * @return string
     */
    public function getRejectedPermissions()
    {
        $this->execute();

        return $this->rejected;
    }
}