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
 * This Callback Notification is received when a call leg has answered
 */
class RibbitCallLegAnsweredNotification extends RibbitCallbackNotification
{
    /**
     * Called internally.
     * Deserializes the JSON sent from the Ribbit REST server
     */
    public function RibbitCallLegAnsweredNotification($obj)
    {
        parent::RibbitCallbackNotification($obj);
    }
    /**
     * The URI of the call leg
     *
     * @return string The URI of the call leg
     */
    public function getCallLegUri()
    {
        $result = $this->getParameter("call_leg_uri");
        return $result;
    }
    /**
     * The time the status changed to answered
     *
     * @return date The time the status changed to answered
     */
    public function getAnsweredTimestamp()
    {
        $result = $this->getParameter("answered_timestamp");
        return $result;
    }
    /**
     * The status of a leg within a call
     *
     * @return string The status of a leg within a call
     */
    public function getCallLegStatus()
    {
        $result = $this->getParameter("call_leg_status");
        return $result;
    }
}
?>