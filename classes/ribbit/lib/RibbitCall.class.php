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
 * Contains the RibbitCall class
 *
 * @package Ribbit
 */
require_once ('RibbitSignedRequest.class.php');
require_once ('RibbitException.class.php');
require_once ('RibbitUtil.class.php');
/**
 * Calls are telephony events between Devices from the point of view of a given User. Calls are initiated by a POST to a User's Call collection, with parameters to represent the source and destination numbers.
 * Note: Phone numbers must have 'tel:' before the phone number.
 */
class RibbitCall
{
    /**
     * Use the classic British Text To Speech voice
     */
    const ANNOUNCE_EN_UK_CLASSIC = "en_UK/classic";
    /**
     * Use the classic American Text To Speech voice
     */
    const ANNOUNCE_EN_US_CLASSIC = "en_US/classic";
    /**
     * Filter Calls by Application Id
     */
    const FILTER_BY_APPLICATION_ID = "application.id";
    /**
     * Filter Calls by Domain
     */
    const FILTER_BY_DOMAIN = "application.domain.name";
    /**
     * Filter Calls by User Id
     */
    const FILTER_BY_USER_ID = "user.guid";
    /**
     * Say a set of numbers
     */
    const MEDIA_TYPE_DIGITS = "digits";
    /**
     * Say a length of time
     */
    const MEDIA_TYPE_DURATION = "duration";
    /**
     * Play a media file in format /media/domain/folder/file (.mp3 or .wav)
     */
    const MEDIA_TYPE_FILE = "file";
    /**
     * Say a money amount
     */
    const MEDIA_TYPE_MONEY = "money";
    /**
     * Say a month
     */
    const MEDIA_TYPE_MONTH = "month";
    /**
     * Say a number
     */
    const MEDIA_TYPE_NUMBER = "number";
    /**
     * Say a ranking
     */
    const MEDIA_TYPE_RANK = "rank";
    /**
     *
     */
    const MEDIA_TYPE_SPELL = "string";
    /**
     * Say a time
     */
    const MEDIA_TYPE_TIME = "time";
    /**
     * Say a week day
     */
    const MEDIA_TYPE_WEEKDAY = "weekday";
    /**
     * Say a year
     */
    const MEDIA_TYPE_YEAR = "year";
    /**
     * The call leg is answered
     */
    const STATUS_ANSWERED = "ANSWERED";
    /**
     * The call leg is connecting
     */
    const STATUS_CONNECTING = "CONNECTING";
    /**
     * The call leg is in error
     */
    const STATUS_ERROR = "ERROR";
    /**
     * The call leg failed to connect
     */
    const STATUS_FAILURE = "FAILURE";
    /**
     * The call leg has hungup
     */
    const STATUS_HUNGUP = "HUNGUP";
    /**
     * The call leg is started
     */
    const STATUS_STARTED = "STARTED";
    /**
     * The call leg has been transferred to another call
     */
    const STATUS_TRANSFERRED = "TRANSFERRED";
    /**
     * The call leg is transferring to another call
     */
    const STATUS_TRANSFERRING = "TRANSFERRING";
    /**
     * Normally accessed through Ribbit::getInstance()->Calls()
     *
     * @return RibbitCall An instance of this class
     */
    public static function getInstance()
    {
        static $instance;
        if (!isset($instance)) $instance = new RibbitCall();
        return $instance;
    }
    private function RibbitCall()
    {
    }
    /**
     * Calls may be made to one or more Devices. To connect Calls to PSTN numbers on the production platform, credit must be available in the User's Account to cover the cost of connecting for at least one minute.
     * This method calls the Ribbit service
     *
     * @param string[] $legs Device IDs which participate in this call (SIP: or TEL: only) (required)
     * @param string $callerid The number which will be presented when devices are called (optional)
     * @param string $mode The mode of a call or leg describes it's state.  Options are: hold, mute, hangup, talk (optional)
     * @param string $announce The Text to Speech culture to use, available from constants in this class (optional)
     * @return string A call identifier
     */
    public function createCall($legs, $callerid = null, $mode = null, $announce = null)
    {
        $signed_request = RibbitSignedRequest::getInstance();
        if (Ribbit::getInstance()->getUserId() == null) {
            throw new AuthenticatedUserRequiredException();
        }
        $user_id = Ribbit::getInstance()->getConfig()->getActiveUserId();
        $exceptions = array();
        if (!RibbitUtil::is_non_empty_array($legs)) {
            $exceptions[] = "legs is required";
        }
        if (!RibbitUtil::is_valid_string_if_defined($callerid)) {
            $exceptions[] = "When defined, callerid must be a string of one or more characters";
        }
        if (!RibbitUtil::is_valid_string_if_defined($mode)) {
            $exceptions[] = "When defined, mode must be a string of one or more characters";
        }
        if (!RibbitUtil::is_valid_string_if_defined($announce)) {
            $exceptions[] = "When defined, announce must be a string of one or more characters";
        }
        if (count($exceptions) > 0) {
            throw new InvalidArgumentException(implode(";", $exceptions));
        }
        $vars = array();
        $vars["legs"] = $legs;
        if (isset($callerid)) {
            $vars["callerid"] = $callerid;
        }
        if (isset($mode)) {
            $vars["mode"] = $mode;
        }
        if (isset($announce)) {
            $vars["announce"] = $announce;
        }
        $uri = "calls/" . $user_id;
        $result = $signed_request->post($vars, $uri);
        return RibbitUtil::get_id_from_uri($result);
    }
    /**
     * Calls may be made between any two Devices. To connect Calls to PSTN numbers on the production platform, credit must be available in the User's Account to cover the cost of connecting for at least one minute.
     * This method calls the Ribbit service
     *
     * @param string $source Device ID (or alias) from which the Call is made (SIP: or TEL: only) (required)
     * @param string[] $dest Device IDs to which this Call is made (SIP: or TEL: only) (required)
     * @return string A call identifier
     */
    public function createThirdPartyCall($source, $dest)
    {
        $signed_request = RibbitSignedRequest::getInstance();
        if (Ribbit::getInstance()->getUserId() == null) {
            throw new AuthenticatedUserRequiredException();
        }
        $user_id = Ribbit::getInstance()->getConfig()->getActiveUserId();
        $exceptions = array();
        if (!RibbitUtil::is_valid_string($source)) {
            $exceptions[] = "source is required";
        }
        if (!RibbitUtil::is_non_empty_array($dest)) {
            $exceptions[] = "dest is required";
        }
        if (count($exceptions) > 0) {
            throw new InvalidArgumentException(implode(";", $exceptions));
        }
        $vars = array();
        $vars["source"] = $source;
        $vars["dest"] = $dest;
        $uri = "calls/" . $user_id;
        $result = $signed_request->post($vars, $uri);
        return RibbitUtil::get_id_from_uri($result);
    }
    /**
     * Once a Call is made the details may be retrieved to show the current status of each Leg. Only the Call owner is able to query the Call details.
     * This method calls the Ribbit service
     *
     * @param string $call_id Unique numeric Call identifier (required)
     * @return RibbitCallResource An associative array containing details about the CallResource
     */
    public function getCall($call_id)
    {
        $signed_request = RibbitSignedRequest::getInstance();
        if (Ribbit::getInstance()->getUserId() == null) {
            throw new AuthenticatedUserRequiredException();
        }
        $user_id = Ribbit::getInstance()->getConfig()->getActiveUserId();
        $exceptions = array();
        if (isset($call_id)) {
            $call_id = "" . $call_id;
        }
        if (!RibbitUtil::is_valid_string($call_id)) {
            $exceptions[] = "call_id is required";
        }
        if (count($exceptions) > 0) {
            throw new InvalidArgumentException(implode(";", $exceptions));
        }
        $uri = "calls/" . $user_id . "/" . $call_id;
        $result = $signed_request->get($uri);
        $result = json_decode($result, true);
        return $result["entry"];
    }
    /**
     * The Call history can be retrieved by making a GET on the Call resource.  The result is a collection of Calls.
     * This method calls the Ribbit service
     *
     * @param int $start_index the first result to return when requesting a paged list (optional)
     * @param int $count the number of results to return when requesting a paged list (required if a start index is supplied)
     * @param string $filter_by an key to an index with which to filter results (optional)
     * @param string $filter_value the value to search within the filter for (required if a filter is supplied)
     * @return RibbitCallResource An associative array, containing paging details and an ordered array, each entry of which contains an associative array containing details about the CallResource
     */
    public function getCalls($start_index = null, $count = null, $filter_by = null, $filter_value = null)
    {
        $signed_request = RibbitSignedRequest::getInstance();
        if (Ribbit::getInstance()->getUserId() == null) {
            throw new AuthenticatedUserRequiredException();
        }
        $user_id = Ribbit::getInstance()->getConfig()->getActiveUserId();
        $exceptions = array();
        $paging_param_error = RibbitUtil::check_paging_parameters($start_index, $count);
        if ($paging_param_error != null) {
            $exceptions[] = $paging_param_error;
        }
        $filter_param_error = RibbitUtil::check_filter_parameters($filter_by, $filter_value);
        if ($filter_param_error != null) {
            $exceptions[] = $filter_param_error;
        }
        if (count($exceptions) > 0) {
            throw new InvalidArgumentException(implode(";", $exceptions));
        }
        $q = array();
        if (!(is_null($start_index) || is_null($count))) {
            $q[] = "startIndex=" . $start_index . "&count=" . $count;
        }
        if (!(is_null($filter_by) || is_null($filter_value))) {
            $q[] = "filterBy=" . $filter_by . "&filterValue=" . $filter_value;
        }
        $q = (count($q) > 0) ? "?" . implode('&', $q) : "";
        $uri = "calls/" . $user_id . $q;
        $result = $signed_request->get($uri);
        $result = json_decode($result, true);
        if (!is_null($start_index)) {
            if (!isset($result["totalResults"])) {
                $result["totalResults"] = 0;
                $result["itemsPerPage"] = 0;
                $result["startIndex"] = 0;
            }
        } else {
            $result = $result['entry'];
        }
        return $result;
    }
    /**
     * Transfers a call leg from one call to another. The leg must be answered, and the destination call must be active
     * This method calls the Ribbit service
     *
     * @param string $source_call_id The call id from which the leg should be transferred (required)
     * @param string $source_leg_id The source call leg identifier (required)
     * @param string $destination_call_id The call id to which the leg should be transferred (required)
     * @return boolean true if the method succeeds
     */
    public function transferLeg($source_call_id, $source_leg_id, $destination_call_id)
    {
        $signed_request = RibbitSignedRequest::getInstance();
        if (Ribbit::getInstance()->getUserId() == null) {
            throw new AuthenticatedUserRequiredException();
        }
        $user_id = Ribbit::getInstance()->getConfig()->getActiveUserId();
        $exceptions = array();
        if (isset($source_call_id)) {
            $source_call_id = "" . $source_call_id;
        }
        if (!RibbitUtil::is_valid_string($source_call_id)) {
            $exceptions[] = "source_call_id is required";
        }
        if (!RibbitUtil::is_valid_string($source_leg_id)) {
            $exceptions[] = "source_leg_id is required";
        }
        if (isset($destination_call_id)) {
            $destination_call_id = "" . $destination_call_id;
        }
        if (!RibbitUtil::is_valid_string($destination_call_id)) {
            $exceptions[] = "destination_call_id is required";
        }
        if (count($exceptions) > 0) {
            throw new InvalidArgumentException(implode(";", $exceptions));
        }
        $result = $this->updateCall($destination_call_id, $source_call_id . "/" . $source_leg_id, null, null, null, null, null, null, null);
        return $result;
    }
    /**
     * Updates a call to change the mode of all legs, start and stop call recording, or play media to all the legs. The call must contain at least one active leg.
     * This method calls the Ribbit service
     *
     * @param string $call_id Unique numeric Call identifier (required)
     * @param string $id Unique numeric Call identifier (optional)
     * @param string $mode The mode of a call or leg describes it's state.  Options are: hold, mute, hangup, talk (optional)
     * @param boolean $active Whether the call is active (optional)
     * @param RibbitCallRecordRequest $record An object containing details of the recording request (optional)
     * @param boolean $recording True if recording is active. Set to false to stop recording (optional)
     * @param string $announce The Text to Speech culture to use, available from constants in this class (optional)
     * @param RibbitCallPlayRequest $play An object containing details of the recording request (optional)
     * @param boolean $playing True if media is playing. Set to false to stop playing (optional)
     * @return boolean true if the method succeeds
     */
    public function updateCall($call_id, $id = null, $mode = null, $active = null, $record = null, $recording = null, $announce = null, $play = null, $playing = null)
    {
        $signed_request = RibbitSignedRequest::getInstance();
        if (Ribbit::getInstance()->getUserId() == null) {
            throw new AuthenticatedUserRequiredException();
        }
        $user_id = Ribbit::getInstance()->getConfig()->getActiveUserId();
        $exceptions = array();
        if (isset($call_id)) {
            $call_id = "" . $call_id;
        }
        if (!RibbitUtil::is_valid_string($call_id)) {
            $exceptions[] = "call_id is required";
        }
        if (isset($id)) {
            $id = "" . $id;
        }
        if (!RibbitUtil::is_valid_string_if_defined($id)) {
            $exceptions[] = "When defined, id must be a string of one or more characters";
        }
        if (!RibbitUtil::is_valid_string_if_defined($mode)) {
            $exceptions[] = "When defined, mode must be a string of one or more characters";
        }
        if (!RibbitUtil::is_valid_bool_if_defined($active)) {
            $exceptions[] = "When defined, active must be boolean";
        }
        if (isset($record)) {
            if (!($record instanceof RibbitCallRecordRequest)) {
                $exceptions[] = "record must be an instance of RibbitCallRecordRequest";
            } else {
                $x = $record->getValidationMessage();
                if ($x != "") {
                    $exceptions[] = $x;
                }
            }
        }
        if (!RibbitUtil::is_valid_bool_if_defined($recording)) {
            $exceptions[] = "When defined, recording must be boolean";
        }
        if (!RibbitUtil::is_valid_string_if_defined($announce)) {
            $exceptions[] = "When defined, announce must be a string of one or more characters";
        }
        if (isset($play)) {
            if (!($play instanceof RibbitCallPlayRequest)) {
                $exceptions[] = "play must be an instance of RibbitCallPlayRequest";
            } else {
                $x = $play->getValidationMessage();
                if ($x != "") {
                    $exceptions[] = $x;
                }
            }
        }
        if (!RibbitUtil::is_valid_bool_if_defined($playing)) {
            $exceptions[] = "When defined, playing must be boolean";
        }
        if (count($exceptions) > 0) {
            throw new InvalidArgumentException(implode(";", $exceptions));
        }
        $vars = array();
        if (isset($id)) {
            $vars["id"] = $id;
        }
        if (isset($mode)) {
            $vars["mode"] = $mode;
        }
        if (isset($active)) {
            $vars["active"] = $active;
        }
        if (isset($record)) {
            $vars["record"] = $record->toArray();
        }
        if (isset($recording)) {
            $vars["recording"] = $recording;
        }
        if (isset($announce)) {
            $vars["announce"] = $announce;
        }
        if (isset($play)) {
            $vars["play"] = $play->toArray();
        }
        if (isset($playing)) {
            $vars["playing"] = $playing;
        }
        $uri = "calls/" . $user_id . "/" . $call_id;
        $result = $signed_request->put($vars, $uri);
        return true;
    }
    /**
     * Mute all active legs on a call. At least one leg must be active.
     * This method calls the Ribbit service
     *
     * @param string $call_id Unique numeric Call identifier (required)
     * @return boolean true if the method succeeds
     */
    public function muteCall($call_id)
    {
        return $this->updateCall($call_id, null, "mute", null, null, null, null, null, null);
    }
    /**
     * Take all active and muted legs on a call off mute. At least one leg must be active
     * This method calls the Ribbit service
     *
     * @param string $call_id Unique numeric Call identifier (required)
     * @return boolean true if the method succeeds
     */
    public function unmuteCall($call_id)
    {
        return $this->updateCall($call_id, null, "talk", null, null, null, null, null, null);
    }
    /**
     * Puts all active legs on a call on hold. At least one leg must be active
     * This method calls the Ribbit service
     *
     * @param string $call_id Unique numeric Call identifier (required)
     * @return boolean true if the method succeeds
     */
    public function holdCall($call_id)
    {
        return $this->updateCall($call_id, null, "hold", null, null, null, null, null, null);
    }
    /**
     * Takes all active and held legs on a call off hold. At least one leg must be active
     * This method calls the Ribbit service
     *
     * @param string $call_id Unique numeric Call identifier (required)
     * @return boolean true if the method succeeds
     */
    public function unholdCall($call_id)
    {
        return $this->updateCall($call_id, null, "talk", null, null, null, null, null, null);
    }
    /**
     * Terminates the call
     * This method calls the Ribbit service
     *
     * @param string $call_id Unique numeric Call identifier (required)
     * @return boolean true if the method succeeds
     */
    public function hangupCall($call_id)
    {
        return $this->updateCall($call_id, null, null, false, null, null, null, null, null);
    }
    /**
     * Start recording a call. At least one leg must be active.
     * This method calls the Ribbit service
     *
     * @param string $call_id Unique numeric Call identifier (required)
     * @param RibbitCallRecordRequest $record An object containing details of the recording request (optional)
     * @return boolean true if the method succeeds
     */
    public function recordCall($call_id, $record = null)
    {
        return $this->updateCall($call_id, null, null, null, $record, null, null, null, null);
    }
    /**
     * Stop recording a call
     * This method calls the Ribbit service
     *
     * @param string $call_id Unique numeric Call identifier (required)
     * @return boolean true if the method succeeds
     */
    public function stopRecordingCall($call_id)
    {
        return $this->updateCall($call_id, null, null, null, null, false, null, null, null);
    }
    /**
     * Play files and/or Text To Speech elements to a call. At least one leg must be active
     * This method calls the Ribbit service
     *
     * @param string $call_id Unique numeric Call identifier (required)
     * @param string $announce The Text to Speech culture to use, available from constants in this class (optional)
     * @param RibbitCallPlayRequest $play An object containing details of the recording request (optional)
     * @return boolean true if the method succeeds
     */
    public function playMediaToCall($call_id, $announce = null, $play = null)
    {
        return $this->updateCall($call_id, null, null, null, null, null, $announce, $play, null);
    }
    /**
     * Stop playing files and/or Text To speech elements to a call
     * This method calls the Ribbit service
     *
     * @param string $call_id Unique numeric Call identifier (required)
     * @return boolean true if the method succeeds
     */
    public function stopPlayingMediaToCall($call_id)
    {
        return $this->updateCall($call_id, null, null, null, null, null, null, null, false);
    }
    /**
     * Updates the mode of a call leg, records it, or plays media to it, or requests DTMF (keypad) input. The leg must be active to respond to update requests
     * This method calls the Ribbit service
     *
     * @param string $call_id Unique numeric Call identifier (required)
     * @param string $leg_id The call leg identifier (required)
     * @param string $mode The mode of a call or leg describes it's state.  Options are: hold, mute, hangup, talk (optional)
     * @param RibbitCallLegDtmfRequest $request_dtmf An object containing details of a request to collect DTMF input from a call leg (optional)
     * @param RibbitCallRecordRequest $record An object containing details of the recording request (optional)
     * @param boolean $recording True if recording is active. Set to false to stop recording (optional)
     * @param string $announce The Text to Speech culture to use, available from constants in this class (optional)
     * @param RibbitCallPlayRequest $play An object containing details of the recording request (optional)
     * @param boolean $playing True if media is playing. Set to false to stop playing (optional)
     * @return boolean true if the method succeeds
     */
    public function updateCallLeg($call_id, $leg_id, $mode = null, $request_dtmf = null, $record = null, $recording = null, $announce = null, $play = null, $playing = null)
    {
        $signed_request = RibbitSignedRequest::getInstance();
        if (Ribbit::getInstance()->getUserId() == null) {
            throw new AuthenticatedUserRequiredException();
        }
        $user_id = Ribbit::getInstance()->getConfig()->getActiveUserId();
        $exceptions = array();
        if (isset($call_id)) {
            $call_id = "" . $call_id;
        }
        if (!RibbitUtil::is_valid_string($call_id)) {
            $exceptions[] = "call_id is required";
        }
        if (!RibbitUtil::is_valid_string($leg_id)) {
            $exceptions[] = "leg_id is required";
        }
        if (!RibbitUtil::is_valid_string_if_defined($mode)) {
            $exceptions[] = "When defined, mode must be a string of one or more characters";
        }
        if (isset($request_dtmf)) {
            if (!($request_dtmf instanceof RibbitCallLegDtmfRequest)) {
                $exceptions[] = "request_dtmf must be an instance of RibbitCallLegDtmfRequest";
            } else {
                $x = $request_dtmf->getValidationMessage();
                if ($x != "") {
                    $exceptions[] = $x;
                }
            }
        }
        if (isset($record)) {
            if (!($record instanceof RibbitCallRecordRequest)) {
                $exceptions[] = "record must be an instance of RibbitCallRecordRequest";
            } else {
                $x = $record->getValidationMessage();
                if ($x != "") {
                    $exceptions[] = $x;
                }
            }
        }
        if (!RibbitUtil::is_valid_bool_if_defined($recording)) {
            $exceptions[] = "When defined, recording must be boolean";
        }
        if (!RibbitUtil::is_valid_string_if_defined($announce)) {
            $exceptions[] = "When defined, announce must be a string of one or more characters";
        }
        if (isset($play)) {
            if (!($play instanceof RibbitCallPlayRequest)) {
                $exceptions[] = "play must be an instance of RibbitCallPlayRequest";
            } else {
                $x = $play->getValidationMessage();
                if ($x != "") {
                    $exceptions[] = $x;
                }
            }
        }
        if (!RibbitUtil::is_valid_bool_if_defined($playing)) {
            $exceptions[] = "When defined, playing must be boolean";
        }
        if (count($exceptions) > 0) {
            throw new InvalidArgumentException(implode(";", $exceptions));
        }
        $vars = array();
        if (isset($mode)) {
            $vars["mode"] = $mode;
        }
        if (isset($request_dtmf)) {
            $vars["requestDtmf"] = $request_dtmf->toArray();
        }
        if (isset($record)) {
            $vars["record"] = $record->toArray();
        }
        if (isset($recording)) {
            $vars["recording"] = $recording;
        }
        if (isset($announce)) {
            $vars["announce"] = $announce;
        }
        if (isset($play)) {
            $vars["play"] = $play->toArray();
        }
        if (isset($playing)) {
            $vars["playing"] = $playing;
        }
        $uri = "calls/" . $user_id . "/" . $call_id . "/" . $leg_id;
        $result = $signed_request->put($vars, $uri);
        return true;
    }
    /**
     * Mutes a call leg. The leg must be active.
     * This method calls the Ribbit service
     *
     * @param string $call_id Unique numeric Call identifier (required)
     * @param string $leg_id The call leg identifier (required)
     * @return boolean true if the method succeeds
     */
    public function muteLeg($call_id, $leg_id)
    {
        return $this->updateCallLeg($call_id, $leg_id, "mute", null, null, null, null, null, null);
    }
    /**
     * Takes a call leg off mute. The leg must be active
     * This method calls the Ribbit service
     *
     * @param string $call_id Unique numeric Call identifier (required)
     * @param string $leg_id The call leg identifier (required)
     * @return boolean true if the method succeeds
     */
    public function unmuteLeg($call_id, $leg_id)
    {
        return $this->updateCallLeg($call_id, $leg_id, "talk", null, null, null, null, null, null);
    }
    /**
     * Puts a call leg on hold. The leg must be active
     * This method calls the Ribbit service
     *
     * @param string $call_id Unique numeric Call identifier (required)
     * @param string $leg_id The call leg identifier (required)
     * @return boolean true if the method succeeds
     */
    public function holdLeg($call_id, $leg_id)
    {
        return $this->updateCallLeg($call_id, $leg_id, "hold", null, null, null, null, null, null);
    }
    /**
     * Takes a call leg off hold. The leg must be active
     * This method calls the Ribbit service
     *
     * @param string $call_id Unique numeric Call identifier (required)
     * @param string $leg_id The call leg identifier (required)
     * @return boolean true if the method succeeds
     */
    public function unholdLeg($call_id, $leg_id)
    {
        return $this->updateCallLeg($call_id, $leg_id, "talk", null, null, null, null, null, null);
    }
    /**
     * Removes a leg from a call
     * This method calls the Ribbit service
     *
     * @param string $call_id Unique numeric Call identifier (required)
     * @param string $leg_id The call leg identifier (required)
     * @return boolean true if the method succeeds
     */
    public function hangupLeg($call_id, $leg_id)
    {
        return $this->updateCallLeg($call_id, $leg_id, "hangup", null, null, null, null, null, null);
    }
    /**
     * Start recording a call leg. The leg must be active
     * This method calls the Ribbit service
     *
     * @param string $call_id Unique numeric Call identifier (required)
     * @param string $leg_id The call leg identifier (required)
     * @param RibbitCallRecordRequest $record An object containing details of the recording request (optional)
     * @return boolean true if the method succeeds
     */
    public function recordCallLeg($call_id, $leg_id, $record = null)
    {
        return $this->updateCallLeg($call_id, $leg_id, null, null, $record, null, null, null, null);
    }
    /**
     * Stop recording a call leg
     * This method calls the Ribbit service
     *
     * @param string $call_id Unique numeric Call identifier (required)
     * @param string $leg_id The call leg identifier (required)
     * @return boolean true if the method succeeds
     */
    public function stopRecordingCallLeg($call_id, $leg_id)
    {
        return $this->updateCallLeg($call_id, $leg_id, null, null, null, false, null, null, null);
    }
    /**
     * Play files and/or Text To Speech elements to a call leg. The leg must be active
     * This method calls the Ribbit service
     *
     * @param string $call_id Unique numeric Call identifier (required)
     * @param string $leg_id The call leg identifier (required)
     * @param string $announce The Text to Speech culture to use, available from constants in this class (optional)
     * @param RibbitCallPlayRequest $play An object containing details of the recording request (optional)
     * @return boolean true if the method succeeds
     */
    public function playMediaToCallLeg($call_id, $leg_id, $announce = null, $play = null)
    {
        return $this->updateCallLeg($call_id, $leg_id, null, null, null, null, $announce, $play, null);
    }
    /**
     * Stop playing files and/or Text To speech elements to a call leg
     * This method calls the Ribbit service
     *
     * @param string $call_id Unique numeric Call identifier (required)
     * @param string $leg_id The call leg identifier (required)
     * @return boolean true if the method succeeds
     */
    public function stopPlayingMediaToCallLeg($call_id, $leg_id)
    {
        return $this->updateCallLeg($call_id, $leg_id, null, null, null, null, null, null, false);
    }
    /**
     * Request DTMF digits collected from a call leg. The leg should be active before DTMF is requested
     * This method calls the Ribbit service
     *
     * @param string $call_id Unique numeric Call identifier (required)
     * @param string $leg_id The call leg identifier (required)
     * @param RibbitCallLegDtmfRequest $request_dtmf An object containing details of a request to collect DTMF input from a call leg (optional)
     * @return boolean true if the method succeeds
     */
    public function requestDtmfFromCallLeg($call_id, $leg_id, $request_dtmf = null)
    {
        return $this->updateCallLeg($call_id, $leg_id, null, $request_dtmf, null, null, null, null, null);
    }
}
?>