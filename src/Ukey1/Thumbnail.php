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
 * An entity of user's thumbnail
 * 
 * @package Ukey1
 * @author  Zdenek Hofler <developers@asaritech.com>
 */
class Thumbnail
{
    private $exists;
    private $default;
    private $data;
    
    /**
     * Creates an entity of user's thumbnail
     * 
     * @param array $data Array of thumbnail details
     */
    public function __construct(array $data = null)
    {
        if ($data) {
            $this->exists = true;
            $this->data = $data;
            $this->default = !$data["isset"];
        } else {
            $this->exists = false;
        }
    }
    
    /**
     * Checks if it is a default (system) thumbnail 
     * (if TRUE, it means that user doesn't have a custom thumbnail)
     * 
     * @return boolean
     */
    public function isDefault()
    {
        return $this->default;
    }
    
    /**
     * URL
     * 
     * @return string|int
     */
    public function url()
    {
        if ($this->exists) {
            return $this->data["url"];
        }
    }
    
    /**
     * Downloads an image from URL 
     * (returns the file in a string; or if destination is set, returns a boolean status)
     * 
     * @param string $destination Optional target destination in your filesystem)
     * 
     * @return string|boolean
     */
    public function download($destination = null)
    {
        if ($this->exists) {
            $content = file_get_contents($this->data["url"]);

            if ($destination) {
                return file_put_contents($destination, $content);
            }

            return $content;
        }
    }
    
    /**
     * Image width
     * 
     * @return string
     */
    public function width()
    {
        if ($this->exists) {
            return $this->data["width"];
        }
    }
    
    /**
     * Image height
     * 
     * @return string
     */
    public function height()
    {
        if ($this->exists) {
            return $this->data["height"];
        }
    }
}