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
class RibbitCallPlayRequest
{
    /**
     * A collection of files and/or Text To Speech elements
     */
    var $media;
    /**
     * A transaction identifier
     */
    var $transaction_id;
    /**
     * Stop playing media when a keypad digit, or digits, are pressed
     */
    var $stoptones;
    /**
     * Set this to true to disregard any keypresses prior to audio being played
     */
    var $flush;
    /**
     * Constructor for RibbitCallPlayRequest
     *
     * @param RibbitCallPlayMedia[] $media A collection of files and/or Text To Speech elements
     * @param string $transaction_id A transaction identifier
     * @param string $stoptones Stop playing media when a keypad digit, or digits, are pressed
     * @param boolean $flush Set this to true to disregard any keypresses prior to audio being played
     */
    function RibbitCallPlayRequest($media, $transaction_id, $stoptones, $flush)
    {
        $this->media = $media;
        $this->transaction_id = $transaction_id;
        $this->stoptones = $stoptones;
        $this->flush = $flush;
    }
    /**
     * Gets the validation error messages for this object.
     */
    function getValidationMessage()
    {
        $exceptions = array();
        if (isset($this->media)) {
            if (!RibbitUtil::is_non_empty_array_if_defined($this->media)) {
                $exceptions[] = "media must be an array containing instances of RibbitCallPlayMedia";
            }
            for ($i = 0; $i < count($this->media); $i++) {
                if (!($this->media[$i] instanceof RibbitCallPlayMedia)) {
                    $exceptions[] = "media contains objects that are not instances of RibbitCallPlayMedia";
                    break;
                }
            }
            if (count($exceptions) == 0) {
                for ($i = 0; $i < count($this->media); $i++) {
                    $x = $this->media[$i]->getValidationMessage();
                    if ($x != "") {
                        $exceptions[] = $x;
                    }
                }
            }
        }
        if (!RibbitUtil::is_valid_string_if_defined($this->transaction_id)) {
            $exceptions[] = "When defined, transaction_id must be a string of one or more characters";
        }
        if (!RibbitUtil::is_valid_string_if_defined($this->stoptones)) {
            $exceptions[] = "When defined, stoptones must be a string of one or more characters";
        }
        if (!is_bool($this->flush)) {
            $exceptions[] = "flush is required";
        }
        return implode(",", $exceptions);
    }
    /**
     * Creates an array from this object.
     */
    function toArray()
    {
        $output = array();
        if (isset($this->media)) {
            $arr = array();
            for ($i = 0; $i < count($this->media); $i++) {
                $arr[] = $this->media[$i]->toArray();
            }
            $output["media"] = $arr;
        }
        if (isset($this->transaction_id)) {
            $output["transactionId"] = $this->transaction_id;
        }
        if (isset($this->stoptones)) {
            $output["stoptones"] = $this->stoptones;
        }
        if (isset($this->flush)) {
            $output["flush"] = $this->flush;
        }
        return $output;
    }
}
?>