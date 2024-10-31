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
class RibbitCallLegDtmfRequest
{
    /**
     * Set this to true to disregard any keypresses prior to audio being played
     */
    var $flush;
    /**
     * The maximum number of key presses to collect
     */
    var $max_digits;
    /**
     * Stop recording when a keypad digit, or digits, are pressed
     */
    var $stoptones;
    /**
     * The number of milliseconds after which the service should stop collecting digits
     */
    var $time_out;
    /**
     * The maximum length of time (in milliseconds) to wait between keypresses without stopping digit collection
     */
    var $max_interval;
    /**
     * Constructor for RibbitCallLegDtmfRequest
     *
     * @param boolean $flush Set this to true to disregard any keypresses prior to audio being played
     * @param int $max_digits The maximum number of key presses to collect
     * @param string $stoptones Stop recording when a keypad digit, or digits, are pressed
     * @param int $time_out The number of milliseconds after which the service should stop collecting digits
     * @param int $max_interval The maximum length of time (in milliseconds) to wait between keypresses without stopping digit collection
     */
    function RibbitCallLegDtmfRequest($flush, $max_digits, $stoptones, $time_out, $max_interval)
    {
        $this->flush = $flush;
        $this->max_digits = $max_digits;
        $this->stoptones = $stoptones;
        $this->time_out = $time_out;
        $this->max_interval = $max_interval;
    }
    /**
     * Gets the validation error messages for this object.
     */
    function getValidationMessage()
    {
        $exceptions = array();
        if (!is_bool($this->flush)) {
            $exceptions[] = "flush is required";
        }
        if (!RibbitUtil::is_positive_integer($this->max_digits)) {
            $exceptions[] = "max_digits is required";
        }
        if (!RibbitUtil::is_valid_string_if_defined($this->stoptones)) {
            $exceptions[] = "When defined, stoptones must be a string of one or more characters";
        }
        if (!RibbitUtil::is_positive_integer($this->time_out)) {
            $exceptions[] = "time_out is required";
        }
        if (!RibbitUtil::is_positive_integer($this->max_interval)) {
            $exceptions[] = "max_interval is required";
        }
        return implode(",", $exceptions);
    }
    /**
     * Creates an array from this object.
     */
    function toArray()
    {
        $output = array();
        if (isset($this->flush)) {
            $output["flush"] = $this->flush;
        }
        if (isset($this->max_digits)) {
            $output["maxDigits"] = $this->max_digits;
        }
        if (isset($this->stoptones)) {
            $output["stopTones"] = $this->stoptones;
        }
        if (isset($this->time_out)) {
            $output["timeOut"] = $this->time_out;
        }
        if (isset($this->max_interval)) {
            $output["maxInterval"] = $this->max_interval;
        }
        return $output;
    }
}
?>