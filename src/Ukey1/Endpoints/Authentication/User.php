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
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\ValidationData;
use Lcobucci\JWT\Signer\Keychain;
use Lcobucci\JWT\Signer\Rsa\Sha512;

/**
 * API endpoint /auth/v2/me
 * 
 * @package Ukey1
 * @author  Zdenek Hofler <developers@asaritech.com>
 */
class User extends Endpoint
{
    /**
     * Endpoint
     */
    const ENDPOINT = "/auth/v2/me";
    
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
     * Raw result
     *
     * @var string
     */
    private $resultRaw;
    
    /**
     * JWT token object
     *
     * @var \Lcobucci\JWT\Token 
     */
    private $jwt;
    
    /**
     * Leeway to prevent server clock skew
     *
     * @var int
     */
    public $leeway = 0;
    
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
      
        if (!$this->accessToken) {
            throw new EndpointException("No access token was provided");
        }
        
        if (!$this->valid()) {
            throw new EndpointException("Access token expired");
        }
        
        $request = new Request(Request::GET);
        $request->setHost($this->app->getHost())
            ->setEndpoint(self::ENDPOINT)
            ->setCredentials($this->app->getAppId(), $this->app->getSecretKey())
            ->setAccessToken($this->accessToken);
        
        $result = $request->send();
        
        $this->resultRaw = $result->getBody();
        $this->resultData = $result->getData();
        $this->executed = true;
    }
    
    /**
     * Returns raw result
     * 
     * @return string
     */
    public function raw()
    {
        $this->execute();

        return $this->resultRaw;
    }
    
    /**
     * Create JWT object from access token string
     * 
     * @throws EndpointException
     */
    private function jwt()
    {
        if (!$this->jwt) {
            if (!$this->accessToken) {
                throw new EndpointException("No access token was provided");
            }

            $this->jwt = (new Parser())->parse($this->accessToken);
            $this->checkSignature();
        }
    }
    
    /**
     * Signature verification
     * 
     * @throws EndpointException
     */
    private function checkSignature()
    {
        $signer = new Sha512();
        $keychain = new Keychain();
        
        if (!$this->jwt->verify($signer, $keychain->getPublicKey($this->app->getSecretKey()))) {
            throw new EndpointException("Access token verification failed");
        }
    }

    /**
     * Returns an entity of the user (deprecated)
     * 
     * @return \Ukey1\User
     * @deprecated
     */
    public function user()
    {
        return $this->getUser();
    }

    /**
     * Returns an entity of the user
     * 
     * @return \Ukey1\User
     */
    public function getUser()
    {
        $this->execute();

        return new UserEntity($this->resultData);
    }

    /**
     * Return user ID (parsed from access token)
     *
     * @return string
     * @deprecated Use getId() instead
     */
    public function id()
    {
        return $this->getId();
    }
    
    /**
     * Return user ID (parsed from access token)
     * 
     * @return string
     */
    public function getId()
    {
        $this->jwt();
        
        $exist = $this->jwt->hasClaim("user");
        
        if (!$exist) {
            throw new EndpointException("Invalid access token");
        }
        
        $claim = $this->jwt->getClaim("user");
        
        if ($claim->id) {
            return $claim->id;
        } else {
            throw new EndpointException("Invalid access token");
        }
    }
    
    /**
     * Return if access token is valid
     * 
     * @return bool
     */
    public function valid()
    {
        $this->jwt();
        
        $data = new ValidationData();
        
        if ($this->leeway != 0) {
          $data->setCurrentTime(time() + $this->leeway);
        }
        
        return $this->jwt->validate($data);
    }
}