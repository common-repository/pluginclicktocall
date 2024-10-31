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
 * The utility class for the Ribbit PHP Client Library
 *
 * @package Ribbit
 * @version 1.6.0
 * @author BT/Ribbit
 */
class RibbitUtil
{
    /**
     * Gets the identifier of the resource from the uri
     *
     * @param string $uri the full path of the resource
     * @return string the identifier of the resource
     */
    public static function get_id_from_uri($uri)
    {
        $i = strrpos($uri, "/");
        return trim(substr($uri, $i + 1, strlen($uri) - $i));
    }
    /**
     * Gets the identifier (as a float) of the resource from the uri
     *
     * @param string $uri the full path of the resource
     * @return float the identifier of the resource
     */
    public static function get_long_from_uri($uri)
    {
        return (float)RibbitUtil::get_id_from_uri($uri);
    }
    /**
     * Gets the inbound number from the id  (the id should be returned by create device)
     *
     * @param string $id the id of the resource
     * @return string the inbound number
     */
    public static function get_inbound_number_from_id($id)
    {
        $i = strrpos($id, ":");
        return str_replace("+", "", trim(substr($id, $i + 1, strlen($id) - $i)));
    }
    //	/**
    //	 * Converts an object to an associative array, removing null values.
    //	 *
    //	 * @param $obj an object
    //	 * @return array the object as an array.
    //	 */
    //	public static function object_to_array($obj){
    //		if(is_array($obj) || is_object($obj)){
    //	    	$result = array();
    //	    	foreach($obj as $key => $value){
    //	    		if ($value != null){
    //	      			$result[$key] = RibbitUtil::object_to_array($value);
    //	    		}
    //	    	}
    //	    	return $result;
    //	  }
    //	  return $obj;
    //	}
    
    /**
     * Formats a date suitable into a string suitable for the Ribbit Service
     *
     * @param string $d a date
     * @return string the formatted date
     */
    public static function format_date($d)
    {
        return RibbitUtil::format_date_for_requests($d) . "Z";
    }
    /**
     * Formats a date suitable into a string suitable for the Ribbit Service
     *
     * @param string $d a date
     * @return string the formatted date
     */
    public static function format_date_for_requests($d)
    {
        $t = date_parse($d);
        return (string)$t["year"] . "-" . (($t["month"] < 10) ? "0" . (string)$t["month"] : (string)$t["month"]) . "-" . (($t["day"] < 10) ? "0" . (string)$t["day"] : (string)$t["day"]) . "T" . (($t["hour"] < 10) ? "0" . (string)$t["hour"] : (string)$t["hour"]) . ":" . (($t["minute"] < 10) ? "0" . (string)$t["minute"] : (string)$t["minute"]) . ":" . (($t["second"] < 10) ? "0" . (string)$t["second"] : (string)$t["second"]);
    }
    /**
     * Detects whether a value is an associative array
     *
     * @param mixed $value any value
     * @return bool true if $var is an associative array, otherwise false.
     *
     */
    public static function is_assoc_array($value)
    {
        if (!is_array($value) || empty($value)) {
            return false;
        }
        foreach(array_keys($value) as $k => $v) {
            if ($k !== $v) {
                return true;
            }
        }
        return false;
    }
    /**
     * Checks if a string ends with a particular string
     *
     * @param string $string the string to search
     * @param string $ending
     * @return bool true if the string ends with the supplied ending
     */
    public static function str_ends_with($string, $ending)
    {
        $len = strlen($ending);
        $string_end = substr($string, strlen($string) - $len);
        return $string_end == $ending;
    }
    /**
     * Returns an array of error messages for paging parameters.
     *
     * @param int $start_index the start index
     * @param int $count the count
     * @return array an array for error messages
     */
    public static function check_paging_parameters($start_index = null, $count = null)
    {
        $exceptions = array();
        if (!is_null($start_index) && is_null($count)) {
            $exceptions[] = "If start_index is specified, count must be specified too";
        }
        if (!is_null($count) && is_null($start_index)) {
            $exceptions[] = "If count is specified, start_index must be specified too";
        }
        if (!is_null($start_index) && !is_null($count)) {
            if (!RibbitUtil::is_positive_integer($start_index)) {
                $exceptions[] = "start_index must be a positive integer";
            }
            if (!RibbitUtil::is_positive_integer($count)) {
                $exceptions[] = "count must be a positive integer";
            }
        }
        return (count($exceptions) == 0) ? null : implode(";", $exceptions);
    }
    /**
     * Returns an array of error messages for filtering parameters.
     *
     * @param string $filter_by the attribute to filter by
     * @param string $filter_value the value to filter on
     * @return array an array for error messages
     */
    public static function check_filter_parameters($filter_by = null, $filter_value = null)
    {
        $exceptions = array();
        if (!is_null($filter_by) && is_null($filter_value)) {
            $exceptions[] = "If filter_by is specified, filter_value must be specified too";
        }
        if (!is_null($filter_value) && is_null($filter_by)) {
            $exceptions[] = "If filter_value is specified, filter_by must be specified too";
        }
        if (!is_null($filter_by) && !is_null($filter_value)) {
            if (!RibbitUtil::is_valid_string_if_defined($filter_by)) {
                $exceptions[] = "When defined, filter_by must be a valid filtering property of the resource";
            }
            if (!RibbitUtil::is_valid_string_if_defined($filter_value)) {
                $exceptions[] = "When defined, filter_value  must be a string of one or more characters";
            }
        }
        return (count($exceptions) == 0) ? null : implode(";", $exceptions);
    }
    /**
     * Checks if the supplied value is a positive integer.
     *
     * @param mixed $v the value to check.
     * @return bool true if the value is a positive integer.
     */
    public static function is_positive_integer($v)
    {
        return is_int($v) && $v >= 0;
    }
    /**
     * Checks if the supplied value is a positive integer.
     *
     * @param mixed $v the value to check.
     * @return bool true if the value is a positive integer.
     */
    public static function is_positive_integer_if_defined($v)
    {
        return is_null($v) || empty($v) || RibbitUtil::is_positive_integer($v);
    }
    /**
     * Checks if the supplied value is a valid notification url.
     *
     * @param string $url the value to check.
     * @return bool true if the value is a valid notification url.
     */
    public static function is_valid_notification_url($url)
    {
        $url = substr($url, -1) == "/" ? substr($url, 0, -1) : $url;
        if (!$url || $url == "") return false;
        if (!($parts = @parse_url($url))) return false;
        else {
            if ($parts[scheme] != "http" && $parts[scheme] != "https") return false;
            else if (!eregi("^[0-9a-z]([-.]?[0-9a-z])*.[a-z]{2,4}$", $parts[host], $regs)) return false;
            else if (!eregi("^([0-9a-z-]|[_])*$", $parts[user], $regs)) return false;
            else if (!eregi("^([0-9a-z-]|[_])*$", $parts[pass], $regs)) return false;
            else if (!eregi("^[0-9a-z/_.@~-]*$", $parts[path], $regs)) return false;
            else if (!eregi("^[0-9a-z?&=#,]*$", $parts[query], $regs)) return false;
        }
        return true;
    }
    /**
     * Builds a uri to redirect to based on the current context.
     *
     * @param string $uri the seed uri to redirect to
     * @return string the uri to redirect to
     */
    public static function redirect_uri_builder($uri)
    {
        if (!is_null($uri)) {
            if (substr($uri, 0, 4) != 'http') {
                $ssl = isset($SERVER["HTTPS"]);
                $serverPort = $_SERVER['SERVER_PORT'];
                $port = (($ssl && $_SERVER['SERVER_PORT'] == "443") || $_SERVER['SERVER_PORT'] == "80") ? "" : ":" + $_SERVER['SERVER_PORT'];
                $path = "";
                if ($uri == "" || $uri == ".") {
                    $path = $_SERVER["REQUEST_URI"];
                } else if (substr($uri, 0, 1) == "?") {
                    $path = $_SERVER["REQUEST_URI"] . $uri;
                } else if (substr($uri, 0, 1) != "/") {
                    $path = $_SERVER["REQUEST_URI"];
                    $path = substr($path, 0, strrpos($path, '/')) . '/' . $uri;
                }
                $host = explode(":", $_SERVER["HTTP_HOST"]);
                $uri = ($ssl ? "https://" : "http://") . $host[0] . $port . $path;
            }
            $query_pos = strpos($uri, '?');
            if ($query_pos > 0) {
                $query = substr($uri, $query_pos + 1);
                $queries = explode("&", $query);
                $newquery = "";
                foreach($queries as $q) {
                    if (substr($q, 0, 14) != "oauth_approval") {
                        $newquery = $newquery . "&" . $q;
                    }
                }
                $newquery = strlen($newquery) > 0 ? "?" . substr($newquery, 1) : "";
                $uri = substr($uri, 0, $query_pos) . $newquery;
            }
        } else {
            $uri = "";
        }
        return $uri;
    }
    /**
     * Checks if the supplied value is an array of at least one item
     *
     * @param $v mixed the value to check.
     * @return bool true if the value is an array of at least one item
     */
    public static function is_non_empty_array($v)
    {
        return is_array($v) && count($v) > 0;
    }
    /**
     * Checks if the supplied value is an array of at least one item, if it has been set.
     *
     * @param $v mixed the value to check.
     * @return bool true if the value is an array of at least one item
     */
    public static function is_non_empty_array_if_defined($v)
    {
        return !isset($v) || RibbitUtil::is_non_empty_array($v);
    }
    /**
     * Checks if the supplied value is an array, if it has been set.
     *
     * @param $v mixed the value to check.
     * @return bool true if the value is an array or not defined
     */
    public static function is_array_if_defined($v)
    {
        return !isset($v) || is_array($v);
    }
    /**
     * Checks if the supplied value is a string
     *
     * @param $v string the value to check.
     * @return bool true if the value is a string
     */
    public static function is_valid_string($v)
    {
        return is_string($v) && strlen($v) > 0;
    }
    /**
     * Checks if the supplied value is a string of at least one character in length
     *
     * @param $v mixed the value to check.
     * @return bool true if the value is a string of at least one character in length
     */
    public static function is_valid_string_if_defined($v)
    {
        return !isset($v) || RibbitUtil::is_valid_string($v);
    }
    public static function is_valid_double($v)
    {
        return is_numeric($v);
    }
    public static function is_valid_double_if_defined($v)
    {
        return !isset($v) || is_valid_double($v);
    }
    /**
     * Checks if the supplied value is a bool
     *
     * @param $v mixed the value to check.
     * @return bool true if the value is a bool
     */
    public static function is_valid_bool_if_defined($v)
    {
        return !isset($v) || is_bool($v);
    }
    public static function is_date_if_defined($v)
    {
        return is_null($v) || !isset($v) || strtotime($v);
    }
    public static function is_long_if_defined($v)
    {
        return empty($v) || is_long($v);
    }
    public static function is_valid_base64_data($v)
    {
        return preg_match('%^[a-zA-Z0-9/+]*={0,2}$%', $v);
    }
    public static function current_millis()
    {
        list($usec, $sec) = explode(" ", microtime());
        return round(((float)$usec + (float)$sec) * 1000);
    }
}
?>