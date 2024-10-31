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
 * Contains the RibbitCallbackNotification class
 *
 * @package Ribbit
 */
require_once ('RibbitUtil.class.php');
require_once ('RibbitDtmfReceivedNotification.class.php');
require_once ('RibbitCallLegAnsweredNotification.class.php');
require_once ('RibbitPlayAnnouncementCompleteNotification.class.php');
require_once ('RibbitCallInitiatedNotification.class.php');
require_once ('RibbitRecordLegCompleteNotification.class.php');
require_once ('RibbitRecordCallCompleteNotification.class.php');
require_once ('RibbitCallLegHungUpNotification.class.php');
require_once ('RibbitCallRingingNotification.class.php');
require_once ('RibbitCallAnsweredNotification.class.php');
require_once ('RibbitCallCompletedNotification.class.php');
require_once ('RibbitCallHangupNotification.class.php');
require_once ('RibbitCallIncomingNotification.class.php');
require_once ('RibbitCallStopRingingNotification.class.php');
require_once ('RibbitCallVMPickupNotification.class.php');
require_once ('RibbitDeleteMessageNotification.class.php');
require_once ('RibbitNewCallLogNotification.class.php');
require_once ('RibbitNewVoicemailNotification.class.php');
require_once ('RibbitTranscribeDataNotification.class.php');
/**
 * Serializes and exposes a callback notification
 *
 * The Ribbit Platform may send notifications of various events back to your web server.<br/>
 * In order to receive these you must ensure that you have set a Notification URL for your application,
 * which you can do by
 * <pre>
 * require_once "path/to/Ribbit.php"
 * $ribbit = Ribbit::getInstance();
 *
 * //you must have entered your application id and domain in ribbit_config.yml for the next line to work
 * $ribbit->Applications()->updateApplication($url);
 *
 * </pre>
 *
 * Then in notificationpage.php you would include the following code
 * <pre>
 * require_once "path/to/Ribbit.php"
 *
 * $ribbit = Ribbit::getInstance();
 * $notification = $ribbit->getCallback Notification();
 * if ($notification instanceof RibbitCallIncomingNotification){
 *		echo ("there is an inbound call coming from ". $notification->getSource());
 * }
 * else if ($notification instanceof RibbitDtmfReceivedNotification){
 *		echo ($notificaton->getDtmfReceived() . " was pressed");
 * }
 * echo "the affected resource was " . $notification->getResource();
 * echo "there are " . count( $notification->getParameters() ) . " parameters in this callback";
 * </pre>
 *
 * @package Ribbit
 * @version 1.6.0
 * @author BT/Ribbit
 */
class RibbitCallbackNotification
{
    private $_event_name;
    private $_time;
    private $_resource;
    private $_params;
    private $_event_type;
    /**
     * Constructs a new RibbitCallbackNotification from a correctly formed associative array
     * @param $obj an assocative array
     * @return unknown_type
     */
    public function RibbitCallbackNotification($obj)
    {
        $this->_time = $obj['time'];
        $this->_resource = $obj['resource'];
        $this->_params = $obj['params'];
        $this->_event_type = $obj['type'];
        $this->_event_name = RibbitCallbackNotification::getClassNameForEventType($this->_event_type);
    }
    /**
     * Returns the name of the event that occured
     *
     * @return string the name of the event
     */
    function getEventName()
    {
        return $this->_event_name;
    }
    /**
     * Returns the time that the event occured in format "yyyy-MM-DDTHH:nn:ssZ"
     *
     * @return string the time the event occured
     */
    function getTime()
    {
        return $this->_time;
    }
    /**
     * Returns the type of the event that occured
     *
     * @return string the type of event
     */
    function getEventType()
    {
        return $this->_event_type;
    }
    /**
     * Returns the resource affected by the event
     *
     * @return string the resource affected by the event
     */
    function getResource()
    {
        return $this->_resource;
    }
    /**
     * Returns an associative array of parameters describing the event
     *
     * @return array an associative array of parameters describing the event
     */
    function getParameters()
    {
        return $this->_params;
    }
    /**
     * Returns the value of a given parameter associated with the event
     *
     * @param string $param the name of the parameter being queried
     * @return string a value or an empty string if $param is not in the event
     */
    function getParameter($param)
    {
        return isset($this->_params[$param]) ? $this->_params[$param] : '';
    }
    /**
     * Parses a post body sent from the Ribbit REST server and returns an appropriate CallbackNotification
     *
     * @param string $param the name of the parameter being queried
     * @return string a value or an empty string if $param is not in the event
     */
    public static function parseCallBackNotification($body)
    {
        if (substr($body, 0, 5) != "Json=") {
            throw new RibbitException("Could not parse the Ribbit Callback Notification", "0");
        }
        $o = substr($body, 5);
        $obj = json_decode($o, true);
        if (!isset($obj['time']) || !isset($obj['resource']) || !RibbitUtil::is_assoc_array($obj['params']) || !isset($obj['type'])) {
            throw new RibbitException("Could not parse the Ribbit Callback Notification", "0");
        }
        $class = new ReflectionClass(RibbitCallbackNotification::getClassNameForEventType($obj['type']));
        return $class->newInstance($obj);
    }
    private static function getClassNameForEventType($eventType)
    {
        $result = "Callback";
        switch ($eventType) {
        case "DtmfReceived":
            $result = "DtmfReceived";
            break;

        case "CallLegAnswered":
            $result = "CallLegAnswered";
            break;

        case "PlayAnnouncementComplete":
            $result = "PlayAnnouncementComplete";
            break;

        case "CallInitiated":
            $result = "CallInitiated";
            break;

        case "RecordComplete":
            $result = "RecordLegComplete";
            break;

        case "RecordCallComplete":
            $result = "RecordCallComplete";
            break;

        case "CallLegHungUp":
            $result = "CallLegHungUp";
            break;

        case "CallRinging":
            $result = "CallRinging";
            break;

        case "cc_call_answered":
            $result = "CallAnswered";
            break;

        case "CallCompleted":
            $result = "CallCompleted";
            break;

        case "cc_call_hangup":
            $result = "CallHangup";
            break;

        case "cc_call_incoming":
            $result = "CallIncoming";
            break;

        case "cc_call_stopringing":
            $result = "CallStopRinging";
            break;

        case "cc_call_vmpickup":
            $result = "CallVMPickup";
            break;

        case "deletemessage":
            $result = "DeleteMessage";
            break;

        case "newcalllog":
            $result = "NewCallLog";
            break;

        case "newvoicemail":
            $result = "NewVoicemail";
            break;

        case "transcribedata":
            $result = "TranscribeData";
            break;
        }
        return "Ribbit" . $result . "Notification";
    }
}
?>