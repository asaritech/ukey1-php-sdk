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

/**
 * User entity
 * 
 * @package Ukey1
 * @author  Zdenek Hofler <developers@asaritech.com>
 */
class User
{
    private $scope;
    private $data;
    
    /**
     * Creates an entity of the user
     * 
     * @param array $responseData Array of data from response to /me endpoint
     */
    public function __construct(array $responseData)
    {
        $this->data = $responseData;
        $this->scope = $this->data["scope"];
    }
    
    /**
     * Returns current available scope
     * 
     * @return array
     * @deprecated
     */
    public function scope()
    {
        return $this->getScope();
    }
    
    /**
     * Returns current available scope
     *
     * @return array
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * Gets all user values
     * 
     * @return array
     */
    public function getAll()
    {
        return $this->data["user"];
    }
    
    /**
     * Gets any value
     * 
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        if (isset($this->data["user"][$key]) && !empty($this->data["user"][$key])) {
            return $this->data["user"][$key];
        }

        return null;
    }
    
    /**
     * User ID
     * 
     * @return string
     * @deprecated
     */
    public function id()
    {
        return $this->getId();
    }

    /**
     * User ID
     *
     * @return string
     */
    public function getId()
    {
        return $this->get("id");
    }

    /**
     * User's firstname
     *
     * @return string|null
     * @deprecated
     */
    public function firstname()
    {
        return $this->getFirstname();
    }
    /**
     * User's firstname
     *
     * @return string|null
     */
    public function getFirstname()
    {
        return $this->get("firstname");
    }

    /**
     * User's surname
     * 
     * @return string|null
     * @deprecated
     */
    public function surname()
    {
        return $this->getSurname();
    }
    /**
     * User's surname
     *
     * @return string|null
     */
    public function getSurname()
    {
        return $this->get("surname");
    }

    /**
     * User's language (ISO 639-1 code)
     * 
     * @return string|null
     * @deprecated
     */
    public function language()
    {
        return $this->getLanguage();
    }
    
    /**
     * User's language (ISO 639-1 code)
     *
     * @return string|null
     */
    public function getLanguage()
    {
        return $this->get("language");
    }

    /**
     * User's country (ISO 3166-1 alpha-2 code)
     * 
     * @return string|null
     * @deprecated
     */
    public function country()
    {
        return $this->getCountry();
    }

    /**
     * User's country (ISO 3166-1 alpha-2 code)
     *
     * @return string|null
     */
    public function getCountry()
    {
        return $this->get("country");
    }

    /**
     * User's email
     * 
     * @return string|null
     * @deprecated
     */
    public function email()
    {
        return $this->getEmail();
    }

    /**
     * User's email
     *
     * @return string|null
     */
    public function getEmail()
    {
        return $this->get("email");
    }

    /**
     * User's image (plain URL)
     * 
     * @return string|null
     * @deprecated Use getImageUrl() instead
     */
    public function image()
    {
        return $this->getImageUrl();
    }
    /**
     * User's image (plain URL)
     *
     * @return string|null
     */
    public function getImageUrl()
    {
        return $this->get("image");
    }
}