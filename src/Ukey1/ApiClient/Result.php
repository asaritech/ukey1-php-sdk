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

use Ukey1\Exceptions\EndpointException;
use GuzzleHttp;
use GuzzleHttp\Psr7\Response;

/**
 * An instance of API result
 * 
 * @package Ukey1
 * @author  Zdenek Hofler <developers@asaritech.com>
 */
class Result
{
    /**
     * An instance of PSR-7 response
     *
     * @var \GuzzleHttp\Psr7\Response 
     */
    private $response;
    
    /**
     * Creates an instance of API result
     * 
     * @param \GuzzleHttp\Psr7\Response $response       An instance of PSR-7 response
     * @param int                       $expectedStatus Expected HTTP status
     */
    public function __construct(Response $response, $expectedStatus)
    {
        $this->response = $response;
    }
    
    /**
     * Gets JSON string
     * 
     * @return string
     */
    public function getBody()
    {
        return $this->response->getBody();
    }
    
    /**
     * Gets JSON data as array
     * 
     * @return array
     */
    public function getData()
    {
        $data = GuzzleHttp\json_decode($this->response->getBody(), true);
        
        if (!isset($data["result"])) {
            throw new EndpointException("Invalid result structure: " . $this->response->getBody());
        }
        
        $this->checkStatus($data["result"]);
        
        return $data;
    }
    
    /**
     * Checks HTTP status
     * 
     * @param int $expectedStatus Expected HTTP status
     * 
     * @throws \Ukey1\Exceptions\EndpointException
     */
    private function checkStatus($expectedStatus)
    {
        $status = $this->response->getStatusCode();
        
        if ($status != $expectedStatus) {
            throw new EndpointException("Unexpected HTTP status {$status} " . $this->response->getReasonPhrase());
        }
    }
}