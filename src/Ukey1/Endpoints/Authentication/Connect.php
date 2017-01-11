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
 * API endpoint /auth/connect
 * 
 * @package Ukey1
 * @author  Zdenek Hofler <developers@asaritech.com>
 */
class Connect extends Endpoint
{
    /**
     * Endpoint
     */
    const ENDPOINT = "/auth/connect";
    
    /**
     * Your reference ID 
     * (it should be unique but it's not strict)
     *
     * @var string|int
     */
    private $requestId;
    
    /**
     * URL for redirecting the user back from the Ukey1 gateway
     *
     * @var string
     */
    private $returnUrl;
    
    /**
     * Array of permissions
     *
     * @var array
     */
    private $scope;
    
    /**
     * Ukey1 reference ID
     *
     * @var string
     */
    private $connectId;
    
    /**
     * Ukey1 gateway URL
     *
     * @var string
     */
    private $gatewayUrl;
    
    /**
     * Sets your reference ID
     * 
     * @param string|int $requestId Your reference ID
     * 
     * @return \Ukey1\Endpoints\Authentication\Connect
     */
    public function setRequestId($requestId)
    {
        $this->requestId = $requestId;
        return $this;
    }
    
    /**
     * Sets URL for redirecting the user back from the Ukey1 gateway
     * 
     * @param string $returnUrl URL
     * 
     * @return \Ukey1\Endpoints\Authentication\Connect
     */
    public function setReturnUrl($returnUrl)
    {
        $this->returnUrl = $returnUrl;
        return $this;
    }
    
    /**
     * Sets an array of permissions
     * 
     * @param array $permissions Array of permissions
     * 
     * @return \Ukey1\Endpoints\Authentication\Connect
     */
    public function setScope(array $permissions)
    {
        $this->scope = $permissions;
        return $this;
    }
    
    /**
     * Executes an API request
     * 
     * @throws \Ukey1\Exceptions\EndpointException
     */
    public function execute()
    {
        $request = new Request(Request::POST);
        $request->setHost($this->app->host())
            ->setVersion(self::API_VERSION)
            ->setEndpoint(self::ENDPOINT)
            ->setCredentials($this->app->appId(), $this->app->secretKey());
        
        $result = $request->send(
            [
                "request_id" => $this->requestId,
                "scope" => $this->scope,
                "return_url" => $this->returnUrl
            ]
        );
        
        $data = $result->getData();
        
        if (!(isset($data["connect_id"]) && isset($data["gateway"]["url"]) && isset($data["gateway"]["expiration"]))) {
            throw new EndpointException("Invalid result structure: " . $result->getBody());
        }
        
        if (!$this->checkExpiration($data["gateway"]["expiration"])) {
            throw new EndpointException("Gateway URL expired");
        }
        
        $this->connectId = $data["connect_id"];
        $this->gatewayUrl = $data["gateway"]["url"];
    }
    
    /**
     * Returns a Ukey1 reference ID
     * 
     * @return string
     */
    public function getId()
    {
        return $this->connectId;
    }
    
    /**
     * Returns a gateway URL
     * 
     * @return string
     */
    public function getGatewayUrl()
    {
        return $this->gatewayUrl;
    }
    
    /**
     * Redirects user to gateway URL
     */
    public function redirect()
    {
        if ($this->gatewayUrl) {
            $code = 302;
            $message = $code . " Found";
            
            header("HTTP/1.1 " . $message, true, $code);
            header("Status: " . $message, true, $code);
            header("Location: {$this->gatewayUrl}");
            exit;
        }
    }
}