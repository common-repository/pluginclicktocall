<?php
/*
Copyright (c) 2010, Ribbit / BT Group PLC
All rights reserved.

Redistribution and use in source and binary forms, with or without modification,
are permitted provided that the following conditions are met:

Redistributions of source code must retain the above copyright notice,
this list of conditions and the following disclaimer.
Redistributions in binary form must reproduce the above copyright notice, this
list of conditions and the following disclaimer in the documentation and/or other
materials provided with the distribution.

Neither the name of BT Group PLC, Ribbit Corporation, nor the names of its contributors
may be used to endorse or promote products derived from this software without specific prior
written permission

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY
EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES
OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT
SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT
OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION)
HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR
TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE,
EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
*/
/**
 * Contains all the custom Exceptions used by Ribbit
 *
 * @package Ribbit
 */
/**
 * The most common exception, thrown most often when there is some problem with the calls to the Ribbit Server
 *
 * @package Ribbit
 * @version 1.6.0
 * @author BT/Ribbit
 */
class RibbitException extends Exception
{
    private $_status;
    private $_message;
    static function create_exception($status, $uri, $text)
    {
        $e = null;
        switch ($status) {
        case "409":
            $e = new ResourceConflictException($text);
            break;

        case "404":
            $e = new ResourceNotFoundException($uri, $status, $text);
            break;

        case "402":
            $e = new InsufficientCreditException($text);
            break;

        default:
            $e = new RibbitException($text, $status);
            break;
        }
        return $e;
    }
    /**
     * The constructor for RibbitException. You shouldn't invoke this.
     */
    public function RibbitException($error, $status)
    {
        parent::__construct($error);
        $this->_message = $error;
        $this->_status = $status;
    }
    /**
     * The error message
     *
     * @return string the error message returned by the service
     */
    public function getErrorMessage()
    {
        return $this->_message;
    }
    /**
     * HTTP Status code returned when they error comes from a call to the Ribbit server
     *
     * @return string an http status code
     */
    public function getStatus()
    {
        return $this->_status;
    }
}
/**
 * Thrown when an attempt is made to access a method that requires an authenticated user in the session, and there is none.
 *
 * @package Ribbit
 * @version 1.6.0
 * @author BT/Ribbit
 */
class AuthenticatedUserRequiredException extends RibbitException
{
    public function AuthenticatedUserRequiredException()
    {
        parent::__construct("An authenticated user is required with this request", "");
    }
}
/**
 * Thrown when an attempt is made to create a resource, and that resource already exists.
 *
 * @package Ribbit
 * @version 1.6.0
 * @author BT/Ribbit
 */
class ResourceConflictException extends RibbitException
{
    public function ResourceConflictException($exception)
    {
        parent::__construct($exception, "409");
    }
}
/**
 * Thrown when an attempt to made to retrieve a resource, and the resource does not exist.
 *
 * @package Ribbit
 * @version 1.6.0
 * @author BT/Ribbit
 */
class ResourceNotFoundException extends RibbitException
{
    public function ResourceNotFoundException($resource, $status, $error)
    {
        parent::__construct(strlen($error) > 0 ? $error : $resource . " does not exist", $status);
    }
}
/**
 * Thrown when attempting to login a user and incorrect credentials are supplied
 *
 * @package Ribbit
 * @version 1.6.0
 * @author BT/Ribbit
 */
class InvalidUserNameOrPasswordException extends RibbitException
{
    public function InvalidUserNameOrPasswordException()
    {
        parent::__construct("Invalid User name or password", "400");
    }
}
/**
 * Thrown when attempting to login a user and incorrect credentials are supplied
 *
 * @package Ribbit
 * @version 1.6.0
 * @author BT/Ribbit
 */
class InsufficientCreditException extends RibbitException
{
    public function InsufficientCreditException($text)
    {
        parent::__construct($text, "402");
    }
}
/**
 * Thrown when attempting to login a user and incorrect credentials are supplied
 *
 * @package Ribbit
 * @version 1.6.0
 * @author BT/Ribbit
 */
class SessionExpiredException extends RibbitException
{
    public function SessionExpiredException()
    {
        parent::__construct("The user session on the Ribbit Server has expired", "402");
    }
}
?>