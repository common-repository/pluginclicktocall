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
 * Contains the RibbitMessage class
 *
 * @package Ribbit
 */
require_once ('RibbitSignedRequest.class.php');
require_once ('RibbitException.class.php');
require_once ('RibbitUtil.class.php');
/**
 * Messages resources represent text, voicemail, SMS, and other forms of media that may be exchanged and saved by Users
 */
class RibbitMessage
{
    /**
     * Filter Messages by Destination
     */
    const FILTER_BY_DESTINATION = "destination";
    /**
     * Filter Messages by Folder
     */
    const FILTER_BY_FOLDER = "folder";
    /**
     * Filter Messages by Media Location
     */
    const FILTER_BY_MEDIA_LOCATION = "mediaLocation";
    /**
     * Filter Messages by Type
     */
    const FILTER_BY_MESSAGE_TYPE = "messageType";
    /**
     * Filter Messages by Notes
     */
    const FILTER_BY_NOTES = "notes";
    /**
     * Filter Messages by Sender
     */
    const FILTER_BY_SENDER = "sender";
    /**
     * Filter Messages by Status
     */
    const FILTER_BY_STATUS = "messageStatus";
    /**
     * Filter Messages by Tags
     */
    const FILTER_BY_TAGS = "tags";
    /**
     * Filter Messages by Title
     */
    const FILTER_BY_TITLE = "title";
    /**
     * Filter Messages by Transcription Status
     */
    const FILTER_BY_TRANSCRIPTION_STATUS = "transcriptionStatus";
    /**
     * Filter Messages by User Id
     */
    const FILTER_BY_USER_ID = "uid";
    /**
     * Use with FILTER_BY_STATUS to get deleted Messages
     */
    const STATUS_DELETED = "DELETED";
    /**
     * The message has been delivered
     */
    const STATUS_DELIVERED = "DELIVERED";
    /**
     * Use with FILTER_BY_STATUS to get failed Messages
     */
    const STATUS_FAILED = "FAILED";
    /**
     * Use with FILTER_BY_STATUS to get Messages in an 'initial' state
     */
    const STATUS_INITIAL = "INITIAL";
    /**
     * Use with FILTER_BY_STATUS to get new Messages
     */
    const STATUS_NEW_MESSAGES = "NEW";
    /**
     * Use with FILTER_BY_STATUS to get read Messages
     */
    const STATUS_READ = "READ";
    /**
     * Use with FILTER_BY_STATUS to get received Messages
     */
    const STATUS_RECEIVED = "RECEIVED";
    /**
     * Use with FILTER_BY_STATUS to get Messages that have been sent
     */
    const STATUS_SENT = "SENT";
    /**
     * Use with FILTER_BY_STATUS to get Messages in an unknown state
     */
    const STATUS_UNKNOWN = "UNKNOWN";
    /**
     * Use with FILTER_BY_STATUS to get urgent Messages
     */
    const STATUS_URGENT = "URGENT";
    /**
     * Use with FILTER_BY_TRANSCRIPTION_STATUS to get Messages where no Transcriptions are available
     */
    const TRANSCRIPTION_STATUS_FAILED = "notAvailable";
    /**
     * Use with FILTER_BY_TRANSCRIPTION_STATUS to get Messages where Transcriptions are pending
     */
    const TRANSCRIPTION_STATUS_PENDING = "pending";
    /**
     * Use with FILTER_BY_TRANSCRIPTION_STATUS to get Messages which have been transcribed
     */
    const TRANSCRIPTION_STATUS_TRANSCRIBED = "transcribed";
    /**
     * Use with FILTER_BY_MESSAGE_TYPE to get broadcast voicemail Messages
     */
    const TYPE_BROADCAST_VOICEMAIL = "BroadcastVoiceMail";
    /**
     * Use with FILTER_BY_MESSAGE_TYPE to get email Messages
     */
    const TYPE_EMAIL = "email";
    /**
     * Use with FILTER_BY_MESSAGE_TYPE to get inbound audio Messages
     */
    const TYPE_INBOUND_AUDIO_MESSAGE = "InboundAudioMessage";
    /**
     * Use with FILTER_BY_MESSAGE_TYPE to get inbound sms Messages
     */
    const TYPE_INBOUND_SMS = "InboundSms";
    /**
     * Use with FILTER_BY_MESSAGE_TYPE to get outbound audio Messages
     */
    const TYPE_OUTBOUND_AUDIO_MESSAGE = "OutboundAudioMessage";
    /**
     * Use with FILTER_BY_MESSAGE_TYPE to get outbound sms Messages
     */
    const TYPE_OUTBOUND_SMS = "OutboundSms";
    /**
     * Use with FILTER_BY_MESSAGE_TYPE to get sms Messages
     */
    const TYPE_SMS = "sms";
    /**
     * Use with FILTER_BY_MESSAGE_TYPE to get voicemail Messages
     */
    const TYPE_VOICEMAIL = "Voicemail";
    /**
     * Normally accessed through Ribbit::getInstance()->Messages()
     *
     * @return RibbitMessage An instance of this class
     */
    public static function getInstance()
    {
        static $instance;
        if (!isset($instance)) $instance = new RibbitMessage();
        return $instance;
    }
    private function RibbitMessage()
    {
    }
    /**
     * To send an SMS the recipients in the array must be formatted tel:xxnnnnnn where xx is a country code and nnnnnn is their phone number.<br/>When sending a SMS the sender must also be a tel:xxnnnnn uri, and a telephone number registered to the current User on the Ribbit Platform, either an allocated inbound (purpose) number or a cell phone. <br/>The body will be the content that gets displayed on the phone. <br/>The title is sometimes referred to as the message id, and some cellular devices and carriers make this available.
     * This method calls the Ribbit service
     *
     * @param string[] $recipients A list of details about the recipients of the Message (required)
     * @param string $body The body of the Message (required)
     * @param string $sender The device ID that sent the Message (optional)
     * @param string $title The title of the Message (optional)
     * @return string A message identifier
     */
    public function createMessage($recipients, $body, $sender = null, $title = null)
    {
        $signed_request = RibbitSignedRequest::getInstance();
        if (Ribbit::getInstance()->getUserId() == null) {
            throw new AuthenticatedUserRequiredException();
        }
        $user_id = Ribbit::getInstance()->getConfig()->getActiveUserId();
        $exceptions = array();
        if (!RibbitUtil::is_non_empty_array($recipients)) {
            $exceptions[] = "recipients is required";
        }
        if (!RibbitUtil::is_valid_string($body)) {
            $exceptions[] = "body is required";
        }
        if (!RibbitUtil::is_valid_string_if_defined($sender)) {
            $exceptions[] = "When defined, sender must be a string of one or more characters";
        }
        if (!RibbitUtil::is_valid_string_if_defined($title)) {
            $exceptions[] = "When defined, title must be a string of one or more characters";
        }
        if (count($exceptions) > 0) {
            throw new InvalidArgumentException(implode(";", $exceptions));
        }
        $vars = array();
        $vars["recipients"] = $recipients;
        $vars["body"] = $body;
        if (isset($sender)) {
            $vars["sender"] = $sender;
        }
        if (isset($title)) {
            $vars["title"] = $title;
        }
        $uri = "messages/" . $user_id . "/outbox";
        $result = $signed_request->post($vars, $uri);
        return RibbitUtil::get_id_from_uri($result);
    }
    /**
     * Gets details of a message in a folder
     * This method calls the Ribbit service
     *
     * @param string $message_id A message identifier (required)
     * @param string $folder A folder that contains messages (required)
     * @return RibbitMessageResource An associative array containing details about the MessageResource
     */
    public function getMessage($message_id, $folder)
    {
        $signed_request = RibbitSignedRequest::getInstance();
        if (Ribbit::getInstance()->getUserId() == null) {
            throw new AuthenticatedUserRequiredException();
        }
        $user_id = Ribbit::getInstance()->getConfig()->getActiveUserId();
        $exceptions = array();
        if (!RibbitUtil::is_valid_string($message_id)) {
            $exceptions[] = "message_id is required";
        }
        if (!RibbitUtil::is_valid_string($folder)) {
            $exceptions[] = "folder is required";
        }
        if (count($exceptions) > 0) {
            throw new InvalidArgumentException(implode(";", $exceptions));
        }
        $uri = "messages/" . $user_id . "/" . $folder . "/" . $message_id;
        $result = $signed_request->get($uri);
        $result = json_decode($result, true);
        return $result["entry"];
    }
    /**
     * Gets details of a message sent by the current User
     * This method calls the Ribbit service
     *
     * @param string $message_id A message identifier (required)
     * @return RibbitMessageResource An associative array containing details about the MessageResource
     */
    public function getSentMessage($message_id)
    {
        return $this->getMessage($message_id, "sent");
    }
    /**
     * Gets details of a sent message
     * This method calls the Ribbit service
     *
     * @param string $message_id A message identifier (required)
     * @return RibbitMessageResource An associative array containing details about the MessageResource
     */
    public function getReceivedMessage($message_id)
    {
        return $this->getMessage($message_id, "inbox");
    }
    /**
     * Gets a collection of details of messages associated with the current User. This method supports pagination and filtering, both separately and in combination
     * This method calls the Ribbit service
     *
     * @param int $start_index the first result to return when requesting a paged list (optional)
     * @param int $count the number of results to return when requesting a paged list (required if a start index is supplied)
     * @param string $filter_by an key to an index with which to filter results (optional)
     * @param string $filter_value the value to search within the filter for (required if a filter is supplied)
     * @return RibbitMessageResource An associative array, containing paging details and an ordered array, each entry of which contains an associative array containing details about the MessageResource
     */
    public function getMessages($start_index = null, $count = null, $filter_by = null, $filter_value = null)
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
        $uri = "messages/" . $user_id . $q;
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
     * Get a list of messages filtered by status. Values are 'delivered', 'received' and 'failed'
     * This method calls the Ribbit service
     *
     * @param string $status The value which represents the delivery status, to this recipient, of the Message (required)
     * @return RibbitMessageResource An ordered array, each entry of which contains an associative array containing details about the MessageResource
     */
    public function getMessagesFilteredByStatus($status)
    {
        $signed_request = RibbitSignedRequest::getInstance();
        if (Ribbit::getInstance()->getUserId() == null) {
            throw new AuthenticatedUserRequiredException();
        }
        $user_id = Ribbit::getInstance()->getConfig()->getActiveUserId();
        $exceptions = array();
        if (!RibbitUtil::is_valid_string($status)) {
            $exceptions[] = "status is required";
        }
        if (count($exceptions) > 0) {
            throw new InvalidArgumentException(implode(";", $exceptions));
        }
        $result = $this->getMessages(null, null, "messageStatus", $status);
        return $result;
    }
    /**
     * Get a list of messages filtered by a tag
     * This method calls the Ribbit service
     *
     * @param string $tag  (required)
     * @return RibbitMessageResource An ordered array, each entry of which contains an associative array containing details about the MessageResource
     */
    public function getMessagesFilteredByTag($tag)
    {
        $signed_request = RibbitSignedRequest::getInstance();
        if (Ribbit::getInstance()->getUserId() == null) {
            throw new AuthenticatedUserRequiredException();
        }
        $user_id = Ribbit::getInstance()->getConfig()->getActiveUserId();
        $exceptions = array();
        if (!RibbitUtil::is_valid_string($tag)) {
            $exceptions[] = "tag is required";
        }
        if (count($exceptions) > 0) {
            throw new InvalidArgumentException(implode(";", $exceptions));
        }
        $result = $this->getMessages(null, null, "tags", $tag);
        return $result;
    }
    /**
     *
     * This method calls the Ribbit service
     *
     * @return RibbitMessageResource An ordered array, each entry of which contains an associative array containing details about the MessageResource
     */
    public function getNewMessages()
    {
        $signed_request = RibbitSignedRequest::getInstance();
        if (Ribbit::getInstance()->getUserId() == null) {
            throw new AuthenticatedUserRequiredException();
        }
        $user_id = Ribbit::getInstance()->getConfig()->getActiveUserId();
        $result = $this->getMessages(null, null, "messageStatus", "new");
        return $result;
    }
    /**
     * Gets a collection of details of messages received by the current User. This method supports pagination
     * This method calls the Ribbit service
     *
     * @param int $start_index the first result to return when requesting a paged list (optional)
     * @param int $count the number of results to return when requesting a paged list (required if a start index is supplied)
     * @return RibbitMessageResource An associative array, containing paging details and an ordered array, each entry of which contains an associative array containing details about the MessageResource
     */
    public function getReceivedMessages($start_index = null, $count = null)
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
        if (count($exceptions) > 0) {
            throw new InvalidArgumentException(implode(";", $exceptions));
        }
        $q = array();
        if (!(is_null($start_index) || is_null($count))) {
            $q[] = "startIndex=" . $start_index . "&count=" . $count;
        }
        $q = (count($q) > 0) ? "?" . implode('&', $q) : "";
        $uri = "messages/" . $user_id . "/inbox" . $q;
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
     * Gets a collection of details of messages sent by the current User. This method supports pagination
     * This method calls the Ribbit service
     *
     * @param int $start_index the first result to return when requesting a paged list (optional)
     * @param int $count the number of results to return when requesting a paged list (required if a start index is supplied)
     * @return RibbitMessageResource An associative array, containing paging details and an ordered array, each entry of which contains an associative array containing details about the MessageResource
     */
    public function getSentMessages($start_index = null, $count = null)
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
        if (count($exceptions) > 0) {
            throw new InvalidArgumentException(implode(";", $exceptions));
        }
        $q = array();
        if (!(is_null($start_index) || is_null($count))) {
            $q[] = "startIndex=" . $start_index . "&count=" . $count;
        }
        $q = (count($q) > 0) ? "?" . implode('&', $q) : "";
        $uri = "messages/" . $user_id . "/sent" . $q;
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
     * Update a message. Move it to a folder or flag it
     * This method calls the Ribbit service
     *
     * @param string $message_id A message identifier (required)
     * @param string $folder A folder that contains messages (optional)
     * @param boolean $new_message Whether the message is flagged as 'new' (optional)
     * @param boolean $urgent Whether the message is flagged as 'urgent' (optional)
     * @param string $new_folder A folder that contains messages (optional)
     * @return RibbitMessageResource An associative array containing details about the MessageResource
     */
    public function updateMessage($message_id, $folder = null, $new_message = null, $urgent = null, $new_folder = null)
    {
        $signed_request = RibbitSignedRequest::getInstance();
        if (Ribbit::getInstance()->getUserId() == null) {
            throw new AuthenticatedUserRequiredException();
        }
        $user_id = Ribbit::getInstance()->getConfig()->getActiveUserId();
        $exceptions = array();
        if (!RibbitUtil::is_valid_string($message_id)) {
            $exceptions[] = "message_id is required";
        }
        if (!RibbitUtil::is_valid_string_if_defined($folder)) {
            $exceptions[] = "When defined, current_folder must be a string of one or more characters";
        }
        if (!RibbitUtil::is_valid_bool_if_defined($new_message)) {
            $exceptions[] = "When defined, new must be boolean";
        }
        if (!RibbitUtil::is_valid_bool_if_defined($urgent)) {
            $exceptions[] = "When defined, urgent must be boolean";
        }
        if (!RibbitUtil::is_valid_string_if_defined($new_folder)) {
            $exceptions[] = "When defined, folder must be a string of one or more characters";
        }
        if (count($exceptions) > 0) {
            throw new InvalidArgumentException(implode(";", $exceptions));
        }
        $vars = array();
        if (isset($new_message)) {
            $vars["new"] = $new_message;
        }
        if (isset($urgent)) {
            $vars["urgent"] = $urgent;
        }
        if (isset($new_folder)) {
            $vars["folder"] = $new_folder;
        }
        $uri = "messages/" . $user_id . "/" . $folder . "/" . $message_id;
        $result = $signed_request->put($vars, $uri);
        $result = json_decode($result, true);
        return $result["entry"];
    }
    /**
     * Flag a message as 'urgent'
     * This method calls the Ribbit service
     *
     * @param string $message_id A message identifier (required)
     * @param string $folder A folder that contains messages (optional)
     * @return RibbitMessageResource An associative array containing details about the MessageResource
     */
    public function markMessageUrgent($message_id, $folder = "inbox")
    {
        return $this->updateMessage($message_id, $folder, null, true, null);
    }
    /**
     *
     * This method calls the Ribbit service
     *
     * @param string $message_id A message identifier (required)
     * @param string $folder A folder that contains messages (optional)
     * @return RibbitMessageResource An associative array containing details about the MessageResource
     */
    public function markMessageNotUrgent($message_id, $folder = "inbox")
    {
        return $this->updateMessage($message_id, $folder, null, false, null);
    }
    /**
     * Flag a message as 'new'
     * This method calls the Ribbit service
     *
     * @param string $message_id A message identifier (required)
     * @param string $folder A folder that contains messages (optional)
     * @return RibbitMessageResource An associative array containing details about the MessageResource
     */
    public function markMessageNew($message_id, $folder = "inbox")
    {
        return $this->updateMessage($message_id, $folder, true, null, null);
    }
    /**
     *
     * This method calls the Ribbit service
     *
     * @param string $message_id A message identifier (required)
     * @param string $folder A folder that contains messages (optional)
     * @return RibbitMessageResource An associative array containing details about the MessageResource
     */
    public function markMessageRead($message_id, $folder = "inbox")
    {
        return $this->updateMessage($message_id, $folder, false, null, null);
    }
    /**
     *
     * This method calls the Ribbit service
     *
     * @param string $message_id A message identifier (required)
     * @param string $folder A folder that contains messages (optional)
     * @return RibbitMessageResource An associative array containing details about the MessageResource
     */
    public function deleteMessage($message_id, $folder = "inbox")
    {
        return $this->updateMessage($message_id, $folder, null, null, "deleted");
    }
}
?>