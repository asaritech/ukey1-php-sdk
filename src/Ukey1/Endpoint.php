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

use Ukey1\App;

/**
 * Abstract class for all API endpoints
 * 
 * @package Ukey1
 * @author  Zdenek Hofler <developers@asaritech.com>
 */
abstract class Endpoint
{
    /**
     * API version
     */
    const API_VERSION = "/v1";
    
    /**
     * An entity of your app
     *
     * @var \Ukey1\App 
     */
    protected $app;
    
    /**
     * Creates an instance of API endpoint
     * 
     * @param \Ukey1\App $app An entity of your app
     */
    final public function __construct(App $app)
    {
        $this->app = $app;
        $this->app->check();
    }
    
    /**
     * Checks if expiration time is missed
     * 
     * @param string $expiration Expiration time
     * 
     * @return boolean
     */
    final protected function checkExpiration($expiration)
    {
        return (strtotime($expiration) > time());
    }
}