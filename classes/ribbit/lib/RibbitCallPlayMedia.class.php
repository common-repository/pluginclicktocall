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
class RibbitCallPlayMedia
{
    /**
     * The type of media to play, available in constants. For example, use "file" to play a file, or "string" to say an arbitrary string
     */
    var $type;
    /**
     * Either a URI to a file already saved on the Ribbit Platform, or a value to be said by the Text To Speech Engine
     */
    var $value;
    /**
     * The position in the file to start playing. Usually 0
     */
    var $offset;
    /**
     * The length of the file to play. Set to -1 to play the entire file
     */
    var $duration;
    /**
     * Constructor for RibbitCallPlayMedia
     *
     * @param string $type The type of media to play, available in constants. For example, use "file" to play a file, or "string" to say an arbitrary string
     * @param string $value Either a URI to a file already saved on the Ribbit Platform, or a value to be said by the Text To Speech Engine
     * @param int $offset The position in the file to start playing. Usually 0
     * @param int $duration The length of the file to play. Set to -1 to play the entire file
     */
    function RibbitCallPlayMedia($type, $value, $offset, $duration)
    {
        $this->type = $type;
        $this->value = $value;
        $this->offset = $offset;
        $this->duration = $duration;
    }
    /**
     * Gets the validation error messages for this object.
     */
    function getValidationMessage()
    {
        $exceptions = array();
        if (!RibbitUtil::is_valid_string($this->type)) {
            $exceptions[] = "type is required";
        }
        if (!RibbitUtil::is_valid_string($this->value)) {
            $exceptions[] = "value is required";
        }
        if (!RibbitUtil::is_valid_double($this->offset)) {
            $exceptions[] = "offset is required";
        }
        if (!RibbitUtil::is_valid_double($this->duration)) {
            $exceptions[] = "duration is required";
        }
        return implode(",", $exceptions);
    }
    /**
     * Creates an array from this object.
     */
    function toArray()
    {
        $output = array();
        if (isset($this->type)) {
            $output["type"] = $this->type;
        }
        if (isset($this->value)) {
            $output["value"] = $this->value;
        }
        if (isset($this->offset)) {
            $output["offset"] = $this->offset;
        }
        if (isset($this->duration)) {
            $output["duration"] = $this->duration;
        }
        return $output;
    }
}
?>