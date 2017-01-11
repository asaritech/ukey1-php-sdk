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

use Ukey1\Thumbnail;

/**
 * An entity of the user
 * 
 * @package Ukey1
 * @author  Zdenek Hofler <developers@asaritech.com>
 */
class User
{
    private $authorized;
    private $data;
    
    /**
     * Creates an entity of the user
     * 
     * @param array $responseData Array of data from response to /me endpoint
     */
    public function __construct(array $responseData)
    {
        $this->data = $responseData;
        $this->authorized = (isset($this->data["authorized"]) && $this->data["authorized"] == 1);
    }
    
    /**
     * Checks if you still have a valid authorization to user's data
     * 
     * @return boolean
     */
    public function check()
    {
        return $this->authorized;
    }
    
    /**
     * User ID
     * 
     * @return string|int
     */
    public function id()
    {
        if ($this->authorized) {
            return $this->data["user"]["id"];
        }
    }
    
    /**
     * User's full name
     * 
     * @return string
     */
    public function fullname()
    {
        if ($this->authorized) {
            return $this->data["user"]["name"]["display"];
        }
    }
    
    /**
     * User's firstname
     * 
     * @return string
     */
    public function firstname()
    {
        if ($this->authorized) {
            return $this->data["user"]["name"]["first_name"];
        }
    }
    
    /**
     * User's surname
     * 
     * @return string
     */
    public function surname()
    {
        if ($this->authorized) {
            return $this->data["user"]["name"]["surname"];
        }
    }
    
    /**
     * User's language (ISO 639-1 code)
     * 
     * @return string
     */
    public function language()
    {
        if ($this->authorized) {
            return $this->data["user"]["locale"]["language"];
        }
    }
    
    /**
     * User's country (ISO 3166-1 alpha-3 code)
     * 
     * @return string
     */
    public function country()
    {
        if ($this->authorized) {
            return $this->data["user"]["locale"]["country"];
        }
    }
    
    /**
     * User's email
     * 
     * @return string
     */
    public function email()
    {
        if ($this->authorized && isset($this->data["user"]["email"])) {
            return $this->data["user"]["email"];
        }
    }
    
    /**
     * User's thumbnail entity
     * 
     * @return \Ukey1\Thumbnail
     */
    public function thumbnailEntity()
    {
        if ($this->authorized && isset($this->data["user"]["thumbnail"])) {
            return new Thumbnail($this->data["user"]["thumbnail"]);
        }
        
        return new Thumbnail();
    }
    
    /**
     * User's thumbnail (plain URL)
     * 
     * @return string
     */
    public function thumbnailUrl()
    {
        if ($this->authorized && isset($this->data["user"]["thumbnail"]["url"])) {
            return $this->data["user"]["thumbnail"]["url"];
        }
    }
}