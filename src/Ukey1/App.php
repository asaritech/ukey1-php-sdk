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
    const SDK_VERSION = "3.0.5";
    
    /**
     * Default host
     */
    const HOST = "https://api.ukey.one";
    
    /**
     * Domain name
     *
     * @var string
     */
    private static $domain;
    
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
     * @deprecated Use getAppId() or setAppId() instead
     */
    public function appId($appId = null)
    {
        if ($appId) {
            return $this->setAppId($appId);
        }
        
        return $this->getAppId();
    }

    /**
     * Get your App ID
     **
     * @return string
     */
    public function getAppId()
    {
        return $this->appId;
    }

    /**
     * Sets your App ID
     *
     * @param string $appId Your App ID
     *
     * @return \Ukey1\App
     */
    public function setAppId($appId)
    {
        $this->appId = $appId;
        return $this;
    }

    /**
     * Sets or gets your secret key
     * 
     * @param string|null $secretKey Your secret key
     * 
     * @return \Ukey1\App|string
     * @deprecated Use getSecretKey() or setSecretKey() instead
     */
    public function secretKey($secretKey = null)
    {
        if ($secretKey) {
            return $this->setSecretKey($secretKey);
        }
        
        return $this->getSecretKey();
    }

    /**
     * Gets your secret key
     *
     * @return string
     */
    public function getSecretKey()
    {
        return $this->secretKey;
    }

    /**
     * Sets or gets your secret key
     *
     * @param string $secretKey Your secret key
     *
     * @return \Ukey1\App
     */
    public function setSecretKey($secretKey)
    {
        $this->secretKey = "-----BEGIN PUBLIC KEY-----" . PHP_EOL . chunk_split($secretKey, 64, PHP_EOL) . "-----END PUBLIC KEY-----";
        $this->checkKey();

        return $this;
    }

    /**
     * Returns host
     * 
     * @return string
     * @deprecated Use getHost() instead
     */
    public function host()
    {
        return $this->getHost();
    }
    
    /**
     * Returns host
     *
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Checks if both App ID and Secret Key are set
     * 
     * @throws \Ukey1\Exceptions\AppException
     */
    public function check()
    {
        if (!($this->appId && $this->secretKey)) {
            throw new AppException("Please set both your App ID and Secret Key");
        }
    }
    
    /**
     * Check the key
     * 
     * @throws \Ukey1\Exceptions\AppException
     */
    private function checkKey()
    {
        $keyResource = openssl_get_publickey($this->secretKey);
        $details = openssl_pkey_get_details($keyResource);

        if (!isset($details["key"]) || $details["type"] !== OPENSSL_KEYTYPE_RSA) {
             throw new AppException("Provided Secret Key is invalid");
        }
    }
    
    /**
     * Set domain name
     * 
     * @param string $domain Domain name
     */
    public static function setDomain($domain)
    {
      self::$domain = substr($domain, -1) == "/" ? substr($domain, 0, -1) : $domain;
    }
    
    /**
     * Returns domain name
     * 
     * @return string
     */
    public static function getDomain()
    {
      return self::$domain;
    }
}