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
 * Contains the RibbitService class
 *
 * @package Ribbit
 */
require_once ('RibbitSignedRequest.class.php');
require_once ('RibbitException.class.php');
require_once ('RibbitUtil.class.php');
/**
 *
 */
class RibbitService
{
    /**
     *
     */
    const SERVICE_TYPE_TRANSCRIPTION = "Transcription";
    /**
     * Normally accessed through Ribbit::getInstance()->Services()
     *
     * @return RibbitService An instance of this class
     */
    public static function getInstance()
    {
        static $instance;
        if (!isset($instance)) $instance = new RibbitService();
        return $instance;
    }
    private function RibbitService()
    {
    }
    /**
     *
     * This method calls the Ribbit service
     *
     * @return RibbitServiceResource An ordered array, each entry of which contains an associative array containing details about the ServiceResource
     */
    public function getServices()
    {
        $signed_request = RibbitSignedRequest::getInstance();
        if (Ribbit::getInstance()->getUserId() == null) {
            throw new AuthenticatedUserRequiredException();
        }
        $user_id = Ribbit::getInstance()->getConfig()->getActiveUserId();
        $uri = "services/" . $user_id;
        $result = $signed_request->get($uri);
        $result = json_decode($result, true);
        $result = $result['entry'];
        return $result;
    }
    /**
     *
     * This method calls the Ribbit service
     *
     * @param string $id  (required)
     * @param string[] $folders  (required)
     * @return RibbitServiceResource An associative array containing details about the ServiceResource
     */
    public function setServiceFolders($id, $folders)
    {
        $signed_request = RibbitSignedRequest::getInstance();
        if (Ribbit::getInstance()->getUserId() == null) {
            throw new AuthenticatedUserRequiredException();
        }
        $user_id = Ribbit::getInstance()->getConfig()->getActiveUserId();
        $exceptions = array();
        if (!RibbitUtil::is_valid_string($id)) {
            $exceptions[] = "id is required";
        }
        if (!is_array($folders)) {
            $exceptions[] = "folders is required";
        }
        if (count($exceptions) > 0) {
            throw new InvalidArgumentException(implode(";", $exceptions));
        }
        $vars = array();
        $vars["folders"] = $folders;
        $uri = "services/" . $user_id . "/" . $id;
        $result = $signed_request->put($vars, $uri);
        $result = json_decode($result, true);
        return $result["entry"];
    }
    /**
     *
     * This method calls the Ribbit service
     *
     * @param string $id  (required)
     * @return RibbitServiceResource An associative array containing details about the ServiceResource
     */
    public function clearServiceFolders($id)
    {
        return $this->setServiceFolders($id, array());
    }
    /**
     *
     * This method calls the Ribbit service
     *
     * @param string $id  (required)
     * @return RibbitServiceResource An associative array containing details about the ServiceResource
     */
    public function setVoicemailTranscriptionProvider($id)
    {
        $signed_request = RibbitSignedRequest::getInstance();
        if (Ribbit::getInstance()->getUserId() == null) {
            throw new AuthenticatedUserRequiredException();
        }
        $user_id = Ribbit::getInstance()->getConfig()->getActiveUserId();
        $exceptions = array();
        if (!RibbitUtil::is_valid_string($id)) {
            $exceptions[] = "id is required";
        }
        if (count($exceptions) > 0) {
            throw new InvalidArgumentException(implode(";", $exceptions));
        }
        $vars = array();
        $vars["voicemail"] = true;
        $uri = "services/" . $user_id . "/" . $id;
        $result = $signed_request->put($vars, $uri);
        $result = json_decode($result, true);
        return $result["entry"];
    }
}
?>