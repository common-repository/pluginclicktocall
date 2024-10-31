<?php
/*  Copyright 2011  DimGoTo  (email : info@dimgoto.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/* Ribbit SDK */
require_once("ribbit/Ribbit.php");

/**
 * Classe ClickToCall_Service.
 *
 * Implementation class telephony services
 *
 * @package Plugins
 * @subpackage ClickToCall
 * @version 2.0.5
 * @author Dimitri GOY
 * @copyright 2011 - DimGoTo
 * @link http://www.dimgoto.com/
 */
class ClickToCall_Service {
	
	public static function ribbit($params) {
		if (!is_array($params)) {
			throw new Exception('Paramètres obligatoires');
		} elseif (!isset($params['email'])) {
			throw new Exception('Paramètre Email obligatoire');
		} elseif (!isset($params['password'])) {
			throw new Exception('Paramètre Mot de passe obligatoire');
		} elseif (!isset($params['phonenumber_from'])) {
			throw new Exception('Paramètre Numéro de téléphone destinataire obligatoire');
		} elseif (!self::check_phonenumber($params['phonenumber_from'])) {
			throw new Exception('Paramètre Numéro de téléphone destinataire invalide');
		} elseif (!isset($params['phonenumber_to'])) {
			throw new Exception('Paramètre Numéro de téléphone à appeler obligatoire');
		} elseif (!self::check_phonenumber($params['phonenumber_to'])) {
			throw new Exception('Paramètre Numéro de téléphone à appeler invalide');
		}
		$ribbit = Ribbit::getInstance();
		try {
	        $ribbit->Login($params['email'],$params['password']);
	        if (!is_null($ribbit->getUserName())) {
                $call_id = $ribbit->Calls()->call(array($params['phonenumber_to'], $params['phonenumber_from']));
                $call_status = $ribbit->Calls()->getCall($call_id);
	        } else {
                throw new Exception('Echec Identification!');
	        }
		}catch(InvalidUserNameOrPasswordException $e) {
			 session_destroy();
	        throw new Exception('email ou mot de passe incorrecte, ou compte Ribbit invalide!');
	       //ribbit/ribbit_config.yaml consumer_key et secret_key
		} catch(RibbitException $e) {
	        session_destroy();
	        if ($e->getStatus()=="401") {
                throw new Exception('Ribbit n\'autorise pas votre session, vérifiez votre configuration!');
	        } else {
	            throw new Exception($e->getMessage());
	        }
		} 
	}
	
	public static function orange($params) {
		if (!is_array($params)) {
			throw new Exception('Paramètres obligatoires');
		} elseif (!isset($params['access_key'])) {
			throw new Exception('Paramètre Clef d\'accès API obligatoire');
		} elseif (!isset($params['phonenumber_from'])) {
			throw new Exception('Paramètre Numéro de téléphone destinataire obligatoire');
		} elseif (!self::check_phonenumber($params['phonenumber_from'])) {
			throw new Exception('Paramètre Numéro de téléphone destinataire invalide');
		} elseif (!isset($params['phonenumber_to'])) {
			throw new Exception('Paramètre Numéro de téléphone à appeler obligatoire');
		} elseif (!self::check_phonenumber($params['phonenumber_to'])) {
			throw new Exception('Paramètre Numéro de téléphone à appeler invalide');
		}
		try {
			$phonenumber_from = urlencode($params['phonenumber_from']);
			$access_key = $params['access_key'];
			$phonenumber_to = $params['phonenumber_to'];
			$url = 'http://call.alpha.orange-api.net/call/createCall.xml' 
			. '?id=' . $access_key
			. '&from=' . $phonenumber_from 
			. '&to=' . $phonenumber_to
			. '&private=true';
			$response = file_get_contents($url);
			$xml = simplexml_load_string($response);
			return 'Status: ' . $xml->status->status_msg;
		} catch (Exception $e) {
			throw $e;
		}
	}
	
	public static function ovh($params) {
		if (!is_array($params)) {
			throw new Exception('Paramètres obligatoires');
		} elseif (!isset($params['nic'])) {
			throw new Exception('Paramètre Identifiant Click2Call obligatoire');
		} elseif (!isset($params['password'])) {
			throw new Exception('Paramètre Mot de passe obligatoire');
		} elseif (!isset($params['phonenumber_from'])) {
			throw new Exception('Paramètre Numéro de téléphone destinataire obligatoire');
		} elseif (!self::check_phonenumber($params['phonenumber_from'])) {
			throw new Exception('Paramètre Numéro de téléphone destinataire invalide');
		} elseif (!isset($params['phonenumber_to'])) {
			throw new Exception('Paramètre Numéro de téléphone à appeler obligatoire');
		} elseif (!self::check_phonenumber($params['phonenumber_to'])) {
			throw new Exception('Paramètre Numéro de téléphone à appeler invalide');
		}
		try {
			
			$client = new SoapClient('https://www.ovh.com/soapi/soapi-re-1.17.wsdl');
			$client->telephonyClick2CallDo(
				$params['nic'],
				$params['password'],
				$params['phonenumber_to'],
				$params['phonenumber_from'],'');
    			return 'Status: Success';
		} catch(SoapFault $sf) {
			throw new Exception($sf->getMessage());
		}
	}
	
	public static function check_phonenumber($value) {
		$pattern = '/(\d)+/';
		$result = preg_match($pattern, $value);
		if ($result === false) {
			return false;
		} else {
			return ($result == 0) ? false : true;
		}
	}
	
	public static function check_openning($params) {
		$theday = (int) date('w');
		$thetime = (double) date('H')+2 . '.' . date('i');
		$result = false;
		if ($params['day-'.$theday] == true) {
			if ((int)$params['time-morning-start-'.$theday] == -1
			&& (int)$params['time-afternoon-end-'.$theday] == -1) {
				if ($thetime >= (double)$params['time-morning-start-'.$theday] 
				&& $thetime <= (double)$params['time-afternoon-end-'.$theday]) {
					$result = true;
				}
			} else {
				if ($thetime >= (double)$params['time-morning-start-'.$theday] 
				&& $thetime <= (double)$params['time-morning-end-'.$theday]) {
					$result = true;
				} else if ($thetime >= (double)$params['time-afternoon-start-'.$theday]
				&& $thetime <= (double)$params['time-afternoon-end-'.$theday]) {
					$result = true;
				}
			}
		}
		return  $result;
	}
}
?>
