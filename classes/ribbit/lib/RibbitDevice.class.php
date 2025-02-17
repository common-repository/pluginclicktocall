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
 * Contains the RibbitDevice class
 *
 * @package Ribbit
 */
require_once ('RibbitSignedRequest.class.php');
require_once ('RibbitException.class.php');
require_once ('RibbitUtil.class.php');
/**
 * A Device represents different addresses through which a User may be contacted.
 * 			Devices are represented where possible as Uniform Resource Identifiers (URI) where the type is determined by the URI scheme.
 * 			Examples include: mailto:, tel:, SIP:, Skype:, MSN:, and ribbit:
 */
class RibbitDevice
{
    /**
     *
     */
    const LOCALE_GBR = "GBR";
    /**
     *
     */
    const LOCALE_USA = "USA";
    /**
     * Normally accessed through Ribbit::getInstance()->Devices()
     *
     * @return RibbitDevice An instance of this class
     */
    public static function getInstance()
    {
        static $instance;
        if (!isset($instance)) $instance = new RibbitDevice();
        return $instance;
    }
    private function RibbitDevice()
    {
    }
    /**
     * Allocates a specified Inbound Number to the current User
     * This method calls the Ribbit service
     *
     * @param string $number  (required)
     * @return string An inboundNumber identifier
     */
    public function allocateInboundNumber($number)
    {
        $signed_request = RibbitSignedRequest::getInstance();
        $exceptions = array();
        if (!RibbitUtil::is_valid_string($number)) {
            $exceptions[] = "number is required";
        }
        if (count($exceptions) > 0) {
            throw new InvalidArgumentException(implode(";", $exceptions));
        }
        $result = $this->createDevice("@purpose/tel:" . $number, "Purpose number", null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null);
        return $result;
    }
    /**
     * Registers a new device to the current User
     * This method calls the Ribbit service
     *
     * @param string $id Unique Device identifier prefixed by schema to reflect device type (e.g. mailto:foo@bar.com) (required)
     * @param string $name Name to refer to this Device (required)
     * @param string $label A label for the Device (optional)
     * @param boolean $callme This Device can be used as an inbound 'CallMe' number (optional)
     * @param boolean $notifyvm Send notifications to this Device on new voicemails (optional)
     * @param boolean $callbackreachme This Device can be used as 'reach me' number (optional)
     * @param boolean $mailtext Include transcribed message content in notifications if available (optional)
     * @param boolean $shared This Device is shared by other people (optional)
     * @param boolean $notifymissedcall Send notifications to this device on missed calls (optional)
     * @param boolean $showcalled Show the callerID of the person called in the notification (optional)
     * @param boolean $answersecurity  (optional)
     * @param boolean $notifytranscription send notifications to this Device on new transcriptions (optional)
     * @param boolean $attachmessage Send voicemail file as an attachment to email notifications (optional)
     * @param boolean $usewave Send voicemail files in WAV format rather than MP3 (optional)
     * @param string $key Security access code to enable this device (optional)
     * @param boolean $ringstatus Ring this Device when an inbound call arrives (optional)
     * @param string $verify_by Populate with 'ccfTest' to request a conditional call forwarding verification test (optional)
     * @param boolean $auto_answer Automatically answer this inbound device (optional)
     * @param boolean $allow_c_c_f Allow conditional call forwarding for this device (optional)
     * @return string A device identifier
     */
    public function createDevice($id, $name, $label = null, $callme = null, $notifyvm = null, $callbackreachme = null, $mailtext = null, $shared = null, $notifymissedcall = null, $showcalled = null, $answersecurity = null, $notifytranscription = null, $attachmessage = null, $usewave = null, $key = null, $ringstatus = null, $verify_by = null, $auto_answer = null, $allow_c_c_f = null)
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
        if (!RibbitUtil::is_valid_string($name)) {
            $exceptions[] = "name is required";
        }
        if (!RibbitUtil::is_valid_string_if_defined($label)) {
            $exceptions[] = "When defined, label must be a string of one or more characters";
        }
        if (!RibbitUtil::is_valid_bool_if_defined($callme)) {
            $exceptions[] = "When defined, callme must be boolean";
        }
        if (!RibbitUtil::is_valid_bool_if_defined($notifyvm)) {
            $exceptions[] = "When defined, notifyvm must be boolean";
        }
        if (!RibbitUtil::is_valid_bool_if_defined($callbackreachme)) {
            $exceptions[] = "When defined, callbackreachme must be boolean";
        }
        if (!RibbitUtil::is_valid_bool_if_defined($mailtext)) {
            $exceptions[] = "When defined, mailtext must be boolean";
        }
        if (!RibbitUtil::is_valid_bool_if_defined($shared)) {
            $exceptions[] = "When defined, shared must be boolean";
        }
        if (!RibbitUtil::is_valid_bool_if_defined($notifymissedcall)) {
            $exceptions[] = "When defined, notifymissedcall must be boolean";
        }
        if (!RibbitUtil::is_valid_bool_if_defined($showcalled)) {
            $exceptions[] = "When defined, showcalled must be boolean";
        }
        if (!RibbitUtil::is_valid_bool_if_defined($answersecurity)) {
            $exceptions[] = "When defined, answersecurity must be boolean";
        }
        if (!RibbitUtil::is_valid_bool_if_defined($notifytranscription)) {
            $exceptions[] = "When defined, notifytranscription must be boolean";
        }
        if (!RibbitUtil::is_valid_bool_if_defined($attachmessage)) {
            $exceptions[] = "When defined, attachmessage must be boolean";
        }
        if (!RibbitUtil::is_valid_bool_if_defined($usewave)) {
            $exceptions[] = "When defined, usewave must be boolean";
        }
        if (!RibbitUtil::is_valid_string_if_defined($key)) {
            $exceptions[] = "When defined, key must be a string of one or more characters";
        }
        if (!RibbitUtil::is_valid_bool_if_defined($ringstatus)) {
            $exceptions[] = "When defined, ringstatus must be boolean";
        }
        if (!RibbitUtil::is_valid_string_if_defined($verify_by)) {
            $exceptions[] = "When defined, verify_by must be a string of one or more characters";
        }
        if (!RibbitUtil::is_valid_bool_if_defined($auto_answer)) {
            $exceptions[] = "When defined, auto_answer must be boolean";
        }
        if (!RibbitUtil::is_valid_bool_if_defined($allow_c_c_f)) {
            $exceptions[] = "When defined, allow_c_c_f must be boolean";
        }
        if (count($exceptions) > 0) {
            throw new InvalidArgumentException(implode(";", $exceptions));
        }
        $vars = array();
        $vars["id"] = $id;
        $vars["name"] = $name;
        if (isset($label)) {
            $vars["label"] = $label;
        }
        if (isset($callme)) {
            $vars["callme"] = $callme;
        }
        if (isset($notifyvm)) {
            $vars["notifyvm"] = $notifyvm;
        }
        if (isset($callbackreachme)) {
            $vars["callbackreachme"] = $callbackreachme;
        }
        if (isset($mailtext)) {
            $vars["mailtext"] = $mailtext;
        }
        if (isset($shared)) {
            $vars["shared"] = $shared;
        }
        if (isset($notifymissedcall)) {
            $vars["notifymissedcall"] = $notifymissedcall;
        }
        if (isset($showcalled)) {
            $vars["showcalled"] = $showcalled;
        }
        if (isset($answersecurity)) {
            $vars["answersecurity"] = $answersecurity;
        }
        if (isset($notifytranscription)) {
            $vars["notifytranscription"] = $notifytranscription;
        }
        if (isset($attachmessage)) {
            $vars["attachmessage"] = $attachmessage;
        }
        if (isset($usewave)) {
            $vars["usewave"] = $usewave;
        }
        if (isset($key)) {
            $vars["key"] = $key;
        }
        if (isset($ringstatus)) {
            $vars["ringstatus"] = $ringstatus;
        }
        if (isset($verify_by)) {
            $vars["verifyBy"] = $verify_by;
        }
        if (isset($auto_answer)) {
            $vars["autoAnswer"] = $auto_answer;
        }
        if (isset($allow_c_c_f)) {
            $vars["allowCCF"] = $allow_c_c_f;
        }
        $uri = "devices/" . $user_id;
        $result = $signed_request->post($vars, $uri);
        return RibbitUtil::get_id_from_uri($result);
    }
    /**
     * Registers a new inbound device to the current User
     * This method calls the Ribbit service
     *
     * @param string $id Unique Device identifier prefixed by schema to reflect device type (e.g. mailto:foo@bar.com) (required)
     * @param string $name Name to refer to this Device (required)
     * @return string A device identifier
     */
    public function createInboundDevice($id, $name)
    {
        return $this->createDevice($id, $name, null, true, null, null, null, null, null, null, null, null, null, null, null, true, null, null, null);
    }
    /**
     * Registers a new outbound device to the current User
     * This method calls the Ribbit service
     *
     * @param string $id Unique Device identifier prefixed by schema to reflect device type (e.g. mailto:foo@bar.com) (required)
     * @param string $name Name to refer to this Device (required)
     * @return string A device identifier
     */
    public function createOutboundDevice($id, $name)
    {
        return $this->createDevice($id, $name, null, null, null, true, null, null, null, null, null, null, null, null, null, true, null, null, null);
    }
    /**
     * Registers a new Inbound Number for the current User
     * This method calls the Ribbit service
     *
     * @param string $locale A country code. Currently 'GBR' and 'USA' are supported, defaults to 'USA' (required)
     * @param string $name Name to refer to this Device (required)
     * @return string An inboundNumber identifier
     */
    public function createInboundNumber($locale = "USA", $name)
    {
        $signed_request = RibbitSignedRequest::getInstance();
        $exceptions = array();
        if (!RibbitUtil::is_valid_string($locale)) {
            $exceptions[] = "locale is required";
        }
        if (!RibbitUtil::is_valid_string($name)) {
            $exceptions[] = "name is required";
        }
        if (count($exceptions) > 0) {
            throw new InvalidArgumentException(implode(";", $exceptions));
        }
        $result = $this->createDevice("@purpose/" . $locale, $name, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null);
        return $result;
    }
    /**
     * Registers a new Inbound SMS Number for the current User
     * This method calls the Ribbit service
     *
     * @param string $locale A country code. Currently 'GBR' and 'USA' are supported, defaults to 'USA' (required)
     * @param string $name Name to refer to this Device (required)
     * @return string A  identifier
     */
    public function createInboundSmsNumber($locale = "USA", $name)
    {
        $signed_request = RibbitSignedRequest::getInstance();
        $exceptions = array();
        if (!RibbitUtil::is_valid_string($locale)) {
            $exceptions[] = "locale is required";
        }
        if (!RibbitUtil::is_valid_string($name)) {
            $exceptions[] = "name is required";
        }
        if (count($exceptions) > 0) {
            throw new InvalidArgumentException(implode(";", $exceptions));
        }
        $result = $this->createDevice("@sms/" . $locale, $name, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null);
        return $result;
    }
    /**
     * Registers a new mail device to the current User
     * This method calls the Ribbit service
     *
     * @param string $email_address Email Address that will be prefixed by "mailto:" to form the Device Identifier (required)
     * @param string $name Name to refer to this Device (required)
     * @return string A  identifier
     */
    public function createMailDevice($email_address, $name)
    {
        $signed_request = RibbitSignedRequest::getInstance();
        $exceptions = array();
        if (!RibbitUtil::is_valid_string($email_address)) {
            $exceptions[] = "email_address is required";
        }
        if (!RibbitUtil::is_valid_string($name)) {
            $exceptions[] = "name is required";
        }
        if (count($exceptions) > 0) {
            throw new InvalidArgumentException(implode(";", $exceptions));
        }
        $result = $this->createDevice("mailto:" . $email_address, $name, null, null, null, null, null, null, null, null, null, null, null, null, null, null, "mailCheck", null, null);
        return $result;
    }
    /**
     * Finds a selection of available Inbound Numbers based on a search string and specified Locale.  These numbers can then be allocated to the current User by using  {@link allocateInboundNumber}.
     * This method calls the Ribbit service
     *
     * @param string $locale A country code. Currently 'GBR' and 'USA' are supported, defaults to 'USA' (required)
     * @param string $search_filter A pattern to search for in available Inbound Numbers (required)
     * @param int $max_results The maximum number of results to return when getting a list of available Inbound Numbers (required)
     * @return RibbitInboundNumber An ordered array, each entry of which contains an associative array containing details about the InboundNumber
     */
    public function findAvailableInboundNumberSelectionForLocale($locale, $search_filter, $max_results)
    {
        $signed_request = RibbitSignedRequest::getInstance();
        if (Ribbit::getInstance()->getUserId() == null) {
            throw new AuthenticatedUserRequiredException();
        }
        $user_id = Ribbit::getInstance()->getConfig()->getActiveUserId();
        $exceptions = array();
        if (!RibbitUtil::is_valid_string($locale)) {
            $exceptions[] = "locale is required";
        }
        if (isset($search_filter)) {
            $search_filter = "" . $search_filter;
        }
        if (!RibbitUtil::is_valid_string($search_filter)) {
            $exceptions[] = "search_filter is required";
        }
        if (!RibbitUtil::is_positive_integer($max_results)) {
            $exceptions[] = "max_results is required";
        }
        if (count($exceptions) > 0) {
            throw new InvalidArgumentException(implode(";", $exceptions));
        }
        $uri = "devices/@purpose?maxResults=" . $max_results . "&filterBy=status,location,id&filterOp=all&filterValue=available," . $locale . "," . $search_filter;
        $result = $signed_request->get($uri);
        $result = json_decode($result, true);
        $result = $result['entry'];
        return $result;
    }
    /**
     * Gets a selection of available Inbound Numbers.  These numbers can then be allocated to the current User by using  {@link allocateInboundNumber}.
     * This method calls the Ribbit service
     *
     * @param int $max_results The maximum number of results to return when getting a list of available Inbound Numbers (required)
     * @return RibbitInboundNumber An ordered array, each entry of which contains an associative array containing details about the InboundNumber
     */
    public function getAvailableInboundNumberSelection($max_results)
    {
        $signed_request = RibbitSignedRequest::getInstance();
        if (Ribbit::getInstance()->getUserId() == null) {
            throw new AuthenticatedUserRequiredException();
        }
        $user_id = Ribbit::getInstance()->getConfig()->getActiveUserId();
        $exceptions = array();
        if (!RibbitUtil::is_positive_integer($max_results)) {
            $exceptions[] = "max_results is required";
        }
        if (count($exceptions) > 0) {
            throw new InvalidArgumentException(implode(";", $exceptions));
        }
        $uri = "devices/@purpose?maxResults=" . $max_results . "&filterBy=status&filterValue=available";
        $result = $signed_request->get($uri);
        $result = json_decode($result, true);
        $result = $result['entry'];
        return $result;
    }
    /**
     * Gets a selection of available Inbound Numbers for the specified Locale.  These numbers can then be allocated to the current User by using  {@link allocateInboundNumber}.
     * This method calls the Ribbit service
     *
     * @param string $locale A country code. Currently 'GBR' and 'USA' are supported, defaults to 'USA' (required)
     * @param int $max_results The maximum number of results to return when getting a list of available Inbound Numbers (required)
     * @return RibbitInboundNumber An ordered array, each entry of which contains an associative array containing details about the InboundNumber
     */
    public function getAvailableInboundNumberSelectionForLocale($locale, $max_results)
    {
        $signed_request = RibbitSignedRequest::getInstance();
        if (Ribbit::getInstance()->getUserId() == null) {
            throw new AuthenticatedUserRequiredException();
        }
        $user_id = Ribbit::getInstance()->getConfig()->getActiveUserId();
        $exceptions = array();
        if (!RibbitUtil::is_valid_string($locale)) {
            $exceptions[] = "locale is required";
        }
        if (!RibbitUtil::is_positive_integer($max_results)) {
            $exceptions[] = "max_results is required";
        }
        if (count($exceptions) > 0) {
            throw new InvalidArgumentException(implode(";", $exceptions));
        }
        $uri = "devices/@purpose?maxResults=" . $max_results . "&filterBy=status,location&filterOp=all&filterValue=available," . $locale;
        $result = $signed_request->get($uri);
        $result = json_decode($result, true);
        $result = $result['entry'];
        return $result;
    }
    /**
     * Gets details about the Device
     * This method calls the Ribbit service
     *
     * @param string $device_id Unique Device identifier prefixed by schema to reflect device type (e.g. mailto:foo@bar.com) (required)
     * @return RibbitDeviceResource An associative array containing details about the DeviceResource
     */
    public function getDevice($device_id)
    {
        $signed_request = RibbitSignedRequest::getInstance();
        if (Ribbit::getInstance()->getUserId() == null) {
            throw new AuthenticatedUserRequiredException();
        }
        $user_id = Ribbit::getInstance()->getConfig()->getActiveUserId();
        $exceptions = array();
        if (!RibbitUtil::is_valid_string($device_id)) {
            $exceptions[] = "device_id is required";
        }
        if (count($exceptions) > 0) {
            throw new InvalidArgumentException(implode(";", $exceptions));
        }
        $uri = "devices/" . $user_id . "/" . $device_id;
        $result = $signed_request->get($uri);
        $result = json_decode($result, true);
        return $result["entry"];
    }
    /**
     * Get a collection of Devices belonging to the current User
     * This method calls the Ribbit service
     *
     * @return RibbitDeviceResource An ordered array, each entry of which contains an associative array containing details about the DeviceResource
     */
    public function getDevices()
    {
        $signed_request = RibbitSignedRequest::getInstance();
        if (Ribbit::getInstance()->getUserId() == null) {
            throw new AuthenticatedUserRequiredException();
        }
        $user_id = Ribbit::getInstance()->getConfig()->getActiveUserId();
        $uri = "devices/" . $user_id;
        $result = $signed_request->get($uri);
        $result = json_decode($result, true);
        $result = $result['entry'];
        return $result;
    }
    /**
     * Deregisters a Device belonging to the current User
     * This method calls the Ribbit service
     *
     * @param string $device_id Unique Device identifier prefixed by schema to reflect device type (e.g. mailto:foo@bar.com) (required)
     * @return boolean true if successfully removed
     */
    public function removeDevice($device_id)
    {
        $signed_request = RibbitSignedRequest::getInstance();
        if (Ribbit::getInstance()->getUserId() == null) {
            throw new AuthenticatedUserRequiredException();
        }
        $user_id = Ribbit::getInstance()->getConfig()->getActiveUserId();
        $exceptions = array();
        if (!RibbitUtil::is_valid_string($device_id)) {
            $exceptions[] = "device_id is required";
        }
        if (count($exceptions) > 0) {
            throw new InvalidArgumentException(implode(";", $exceptions));
        }
        $uri = "devices/" . $user_id . "/" . $device_id;
        $result = $signed_request->delete($uri);
        return true;
    }
    /**
     * Updates details about a Device, and flags which control how it interacts with the Ribbit Platform
     * This method calls the Ribbit service
     *
     * @param string $device_id Unique Device identifier prefixed by schema to reflect device type (e.g. mailto:foo@bar.com) (required)
     * @param string $name Name to refer to this Device (optional)
     * @param string $label A label for the Device (optional)
     * @param boolean $callme This Device can be used as an inbound 'CallMe' number (optional)
     * @param boolean $notifyvm Send notifications to this Device on new voicemails (optional)
     * @param boolean $callbackreachme This Device can be used as 'reach me' number (optional)
     * @param boolean $mailtext Include transcribed message content in notifications if available (optional)
     * @param boolean $shared This Device is shared by other people (optional)
     * @param boolean $notifymissedcall Send notifications to this device on missed calls (optional)
     * @param boolean $showcalled Show the callerID of the person called in the notification (optional)
     * @param boolean $answersecurity  (optional)
     * @param boolean $notifytranscription send notifications to this Device on new transcriptions (optional)
     * @param boolean $attachmessage Send voicemail file as an attachment to email notifications (optional)
     * @param boolean $usewave Send voicemail files in WAV format rather than MP3 (optional)
     * @param string $key Security access code to enable this device (optional)
     * @param boolean $ringstatus Ring this Device when an inbound call arrives (optional)
     * @param string $verify_by Populate with 'ccfTest' to request a conditional call forwarding verification test (optional)
     * @param boolean $auto_answer Automatically answer this inbound device (optional)
     * @param boolean $allow_c_c_f Allow conditional call forwarding for this device (optional)
     * @return RibbitDeviceResource An associative array containing details about the DeviceResource
     */
    public function updateDevice($device_id, $name = null, $label = null, $callme = null, $notifyvm = null, $callbackreachme = null, $mailtext = null, $shared = null, $notifymissedcall = null, $showcalled = null, $answersecurity = null, $notifytranscription = null, $attachmessage = null, $usewave = null, $key = null, $ringstatus = null, $verify_by = null, $auto_answer = null, $allow_c_c_f = null)
    {
        $signed_request = RibbitSignedRequest::getInstance();
        if (Ribbit::getInstance()->getUserId() == null) {
            throw new AuthenticatedUserRequiredException();
        }
        $user_id = Ribbit::getInstance()->getConfig()->getActiveUserId();
        $exceptions = array();
        if (!RibbitUtil::is_valid_string($device_id)) {
            $exceptions[] = "device_id is required";
        }
        if (!RibbitUtil::is_valid_string_if_defined($name)) {
            $exceptions[] = "When defined, name must be a string of one or more characters";
        }
        if (!RibbitUtil::is_valid_string_if_defined($label)) {
            $exceptions[] = "When defined, label must be a string of one or more characters";
        }
        if (!RibbitUtil::is_valid_bool_if_defined($callme)) {
            $exceptions[] = "When defined, callme must be boolean";
        }
        if (!RibbitUtil::is_valid_bool_if_defined($notifyvm)) {
            $exceptions[] = "When defined, notifyvm must be boolean";
        }
        if (!RibbitUtil::is_valid_bool_if_defined($callbackreachme)) {
            $exceptions[] = "When defined, callbackreachme must be boolean";
        }
        if (!RibbitUtil::is_valid_bool_if_defined($mailtext)) {
            $exceptions[] = "When defined, mailtext must be boolean";
        }
        if (!RibbitUtil::is_valid_bool_if_defined($shared)) {
            $exceptions[] = "When defined, shared must be boolean";
        }
        if (!RibbitUtil::is_valid_bool_if_defined($notifymissedcall)) {
            $exceptions[] = "When defined, notifymissedcall must be boolean";
        }
        if (!RibbitUtil::is_valid_bool_if_defined($showcalled)) {
            $exceptions[] = "When defined, showcalled must be boolean";
        }
        if (!RibbitUtil::is_valid_bool_if_defined($answersecurity)) {
            $exceptions[] = "When defined, answersecurity must be boolean";
        }
        if (!RibbitUtil::is_valid_bool_if_defined($notifytranscription)) {
            $exceptions[] = "When defined, notifytranscription must be boolean";
        }
        if (!RibbitUtil::is_valid_bool_if_defined($attachmessage)) {
            $exceptions[] = "When defined, attachmessage must be boolean";
        }
        if (!RibbitUtil::is_valid_bool_if_defined($usewave)) {
            $exceptions[] = "When defined, usewave must be boolean";
        }
        if (!RibbitUtil::is_valid_string_if_defined($key)) {
            $exceptions[] = "When defined, key must be a string of one or more characters";
        }
        if (!RibbitUtil::is_valid_bool_if_defined($ringstatus)) {
            $exceptions[] = "When defined, ringstatus must be boolean";
        }
        if (!RibbitUtil::is_valid_string_if_defined($verify_by)) {
            $exceptions[] = "When defined, verify_by must be a string of one or more characters";
        }
        if (!RibbitUtil::is_valid_bool_if_defined($auto_answer)) {
            $exceptions[] = "When defined, auto_answer must be boolean";
        }
        if (!RibbitUtil::is_valid_bool_if_defined($allow_c_c_f)) {
            $exceptions[] = "When defined, allow_c_c_f must be boolean";
        }
        if (count($exceptions) > 0) {
            throw new InvalidArgumentException(implode(";", $exceptions));
        }
        $vars = array();
        if (isset($name)) {
            $vars["name"] = $name;
        }
        if (isset($label)) {
            $vars["label"] = $label;
        }
        if (isset($callme)) {
            $vars["callme"] = $callme;
        }
        if (isset($notifyvm)) {
            $vars["notifyvm"] = $notifyvm;
        }
        if (isset($callbackreachme)) {
            $vars["callbackreachme"] = $callbackreachme;
        }
        if (isset($mailtext)) {
            $vars["mailtext"] = $mailtext;
        }
        if (isset($shared)) {
            $vars["shared"] = $shared;
        }
        if (isset($notifymissedcall)) {
            $vars["notifymissedcall"] = $notifymissedcall;
        }
        if (isset($showcalled)) {
            $vars["showcalled"] = $showcalled;
        }
        if (isset($answersecurity)) {
            $vars["answersecurity"] = $answersecurity;
        }
        if (isset($notifytranscription)) {
            $vars["notifytranscription"] = $notifytranscription;
        }
        if (isset($attachmessage)) {
            $vars["attachmessage"] = $attachmessage;
        }
        if (isset($usewave)) {
            $vars["usewave"] = $usewave;
        }
        if (isset($key)) {
            $vars["key"] = $key;
        }
        if (isset($ringstatus)) {
            $vars["ringstatus"] = $ringstatus;
        }
        if (isset($verify_by)) {
            $vars["verifyBy"] = $verify_by;
        }
        if (isset($auto_answer)) {
            $vars["autoAnswer"] = $auto_answer;
        }
        if (isset($allow_c_c_f)) {
            $vars["allowCCF"] = $allow_c_c_f;
        }
        $uri = "devices/" . $user_id . "/" . $device_id;
        $result = $signed_request->put($vars, $uri);
        $result = json_decode($result, true);
        return $result["entry"];
    }
    /**
     * Request a conditional call forwarding verification test
     * This method calls the Ribbit service
     *
     * @param string $device_id Unique Device identifier prefixed by schema to reflect device type (e.g. mailto:foo@bar.com) (required)
     * @return RibbitDeviceResource An associative array containing details about the DeviceResource
     */
    public function requestConditionalCallForwardingTest($device_id)
    {
        return $this->updateDevice($device_id, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, "ccfTest", null, null);
    }
    /**
     * Configures a purpose number to be automatically answered by REST
     * This method calls the Ribbit service
     *
     * @param string $device_id Unique Device identifier prefixed by schema to reflect device type (e.g. mailto:foo@bar.com) (required)
     * @param boolean $auto_answer Automatically answer this inbound device (optional)
     * @param boolean $allow_c_c_f Allow conditional call forwarding for this device (optional)
     * @return RibbitDeviceResource An associative array containing details about the DeviceResource
     */
    public function setAutoAnswer($device_id, $auto_answer = null, $allow_c_c_f = null)
    {
        return $this->updateDevice($device_id, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, $auto_answer, $allow_c_c_f);
    }
}
?>