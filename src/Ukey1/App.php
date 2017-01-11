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

namespace Ukey1;

use Ukey1\Exceptions\AppException;

/**
 * An instance with configuration of your app
 * 
 * @package Ukey1
 * @author  Zdenek Hofler <developers@asaritech.com>
 */
class App
{
    /**
     * SDK version
     */
    const SDK_VERSION = "1.0.0";
    
    /**
     * Default host 
     * 
     * NOTICE: This API host may be changed in near future and in that case we 
     * will notify all of developers from our database, of course in advance.
     */
    const HOST = "https://ukey1-api.nooledge.com";
    
    /**
     * API host
     * 
     * @var string 
     */
    private $host;
    
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
     * Creates an entity of your app
     * 
     * @param string|null $host API host (if you need to use another than the default)
     */
    public function __construct($host = null)
    {
        $this->host = ($host ? $host : self::HOST);
    }
    
    /**
     * Sets or gets your App ID
     * 
     * @param string|null $appId Your App ID
     * 
     * @return \Ukey1\App|string
     */
    public function appId($appId = null)
    {
        if ($appId) {
            $this->appId = $appId;
            return $this;
        }
        
        return $this->appId;
    }
    
    /**
     * Sets or gets your secret key
     * 
     * @param string|null $secretKey Your secret key
     * 
     * @return \Ukey1\App|string
     */
    public function secretKey($secretKey = null)
    {
        if ($secretKey) {
            $this->secretKey = $secretKey;
            return $this;
        }
        
        return $this->secretKey;
    }
    
    /**
     * Returns the host
     * 
     * @return string
     */
    public function host()
    {
        return $this->host;
    }
    
    /**
     * Checks if both App ID and secret key are set
     * 
     * @throws \Ukey1\Exceptions\AppException
     */
    public function check()
    {
        if (!($this->appId && $this->secretKey)) {
            throw new AppException("Please set both your App ID and secret key");
        }
    }
}