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
use Ukey1\User as UserEntity;
use Ukey1\ApiClient\Request;
use Ukey1\Exceptions\EndpointException;

/**
 * API endpoint /me
 * 
 * @package Ukey1
 * @author  Zdenek Hofler <developers@asaritech.com>
 */
class User extends Endpoint
{
    /**
     * Endpoint
     */
    const ENDPOINT = "/me";
    
    /**
     * Access token
     *
     * @var string
     */
    private $accessToken;
    
    /**
     * Result data
     *
     * @var array
     */
    private $resultData;
    
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
     * @return string
     * @throws \Ukey1\Exceptions\EndpointException
     */
    public function execute()
    {
        if (!$this->accessToken) {
            throw new EndpointException("No access token was provided");
        }
        
        $request = new Request(Request::GET);
        $request->setHost($this->app->host())
            ->setVersion(self::API_VERSION)
            ->setEndpoint(self::ENDPOINT)
            ->setCredentials($this->app->appId(), $this->app->secretKey())
            ->setAccessToken($this->accessToken);
        
        $result = $request->send();
        
        $raw = $result->getBody();
        $this->resultData = $result->getData();
        
        return $raw;
    }

    /**
     * Returns an entity of the user
     * 
     * @return \Ukey1\User
     */
    public function getUser()
    {
        return new UserEntity($this->resultData);
    }
}