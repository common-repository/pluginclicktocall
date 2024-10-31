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
class RibbitCallRecordRequest
{
    /**
     * The file to record to, a relative URI such as media/domain/myfolder/recording.mp3
     */
    var $file;
    /**
     * Set to true to append the recording to an existing file
     */
    var $append;
    /**
     * Set this to true to disregard any keypresses prior to audio being played
     */
    var $flush;
    /**
     * The length of the recording to make, in seconds
     */
    var $duration;
    /**
     * Stop recording when a keypad digit, or digits, are pressed
     */
    var $stoptones;
    /**
     * Constructor for RibbitCallRecordRequest
     *
     * @param string $file The file to record to, a relative URI such as media/domain/myfolder/recording.mp3
     * @param boolean $append Set to true to append the recording to an existing file
     * @param boolean $flush Set this to true to disregard any keypresses prior to audio being played
     * @param int $duration The length of the recording to make, in seconds
     * @param string $stoptones Stop recording when a keypad digit, or digits, are pressed
     */
    function RibbitCallRecordRequest($file, $append, $flush, $duration, $stoptones)
    {
        $this->file = $file;
        $this->append = $append;
        $this->flush = $flush;
        $this->duration = $duration;
        $this->stoptones = $stoptones;
    }
    /**
     * Gets the validation error messages for this object.
     */
    function getValidationMessage()
    {
        $exceptions = array();
        if (!RibbitUtil::is_valid_string($this->file)) {
            $exceptions[] = "file is required";
        }
        if (!RibbitUtil::is_valid_bool_if_defined($this->append)) {
            $exceptions[] = "When defined, append must be boolean";
        }
        if (!is_bool($this->flush)) {
            $exceptions[] = "flush is required";
        }
        if (!RibbitUtil::is_positive_integer_if_defined($this->duration)) {
            $exceptions[] = "When defined, duration must be a positive integer";
        }
        if (!RibbitUtil::is_valid_string_if_defined($this->stoptones)) {
            $exceptions[] = "When defined, stoptones must be a string of one or more characters";
        }
        return implode(",", $exceptions);
    }
    /**
     * Creates an array from this object.
     */
    function toArray()
    {
        $output = array();
        if (isset($this->file)) {
            $output["file"] = $this->file;
        }
        if (isset($this->append)) {
            $output["append"] = $this->append;
        }
        if (isset($this->flush)) {
            $output["flush"] = $this->flush;
        }
        if (isset($this->duration)) {
            $output["duration"] = $this->duration;
        }
        if (isset($this->stoptones)) {
            $output["stoptones"] = $this->stoptones;
        }
        return $output;
    }
}
?>