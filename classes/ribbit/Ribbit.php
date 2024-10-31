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
 * The main file in the Ribbit API
 *
 * @package Ribbit
 */
require_once 'lib/RibbitCallbackNotification.class.php';
require_once 'lib/RibbitConfig.class.php';
require_once 'lib/RibbitSignedRequest.class.php';
require_once 'lib/RibbitApplication.class.php';
require_once 'lib/RibbitCall.class.php';
require_once 'lib/RibbitCallPlayRequest.class.php';
require_once 'lib/RibbitCallPlayMedia.class.php';
require_once 'lib/RibbitCallRecordRequest.class.php';
require_once 'lib/RibbitCallLegDtmfRequest.class.php';
require_once 'lib/RibbitDevice.class.php';
require_once 'lib/RibbitDomain.class.php';
require_once 'lib/RibbitLabel.class.php';
require_once 'lib/RibbitMediaFiles.class.php';
require_once 'lib/RibbitMessage.class.php';
require_once 'lib/RibbitService.class.php';
require_once 'lib/RibbitToken.class.php';
require_once 'lib/RibbitUser.class.php';
require_once ('lib/RibbitUserAuthentication.class.php');
/**
 * The main class in the Ribbit API
 *
 * This is the class you'll use every time you use the Ribbit API.
 * You'll start most of your code with<pre>$ribbit = Ribbit::getInstance();</pre>
 * Then you'll probably want to login a user <pre>$ribbit->Login($email, $password);</pre>
 *
 *
 * @package Ribbit
 * @version 1.6.0
 * @author BT/Ribbit
 */
class Ribbit
{
    const VERSION = '1.6.0';
    /**
     * Everything you do with Ribbit starts by calling Ribbit::getInstance();
     *
     * @return Ribbit
     */
    public static function getInstance()
    {
        static $instance;
        if (!isset($instance)) $instance = new Ribbit();
        return $instance;
    }
    private function Ribbit()
    {
    }
    /**
     * Returns an instance of RibbitConfig. You can set your application credentials through this class, in
     * addition to setting them in ribbit_config.yml
     *
     *  @return RibbitConfig
     */
    public function getConfig()
    {
        return RibbitConfig::getInstance();
    }
    /**
     * Determine if there is a logged in user
     *
     *  @return boolean
     */
    public function isLoggedIn()
    {
        return $this->getUserId() != null && !$this->isSessionExpired();
    }
    /**
     * Get details about the currently logged in user
     *
     *  @return RibbitUser An associative array containing details about the User
     */
    public function getUser()
    {
        $result = null;
        if ($this->isLoggedIn()) {
            $result = $this->Users()->getUser($this->getUserId());
        }
        return $result;
    }
    /**
     * Returns the currently logged on user, or null
     *
     * @return string|null Returns the currently logged on users ID, or null if there is no user logged on
     */
    public function getUserName()
    {
        return $this->getConfig()->getUserName();
    }
    /**
     * Returns the currently logged on user, or null
     *
     * @deprecated use getUserName()
     * @return string|null Returns the currently logged on users ID, or null if there is no user logged on
     */
    public function getCurrentUser()
    {
        return $this->getUserName();
    }
    /**
     * Returns the currently logged on user's id, or null
     *
     * @return string|null Returns the currently logged on users ID, or null if there is no user logged on
     */
    public function getUserId()
    {
        return $this->getConfig()->getUserId();
    }
    /**
     * Set the user id that should be impersonated. This can only be done when you are logged in as an admin user of your domain.
     * Attempting to set impersonation without having a valid logged on admin user will result in NotAuthorizedExceptions
     * This user id will be used as the active user in all subsequent requests made to the Ribbit server
     * @param string $user_id The user id of the user that will be impersonated
     */
    public function setImpersonatedUserId($user_id)
    {
        $this->getConfig()->setCustomHeaders(array("X-BT-Ribbit-SP-UserId" => $user_id), true);
    }
    /**
     * Clear the user id that is currently being impersonated by an administrative user.
     */
    public function clearImpersonatedUserId()
    {
        $this->getConfig()->removeCustomHeader("X-BT-Ribbit-SP-UserId");
    }
    /**
     * Returns the currently logged on user's id, or null
     *
     * @deprecated use getUserId()
     * @return string|null Returns the currently logged on users ID, or null if there is no user logged on
     */
    public function getCurrentUserId()
    {
        return $this->getUserId();
    }
    /**
     * Returns true when the currently logged on user session has expired (or not logged on)
     *
     * @return boolean|null
     */
    public function isSessionExpired()
    {
        return $this->getConfig()->sessionExpired();
    }
    /**
     * Returns the temporary Access Token for the currently logged on user
     *
     * @deprecated use getAccessSecret()
     * @return string
     */
    public function getUserAccessToken()
    {
        return $this->getAccessToken();
    }
    /**
     * Returns the temporary Access Token for the currently logged on user
     *
     *  @return string
     */
    public function getAccessToken()
    {
        return $this->getConfig()->getAccessToken();
    }
    /**
     * Returns the temporary Secret Key for the currently logged on user
     *
     *  @return string
     */
    public function getAccessSecret()
    {
        return $this->getConfig()->getAccessSecret();
    }
    /**
     * Returns the temporary Secret Key for the currently logged on user
     *
     * @deprecated use getAccessSecret()
     * @return string
     */
    public function getUserSecretKey()
    {
        return $this->getAccessSecret();
    }
    /**
     * Change the user access token at runtime. Note that access tokens do expire
     *
     * @param string $user_id an application id
     * @param string $user_name the
     * @param string $access_token a token representing a user session
     * @param string $access_secret a secret key for the user session
     */
    public function setUser($user_id, $user_name, $access_token, $access_secret)
    {
        $this->getConfig()->setUser($user_id, $user_name, $access_token, $access_secret);
    }
    /**
     * Change the user access token at runtime. Note that access tokens do expire
     *
     * @deprecated use setUser()
     *
     * @param string $user_id an application id
     * @param string $user_name the
     * @param string $access_token a token representing a user session
     * @param string $access_secret a secret key for the user session
     */
    public function setUserCredentials($user_id, $user_name, $access_token, $access_secret)
    {
        $this->setUser($user_id, $user_name, $access_token, $access_secret);
    }
    /**
     * Allows you to change the application credentials at runtime. Overrides any credentials specified
     * in ribbit_config.yml. Useful in multi tenant applications.
     *
     * Values for all of these can be found by an application owner at http://developer.ribbit.com
     *
     * @param string $consumer_secret a consumer secret
     * @param string $secret_key a shared secret key
     * @param string $application_id an application id
     * @param string $domain a domain name
     * @param string $account_id an account id
     */
    public function setApplicationCredentials($consumer_key, $secret_key, $application_id, $domain, $account_id)
    {
        $this->getConfig()->setApplicationCredentials($consumer_key, $secret_key, $application_id, $domain, $account_id);
    }
    /**
     * Returns your application ID
     *
     *  @return string|null Returns the ID of your application, if ribbit_config.php is configured correctly
     */
    public function getCurrentApplicationId()
    {
        return $this->getConfig()->getApplicationId();
    }
    /**
     * Executes a method, by passing in an associative array that contains a 'resource', a 'method' and another associative array of 'params'
     *
     * Use is as follows <br/> <pre>$users = Ribbit::getInstance().exec(array ("resource"=>"Users","method"=>"getUsers", params=>array ("start_index"=>0,"count"=>100)));</pre>
     *
     * Throws an InvalidArgumentException if the $request array does not refer to a valid resource and method
     *
     * May throw other Exceptions, depending on the resource and method that are being requested.
     *
     * @param array $request an associative array
     * @return mixed The result of this will vary based on the requested resource and method
     */
    public function exec($request)
    {
        try {
            $r_m = new ReflectionMethod("Ribbit", $request["resource"]);
        }
        catch(ReflectionException $e) {
            throw new InvalidArgumentException("Resource '" . $request["resource"] . "' does not exist.");
        }
        $o = $r_m->invoke($this);
        $r_o = new ReflectionObject($o);
        try {
            $r_m = $r_o->getMethod($request["method"]);
        }
        catch(ReflectionException $e) {
            throw new InvalidArgumentException("Method '" . $request["method"] . "' does not exist.");
        }
        $out = null;
        if (!isset($request["params"]) || !RibbitUtil::is_non_empty_array($request["params"])) {
            $out = $r_m->invoke($o);
        } else {
            $a = array();
            $params = $r_m->getParameters();
            foreach($params as $p) {
                $result = null;
                if (isset($request["params"][$p->name])) {
                    $result = $request["params"][$p->name];
                }
                if (RibbitUtil::is_assoc_array($result)) {
                    switch ($p->name) {
                    case "requestDtmf":
                    case "request_dtmf":
                        $maxDigits = isset($result["maxDigits"]) ? $result["maxDigits"] : (isset($result["max_digits"]) ? $result["max_digits"] : null);
                        $maxInterval = isset($result["maxInterval"]) ? $result["maxInterval"] : (isset($result["max_interval"]) ? $result["max_interval"] : null);
                        $timeOut = isset($result["timeOut"]) ? $result["timeOut"] : (isset($result["time_out"]) ? $result["time_out"] : null);
                        $stoptones = isset($result["stoptones"]) ? $result["stoptones"] : null;
                        $flush = isset($result["flush"]) ? $result["flush"] : null;
                        $result = new RibbitCallLegDtmfRequest($flush, $maxDigits, $stoptones, $timeOut, $maxInterval);
                        break;

                    case "play":
                        $media = array();
                        for ($i = 0; $i < count($result["media"]); $i++) {
                            $m = $result["media"][$i];
                            $type = isset($m["type"]) ? $m["type"] : null;
                            $value = isset($m["value"]) ? $m["value"] : null;
                            $offset = isset($m["offset"]) ? $m["offset"] : null;
                            $duration = isset($m["duration"]) ? $m["duration"] : null;
                            $media[] = new RibbitCallPlayMedia($type, $value, $offset, $duration);
                        }
                        $transactionId = isset($result["transactionId"]) ? $result["transactionId"] : (isset($result["transaction_id"]) ? $result["transaction_id"] : null);
                        $flush = isset($result["flush"]) ? $result["flush"] : null;
                        $stoptones = isset($result["stoptones"]) ? $result["stoptones"] : null;
                        $result = new RibbitCallPlayRequest($media, $transactionId, $stoptones, $flush);
                        break;

                    case "record":
                        $file = isset($result["file"]) ? $result["file"] : false;
                        $append = isset($result["append"]) ? $result["append"] : false;
                        $stoptones = isset($result["stoptones"]) ? $result["stoptones"] : null;
                        $duration = isset($result["duration"]) ? $result["duration"] : null;
                        $flush = isset($result["flush"]) ? $result["flush"] : null;
                        $result = new RibbitCallRecordRequest($file, $append, $flush, $duration, $stoptones);
                        break;

                    default:
                        break;
                    }
                }
                $args[] = $result;
            }
            $out = $r_m->invokeArgs($o, $args);
        }
        return $out;
    }
    /**
     * Login a User
     *
     * Most interactions with Ribbit require a logged on user.
     *
     * Throws a RibbitException if an error was returned from the service.
     *
     * Throws an InvalidUserNameOrPasswordException if the credentials are wrong
     *
     * The users' ID and OAuth access tokens retrieved by this class are stored in the PHP session.
     *
     * @param string $email The email address that is used to login (required)
     * @param string $password The users password (required)
     *
     * @return boolean If you successfully log on a user, returns true. Otherwise throws an exception
     */
    public function Login($email, $password)
    {
        $this->logoff();
        return RibbitUserAuthentication::AuthenticateUser($email, $password);
    }
    /**
     * Logoff the current user.
     *
     * Clears the session of OAuth access tokens that provide you with permission to manipulate user.
     * protected resources.
     * @param boolean $terminateRibbitSession - will additionally terminate the session on the Ribbit server. Defaults to true
     */
    public function Logoff($terminateRibbitSession = true)
    {
        return RibbitUserAuthentication::logoff($terminateRibbitSession);
    }
    /**
     * Begins the 3 legged authentication process. Will redirect to the Ribbit Mobile web site to authorize the request
     *
     * @param string $callback the url to which the user should be returned. Defaults to the current page if none is enteredd
     * @param boolean $redirect - whether the user should be automatically redirected to the Ribbit Mobile site to authorize the request. Defaults to true.
     * @return string If redirect is set to false
     */
    public function getAuthenticatedUser($callback = null, $redirect = true)
    {
        $this->Logoff();
        return RibbitUserAuthentication::getAuthenticatedUser($callback, $redirect);
    }
    /**
     * Checks the result of a 3 legged authentication request after control is returned.
     *
     * @return boolean|NotAuthorizedException
     */
    public function checkAuthenticatedUser()
    {
        return RibbitUserAuthentication::checkAuthenticatedUser();
    }
    /**
     * Returns true when there is an user currently authenticating on the Ribbit Mobile domain - normally while they have been redirected away from the application
     */
    public function isAuthenticationRequestActive()
    {
        $request_token = $this->getConfig()->getRequestToken();
        return $request_token != null && $request_token != "";
    }
    /**
     * Parses an HTTP Post sent from the Ribbit Server when events occurs
     *
     * @return a typed CallbackNotification or null if the response is not parseable
     */
    public function getCallbackNotification()
    {
        $out = null;
        if (isset($_SERVER['REQUEST_METHOD']) && strtolower($_SERVER['REQUEST_METHOD']) == 'post') {
            if (strtolower($_SERVER['CONTENT_TYPE']) == 'application/json') {
                $body = @file_get_contents('php://input');
                $out = RibbitCallbackNotification::parseCallBackNotification($body);
                return $out;
            }
        }
        throw new RibbitException("Could not parse the Ribbit Callback Notification", "0");
    }
    /**
     * Parses an HTTP Post sent from the Ribbit Server when events occurs
     *
     * @return CallbackNotification or null if the response is not parseable
     */
    public function parseCallbackNotification($body)
    {
        return RibbitCallbackNotification::parseCallBackNotification($body);
    }
    /**
     * Used when sending a 'GET' request to the Ribbit Server, and saving the response to a file.
     *
     * @param string $file A filename, used in conjuction with a folder and domain; Or, a relative (eg "media/domain/folder/filename") or full URI
     * @param string $folder The folder that the file is in (do not populate if using relative or full URIs)
     * @param string $file The domain (do not populate if using relative or full URIs), will default from initialization domain value
     * @return A Full URI that can be passed to a Media player
     */
    public function getStreamableUrl($file, $folder = null, $domain = null)
    {
        if (!isset($folder)) {
            if (strpos($file, $this->getConfig()->getRibbitEndpoint()) === 0) {
                $uri = substr($file, strlen($this->getConfig()->getRibbitEndpoint()), strlen($file) - strlen($this->getConfig()->getRibbitEndpoint()));
            } else if (strpos($file, "media") === 0) {
                $uri = $file;
            } else if (strpos($file, "media") === 1) {
                $uri = substr($file, 1, strlen($file) - 1);
            }
        } else {
            if (!isset($domain)) {
                $domain = $this->getConfig()->getDomain();
            }
            $domain = strpos($domain, "/") === 0 ? substr($domain, 1, strlen($domain) - 1) : $domain;
            $domain = strrpos($domain, "/") === strlen($domain) - 1 ? substr($domain, 0, strlen($domain) - 1) : $domain;
            $folder = strpos($folder, "/") === 0 ? substr($folder, 1, strlen($folder) - 1) : $folder;
            $folder = strrpos($folder, "/") === strlen($folder) - 1 ? substr($folder, 0, strlen($folder) - 1) : $folder;
            $file = strpos($file, "/") === 0 ? substr($file, 1, strlen($file) - 1) : $file;
            $file = strrpos($file, "/") === strlen($file) - 1 ? substr($file, 0, strlen($file) - 1) : $file;
            $uri = "media" . "/" . $domain . "/" . $folder . "/" . $file;
        }
        return RibbitSignedRequest::getInstance()->getStreamableUrl($uri);
    }
    /**
     * Provides access to the Applications resource
     *
     * @return RibbitApplication Returns an instance of RibbitApplication
     */
    public function Applications()
    {
        return RibbitApplication::getInstance();
    }
    /**
     * Provides access to the Calls resource
     *
     * @return RibbitCall Returns an instance of RibbitCall
     */
    public function Calls()
    {
        return RibbitCall::getInstance();
    }
    /**
     * Provides access to the Devices resource
     *
     * @return RibbitDevice Returns an instance of RibbitDevice
     */
    public function Devices()
    {
        return RibbitDevice::getInstance();
    }
    /**
     * Provides access to the Domains resource
     *
     * @return RibbitDomain Returns an instance of RibbitDomain
     */
    public function Domains()
    {
        return RibbitDomain::getInstance();
    }
    /**
     * Provides access to the Labels resource
     *
     * @return RibbitLabel Returns an instance of RibbitLabel
     */
    public function Labels()
    {
        return RibbitLabel::getInstance();
    }
    /**
     * Provides access to the Media resource
     *
     * @return RibbitMediaFiles Returns an instance of RibbitMediaFiles
     */
    public function Media()
    {
        return RibbitMediaFiles::getInstance();
    }
    /**
     * Provides access to the Messages resource
     *
     * @return RibbitMessage Returns an instance of RibbitMessage
     */
    public function Messages()
    {
        return RibbitMessage::getInstance();
    }
    /**
     * Provides access to the Services resource
     *
     * @return RibbitService Returns an instance of RibbitService
     */
    public function Services()
    {
        return RibbitService::getInstance();
    }
    /**
     * Provides access to the Tokens resource
     *
     * @return RibbitToken Returns an instance of RibbitToken
     */
    public function Tokens()
    {
        return RibbitToken::getInstance();
    }
    /**
     * Provides access to the Users resource
     *
     * @return RibbitUser Returns an instance of RibbitUser
     */
    public function Users()
    {
        return RibbitUser::getInstance();
    }
}
?>