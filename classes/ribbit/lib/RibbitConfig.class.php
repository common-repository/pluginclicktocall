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
require_once "ext/spyc.php";
if (!isset($_SESSION)) {
    session_start();
}
RibbitConfig::getInstance();
/**
 * Holds configuration details. These are all specified in ribbit_config.yml.
 *
 * Application ID, Consumer Key and Secret Key can be changed at runtime. A method is exposed in the Ribbit class to do this easily
 * Http Proxy details are optional. Proxys with automatic configuration scripts (normally javascript files ending in .pac) are not supported.
 */
class RibbitConfig
{
    const ACCESS_TOKEN_IDLE_EXPIRY = 3540000; //expire after 59 idle minutes
    const ACCESS_TOKEN_EXPIRY = 86340000; //expire after 23 hours, 59 minutes
    private $using_session = null;
    private $custom_headers = array();
    /**
     * Normally accessed through Ribbit::getInstance()->getConfig()
     *
     * This method will load the ribbit_config.yml file initialization file only the first time it's called.
     * Subsequent calls will refer to the deserialized  values, some of which a developer can change.
     *
     * @return RibbitConfig The instance of this class
     */
    public static function getInstance()
    {
        static $instance;
        if (!isset($instance)) $instance = new RibbitConfig();
        if ($instance->sessionExpired()) {
            $instance->clearAccessToken(true);
        }
        return $instance;
    }
    private function RibbitConfig()
    {
        if (!isset($_SESSION["ribbit_config"])) {
            if (!file_exists(dirname(__FILE__) . "/../ribbit_config.yml")) {
                return;
            }
            $_SESSION["ribbit_config"] = spyc::YAMLLoad(dirname(__FILE__) . "/../ribbit_config.yml");
        }
    }
    /**
     * Can be used to set the entire config in one go.
     *
     * @param associative_array $config the configuration to use.
     */
    public function setConfig($config)
    {
        $_SESSION["ribbit_config"] = $config;
    }
    /**
     * Gets a configuration value
     *
     * @param string $group - the group in which the configuration value is stored
     * @param string $key - the name of the configuration value
     * @return string
     */
    public function getItem($group, $key)
    {
        $o = null;
        if (isset($_SESSION["ribbit_config"][$group]) && isset($_SESSION["ribbit_config"][$group][$key])) {
            $o = $_SESSION["ribbit_config"][$group][$key];
        }
        return $o;
    }
    /**
     * Retrieves the entire configuration in use
     *
     * @return associative_array
     */
    public function getConfig()
    {
        return $_SESSION["ribbit_config"];
    }
    private function saveItemInSession($group, $key, $value)
    {
        if (isset($_SESSION)) {
            $_SESSION["ribbit_config"][$group][$key] = $value;
        }
    }
    private function removeItemFromSession($group, $key)
    {
        if (isset($_SESSION["ribbit_config"][$group][$key])) {
            unset($_SESSION["ribbit_config"][$group][$key]);
        }
    }
    /**
     * Change the application being used. Will clear user session if the application or application secret is different
     *
     * @param string $consumer_key an application's consumer key
     * @param string $secret_key an applicaiton's secret key
     * @param string $application_id an application's identifier
     * @param string $domain the domain in which a user belongs
     * @param long $account_id the account number in which the application is billed - currently unused
     */
    public function setApplicationCredentials($consumer_key, $secret_key, $application_id, $domain, $account_id = 0)
    {
        if ($consumer_key != $this->getConsumerKey() || $secret_key != $this->getSecretKey()) {
            $this->clearAccessToken();
        }
        $this->setConsumerKey($consumer_key);
        $this->setSecretKey($secret_key);
        $this->setApplicationId($application_id);
        $this->setDomain($domain);
        $this->setAccountId($account_id);
    }
    /*
    * Returns a Name Value pair of additional headers that will be injected into each request
    *
    * @return associative_array the headers that will be injected into each request
    *
    */
    public function getCustomHeaders()
    {
        return $this->custom_headers;
    }
    /*
    * Remove all additional headers
    */
    public function resetCustomHeaders()
    {
        $this->custom_headers = array();
    }
    /*
    * Sets additional HTTP headers that will be used in requests to REST. Any keys that already exist will be overwritten
    *
    * @param associative_array $headers a name value pair collection of headers
    * @param boolean $reset defaults to true; whether to remove all currently set additional headers first
    */
    public function setCustomHeaders($headers, $reset = true)
    {
        if ($reset) {
            $this->resetCustomHeaders();
        }
        foreach($headers as $key => $val) {
            $this->custom_headers[$key] = $val;
        }
    }
    /*
    * Removes an additional HTTP header from the currently stored array of additional headers
    *
    * @param string $header the name of the additional header that should no longer be sent
    */
    public function removeCustomHeader($header)
    {
        if (array_key_exists($header, $this->custom_headers)) {
            unset($this->custom_headers[$header]);
        }
    }
    /**
     * Gets the application id currently being used
     *
     * @return string
     */
    public function getApplicationId()
    {
        return $this->getItem("ribbit", "application_id");
    }
    /**
     * Sets the application id to use
     *
     * @param string $application_id
     */
    public function setApplicationId($application_id)
    {
        $this->saveItemInSession("ribbit", "application_id", $application_id);
    }
    /**
     * Gets the account id currently being used
     *
     * @return string
     */
    public function getAccountId()
    {
        return $this->getItem("ribbit", "account_id");
    }
    /**
     * Sets the account id to use
     *
     * @param string $account_id
     */
    public function setAccountId($account_id)
    {
        $this->saveItemInSession("ribbit", "account_id", $account_id);
    }
    /**
     * Gets the domain currently being used
     *
     * @return string
     */
    public function getDomain()
    {
        return $this->getItem("ribbit", "domain");
    }
    /**
     * Sets the domain to use
     *
     * @param string
     */
    public function setDomain($domain)
    {
        $this->saveItemInSession("ribbit", "domain", $domain);
    }
    /**
     * Gets the consumer key currently being used
     *
     * @return string
     */
    public function getConsumerKey()
    {
        return $this->getItem("ribbit", "consumer_key");
    }
    /**
     * Sets the consumer key to use
     *
     * @param string
     */
    public function setConsumerKey($consumer_key)
    {
        $this->saveItemInSession("ribbit", "consumer_key", $consumer_key);
    }
    /**
     * Gets the secret key currently being used
     *
     * @return string
     */
    public function getSecretKey()
    {
        return $this->getItem("ribbit", "secret_key");
    }
    /**
     * Sets the secret key to use
     *
     * @return
     */
    public function setSecretKey($secret_key)
    {
        $this->saveItemInSession("ribbit", "secret_key", $secret_key);
    }
    /**
     * Set the Access token and secret to authenticate oAuth calls
     *
     * @param string $token the access token to use
     * @param string $token the access secret to use
     */
    public function setAccessToken($token, $secret)
    {
        $this->saveItemInSession("ribbit", "access_token", $token);
        $this->saveItemInSession("ribbit", "access_secret", $secret);
        $this->saveItemInSession("ribbit", "access_token_allocated", RibbitUtil::current_millis());
        $this->saveItemInSession("ribbit", "access_token_last_used", RibbitUtil::current_millis());
        $this->saveItemInSession("ribbit", "session_expired", null);
    }
    /**
     * Clear the current acces tokens
     */
    public function clearAccessToken($expired = false)
    {
        $this->saveItemInSession("ribbit", "access_token", null);
        $this->saveItemInSession("ribbit", "access_secret", null);
        if (!$expired) {
            $this->saveItemInSession("ribbit", "user_name", null);
            $this->saveItemInSession("ribbit", "user_id", null);
            $this->removeItemFromSession("ribbit", "access_token_allocated");
            $this->removeItemFromSession("ribbit", "access_token_last_used");
            $this->saveItemInSession("ribbit", "session_expired", null);
        } else {
            $this->saveItemInSession("ribbit", "session_expired", true);
        }
    }
    /**
     * Sets the request token details
     *
     * @param string $request_token
     * @param string $request_secret
     */
    public function setRequestToken($request_token, $request_secret, $request_callback_url)
    {
        $this->setAccessToken($request_token, $request_secret);
        $this->saveItemInSession("ribbit", "request_token", $request_token);
        $this->saveItemInSession("ribbit", "request_secret", $request_secret);
        $this->saveItemInSession("ribbit", "request_callback_url", $request_callback_url);
    }
    /**
     * Gets the access token
     *
     * @return string
     */
    public function getAccessToken($throw = false)
    {
        $token = $this->getItem("ribbit", "access_token");
        if ($throw && $this->sessionExpired()) {
            throw new SessionExpiredException();
        }
        return $token;
    }
    public function sessionExpired()
    {
        if (!is_null($this->getItem("ribbit", "session_expired"))) {
            return true;
        }
        if (is_null($this->getItem("ribbit", "access_token"))) {
            return false;
        }
        $token_allocated = $this->getItem("ribbit", "access_token_allocated");
        $token_last_used = $this->getItem("ribbit", "access_token_last_used");
        $now = RibbitUtil::current_millis();
        return ($token_allocated + RibbitConfig::ACCESS_TOKEN_EXPIRY < $now || $token_last_used + RibbitConfig::ACCESS_TOKEN_IDLE_EXPIRY < $now);
    }
    public function noteUsed()
    {
        $token = $this->getItem("ribbit", "access_token");
        if (!is_null($token)) {
            $this->saveItemInSession("ribbit", "access_token_last_used", RibbitUtil::current_millis());
        }
    }
    /**
     * Gets the user name
     *
     * @return string
     */
    public function getUserName()
    {
        return $this->getItem("ribbit", "user_name");
    }
    /**
     * Gets the id of the currently logged on user
     *
     * @return string
     */
    public function getUserId()
    {
        return $this->getItem("ribbit", "user_id");
    }
    /**
     * Gets the id of the currently active user - this will be the currently logged in user, unless there is an admin user logged in impersonating another user
     *
     * @return string
     */
    public function getActiveUserId()
    {
        return array_key_exists("X-BT-Ribbit-SP-UserId", $this->custom_headers) ? $this->custom_headers["X-BT-Ribbit-SP-UserId"] : $this->getUserId();
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
        $this->saveItemInSession("ribbit", "user_id", $user_id);
        $this->saveItemInSession("ribbit", "user_name", $user_name);
        if (is_null($access_token)) {
            $this->clearAccessToken();
        } else {
            $this->setAccessToken($access_token, $access_secret);
        }
        $this->saveItemInSession("ribbit", "request_token", "");
        $this->saveItemInSession("ribbit", "request_secret", "");
    }
    /**
     * Gets the request token in use during a three legged authentication process
     *
     * @return string
     */
    public function getRequestToken()
    {
        return $this->getItem("ribbit", "request_token");
    }
    /**
     * Gets the request secret in use during a three legged authentication process
     *
     * @return string
     */
    public function getRequestSecret()
    {
        return $this->getItem("ribbit", "request_secret");
    }
    /**
     * Gets the url to which a user will be returned during a three legged authentication process
     *
     * @return string
     */
    public function getRequestCallbackUrl()
    {
        return $this->getItem("ribbit", "request_callback_url");
    }
    /**
     * Gets the access token
     *
     * @return string
     */
    public function getAccessSecret()
    {
        return $this->getItem("ribbit", "access_secret");
    }
    /**
     * Gets the ribbit endpoint, defined in ribbit_config.yml
     *
     * @return string
     */
    public function getRibbitEndpoint()
    {
        $e = $this->getItem("ribbit", "endpoint");
        if (substr($e, strlen($e) - 1, 1) != "/") {
            $e = $e . "/";
            $this->setRibbitEndpoint($e);
        }
        return $e;
    }
    /**
     * Sets the ribbit endpoint
     *
     * @return string
     */
    public function setRibbitEndpoint($endpoint)
    {
        $this->saveItemInSession("ribbit", "endpoint", $endpoint);
    }
    /**
     * Gets the http proxy address, defined in ribbit_config.yml
     *
     * @return string
     */
    public function getHttpProxyAddress()
    {
        return $this->getItem("http", "proxy_address");
    }
    /**
     * Gets the username to use with an authenticated http proxy, defined in ribbit_config.yml
     *
     * @return string
     */
    public function getHttpProxyUsername()
    {
        return $this->getItem("http", "proxy_username");
    }
    /**
     * Gets the password to use with an authenticated http proxy, defined in ribbit_config.yml
     *
     * @return string
     */
    public function getHttpProxyPassword()
    {
        return $this->getItem("http", "proxy_password");
    }
    /**
     * Defines whether to log http requests made to Ribbit to the syslog (or event viewer in Windows)
     *
     * @return string
     */
    public function getLog()
    {
        return $this->getItem("ribbit", "log");
    }
}
?>