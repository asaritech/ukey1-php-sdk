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
 * API endpoint /auth/v2/extranet/users
 * 
 * @package Ukey1
 * @author  Zdenek Hofler <developers@asaritech.com>
 */
class ExtranetUsers extends Endpoint
{
    /**
     * Endpoint
     */
    const ENDPOINT = "/auth/v2/extranet/users";
    
    /**
     * Success status - the user has already been connected
     */
    const STATUS_CONNECTED = "connected";
    
    /**
     * Success status - the user was created and email sent
     */
    const STATUS_EMAIL_SENT = "email-sent";
    
    /**
     * Target email
     *
     * @var string
     */
    private $email;
    
    /**
     * Invitation locale
     *
     * @var string
     */
    private $locale;
    
    /**
     * Success status (FYI)
     *
     * @var string
     */
    private $status;
    
    /**
     * Reference ID
     *
     * @var string
     */
    private $referenceId;
    
    /**
     * Sets email of the user
     * 
     * @param string $email Email
     * 
     * @return \Ukey1\Endpoints\Authentication\ExtranetUsers
     */
    public function setEmail($email)
    {
        $this->email = $email;
        $this->referenceId = null;
        $this->executed = false;
        return $this;
    }
    
    /**
     * Sets locale for the invitation email 
     * 
     * (please note that not all locales are supported - if provided locale is unsupported, default en_GB will be used instead)
     * 
     * @param string $locale Locale (in the format xx_YY)
     * 
     * @return \Ukey1\Endpoints\Authentication\ExtranetUsers
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
        return $this;
    }
    
    /**
     * Sets reference ID for deletion
     * 
     * @param string $referenceId Reference ID
     * 
     * @return \Ukey1\Endpoints\Authentication\ExtranetUsers
     */
    public function setReferenceId($referenceId)
    {
        $this->referenceId = $referenceId;
        $this->email = null;
        $this->executed = false;
        return $this;
    }
    
    /**
     * Dynamic alias for create() or delete() methods based on input params
     */
    public function execute()
    {
        if ($this->email && $this->locale) {
            $this->create();
        } elseif ($this->referenceId) {
            $this->delete();
        }
    }
    
    /**
     * Executes an API request (method POST)
     * 
     * @throws \Ukey1\Exceptions\EndpointException
     */
    protected function create()
    {
        if ($this->executed) {
            return;
        }
        
        $request = new Request(Request::POST);
        $request->setHost($this->app->getHost())
            ->setEndpoint(self::ENDPOINT)
            ->setCredentials($this->app->getAppId(), $this->app->getSecretKey());
        
        $result = $request->send(
            [
                "email" => $this->email,
                "locale" => $this->locale
            ]
        );
        
        $data = $result->getData();
        
        if (!(isset($data["reference_id"]))) {
            throw new EndpointException("Invalid result structure: " . $result->getBody());
        }
        
        $this->status = $data["status"];
        $this->referenceId = $data["reference_id"];
        $this->executed = true;
    }
    
    /**
     * Executes an API request (method DELETE)
     * 
     * @throws \Ukey1\Exceptions\EndpointException
     */
    protected function delete()
    {
        if ($this->executed) {
            return;
        }

        $request = new Request(Request::DELETE);
        $request->setHost($this->app->getHost())
            ->setEndpoint(self::ENDPOINT)
            ->setCredentials($this->app->getAppId(), $this->app->getSecretKey());

        $result = $request->send(
            [
                "reference_id" => $this->referenceId
            ]
        );

        $result->getData();
        $this->executed = true;
    }
    
    /**
     * Returns success status
     * 
     * @return string
     */
    public function getSuccessStatus()
    {
        $this->execute();

        return $this->status;
    }
    
    /**
     * Returns reference ID needed for further deletion
     * 
     * @return string
     */
    public function getReferenceId()
    {
        $this->execute();

        return $this->referenceId;
    }
}