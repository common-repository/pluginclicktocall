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
 * Contains the RibbitUserAuthentication class
 *
 * @package Ribbit
 */
require_once ('RibbitConfig.class.php');
require_once ('RibbitSignedRequest.class.php');
require_once ('RibbitException.class.php');
if (!isset($_SESSION)) {
    session_start();
}
if (isset($_SESSION["ribbit_user_object"])) {
    $u = $_SESSION["ribbit_user_object"];
    RibbitConfig::getInstance()->setUser($u["guid"], $u["login"], $u["token"], $u["secret"]);
}
/**
 * Used to retrieve an oAuth token that allows applications on behalf of a user.
 *
 * @package Ribbit
 * @version 1.3.0
 * @author BT/Ribbit
 */
class RibbitUserAuthentication
{
    /**
     * Normally accessed through Ribbit::getInstance()->Login($email,$password)
     *
     * Throws a RibbitException if an error was returned from the service.
     *
     * Throws an InvalidUserNameOrPasswordException if the credentials are wrong
     *
     * @param string $login The user name that is required to login (required)
     * @param string $password The users password (required)
     * @return boolean if the authentication works ok and tokens are set in session
     */
    static function AuthenticateUser($login, $password)
    {
        unset($_SESSION["ribbit_user_object"]);
        RibbitUserAuthentication::logoff(true);
        try {
            $t = RibbitSignedRequest::getInstance()->post(null, "login", $login, $password);
            $t = explode("&", $t);
            $u = explode("=", $t[0]);
            $access_token = $u[1];
            $u = explode("=", $t[1]);
            $access_secret = $u[1];
            $u = explode("=", $t[2]);
            $current_user = $u[1];
            RibbitConfig::getInstance()->setUser($current_user, $login, $access_token, $access_secret);
            $_SESSION["ribbit_user_object"] = array("token" => $access_token, "secret" => $access_secret, "login" => $login, "guid" => $current_user);
        }
        catch(RibbitException $e) {
            RibbitUserAuthentication::logoff(false);
            if ($e->getStatus() == 0) {
                throw $e;
            } else {
                throw new InvalidUserNameOrPasswordException();
            }
        }
        return true;
    }
    /**
     * Call this method to authenticate a user on the Ribbit Mobile domain
     *
     * You must have called Ribbit.init using a secret key and consumer key for your application, and that must be a guest on the Ribbit Mobile domain
     * Calling this will start a three legged oAuth process. The user will be directed to
     * the Ribbit For Mobile sign in page, and returned to this page when they have either approved or denied
     * access for your application to use their account.
     *
     * You may specify a callback function by name, that will be invoked when control is returned to your page.
     * When control is returned to your application, please check the value of Ribbit.isLoggedIn, which will be true if the user approved
     * your authentication request, otherwise it will be false
     *
     *
     * @param callbackFunctionName string: The name of a function to be called when the page reloads - note that this must be the name of a function, and not a pointer - as the page redirects function pointers will be lost when the page reloads.
     *
     * @public
     * @function
     */
    static function getAuthenticatedUser($callback_url, $redirect)
    {
        RibbitUserAuthentication::logoff(true);
        if (is_null($callback_url) && $redirect) {
            $callback_url = "";
        }
        $callback_url = RibbitUtil::redirect_uri_builder($callback_url);
        $response = RibbitSignedRequest::getInstance()->post(null, "request_token");
        $t = explode("&", $response);
        $u = explode("=", $t[0]);
        $request_token = $u[1];
        $u = explode("=", $t[1]);
        $request_secret = $u[1];
        RibbitConfig::getInstance()->setRequestToken($request_token, $request_secret, $callback_url);
        $callback_query_param = ($callback_url != null) ? "&oauth_callback=" . $callback_url : "";
        $redirect_url = RibbitConfig::getInstance()->getRibbitEndpoint() . "oauth/display_token.html?oauth_token=" . $request_token . $callback_query_param;
        if ($redirect) {
            header('Location: ' . $redirect_url);
            exit;
        }
        return $redirect_url;
    }
    static function checkAuthenticatedUser()
    {
        $valid = null;
        $query_string = $_SERVER["QUERY_STRING"];
        if (isset($query_string)) {
            $pos = strpos($query_string, "oauth_approval=denied");
            if ($pos > 0 || $pos === 0) {
                RibbitConfig::getInstance()->setRequestToken(null, null, null);
                $valid = false;
            }
        }
        if (is_null($valid)) {
            $response = RibbitSignedRequest::getInstance()->post(null, "access_token");
            $t = explode("&", $response);
            $u = explode("=", $t[0]);
            $access_token = $u[1];
            $u = explode("=", $t[1]);
            $access_secret = $u[1];
            $u = explode("=", $t[2]);
            $user_id = $u[1];
            $u = explode("=", $t[3]);
            $user_details = $u[1];
            $user_details = urldecode($user_details);
            $t = explode(':', $user_details);
            $domain = $t[0];
            $user_name = $t[1];
            RibbitConfig::getInstance()->setDomain($domain);
            RibbitConfig::getInstance()->setUser($user_id, $user_name, $access_token, $access_secret);
            $valid = true;
        }
        return $valid;
    }
    /**
     * Removes Ribbit user session details from the PHP session. Optionally revokes the session on the Ribbit Server
     * @param boolean $revoke whether revoke the session on the Ribbit Server
     */
    static function logoff($revoke)
    {
        if (isset($revoke) && $revoke && Ribbit::getInstance()->isLoggedIn()) {
            RibbitSignedRequest::getInstance()->post(null, "logout");
        }
        RibbitConfig::getInstance()->setUser(null, null, null, null);
        RibbitConfig::getInstance()->setRequestToken(null, null, null);
        unset($_SESSION["ribbit_user_object"]);
    }
}
?>