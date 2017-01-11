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

namespace Ukey1\Generators;

use Ukey1\Exceptions\GeneratorException;

/**
 * Random string generator
 * 
 * @package Ukey1
 * @author  Zdenek Hofler <developers@asaritech.com>
 */
class RandomString
{
    /**
     * Generates a random string of bytes 
     * (note that returned string contains twice more characters than you specify in the parameter $length)
     * 
     * @param int $length Number of bytes (default 16)
     * 
     * @return string
     * @throws \Ukey1\Exceptions\GeneratorException
     */
    public static function generate($length = 16) 
    {
        if (!function_exists("openssl_random_pseudo_bytes")) {
            throw new GeneratorException("Required function openssl_random_pseudo_bytes() doesn't exist");
        }
        
        return bin2hex(openssl_random_pseudo_bytes($length));
    }
}