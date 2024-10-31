<?php
/*
Plugin Name: ClickToCall
Plugin URI: http://www.dimgoto.com/open-source/wordpress/plugins/plugin-clicktocall
Description: Ce plugin permet à vos internet de se faire appeler par téléphone en indiquant leur numéro de téléphone. 
Installer, configurer, insérer dans votre Sidebar, page, article. 
Les Services acceptés sont OVH, Orange, veuillez nous contacter pour enrichir cette liste.
Version: 2.0.5
Author: Dimitri GOY
Author URI: http://www.dimgoto.com
*/

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

/* Public Constants */
define('PLUGINCLICKTOCALL_MINVERSION', '2.6');
define('PLUGINCLICKTOCALL_NAME', 'pluginclicktocall');

/* ClickToCall_Service Class */
require_once('classes/clicktocall-service.php');
/* ClickToCall_Widget Class */
require_once('clicktocall-widget.php');

/* Check WordPress's Version? */
if (!version_compare(get_bloginfo('version'), PLUGINCLICKTOCALL_MINVERSION, '>='))
	die(sprintf('Ce plugin requiere WordPress %s ou supérieur'.get_bloginfo( 'version' ), PLUGINCLICKTOCALL_MINVERSION));

if (is_admin()) {
	register_activation_hook(__FILE__, 'wp_clicktocall_activate');
	register_deactivation_hook(__FILE__, 'wp_clicktocall_deactivate');
	if (function_exists('register_uninstall_hook'))
	    register_uninstall_hook(__FILE__, 'wp_clicktocall_uninstall');
	    		
	add_filter('contextual_help', 'wp_clicktocall_help');
	add_action('admin_menu', 'wp_clicktocall_admin_menu');
	add_action('admin_notices', 'wp_clicktocall_admin_notices');
	wp_enqueue_style(PLUGINCLICKTOCALL_NAME.'_style', WP_PLUGIN_URL.'/'.str_replace('\\', '/', dirname(plugin_basename(__FILE__))).'/admin-style.css', array(), false, 'screen');
} else {
	
}
add_shortcode('clicktocall', 'wp_clicktocall_shortcode');	
function wp_clicktocall_activate() {
	$option = get_option(PLUGINCLICKTOCALL_NAME);
	if (empty($option))
		add_option(PLUGINCLICKTOCALL_NAME, array());
}
function wp_clicktocall_deactivate() {}
function wp_clicktocall_uninstall() {
	delete_option(PLUGINCLICKTOCALL_NAME);
}
function wp_clicktocall_help($text) {
	if($_GET['page'] == 'pluginclicktocall') {
		$text = '<h5>Aide et Notice d\'utilisation</h5>';
		$text .= '<p>Ce plugin vous permet à vos internautes de vous appeler par un simple click.';
		$text .= 'Pour insérer Click To Call utilisez l\'une des posibilités suivantes:';
		$text .= '<ol>';
		$text .= '<li>Widget, insérez le dans la sidebar souhaitée</li>';
		$text .= '<li>Page ou article, en utilisant le code suivant <strong>[clicktocall/]</strong></li>';
		$text .= '<li>Theme ou modèle, en utilisant le code suivant <strong>&lt;?php if (function_exists(\'wp_clicktocall_insert_to\')) {wp_clicktocall_insert_to();}?&gt;</strong></li>';
		$text .= '</ol>';
		$text .= '</p>';
		$text .= '<p><strong>Configuration du service</strong><br /><br />';
		$text .= 'Sélectionnez le service souhaité, les services disponibles sont OVH, Orange. Pour agrandir cette liste contactez nous.';
		$text .= 'Puis renseignez la configuration du service, n\'hésitez pas à utiliser le <strong>Test</strong> pour vous assurer du bon fonctionnement.';
		$text .= 'Et enfin, enregistrez votre configuration une fois validée.';
		$text .= '</p>';
	}
	return $text;
}
function wp_clicktocall_admin_menu() {
	/* Add Submenu Admin Plugin */
	add_submenu_page(
		'plugins.php',
		'Click To Call',
		'Click To Call',
		'activate_plugins',
		PLUGINCLICKTOCALL_NAME,
		'wp_clicktocall_option'
	);
}
function wp_clicktocall_admin_notices() {
	$notices = get_transient(PLUGINCLICKTOCALL_NAME.'_notices');
	if (!empty($notices) && is_array($notices)) {
		$html = '<div id="'.PLUGINCLICKTOCALL_NAME.'_notices" class="'.$notices[0].' fade">';
		if ($notices[0] == 'error')
	        $html .= '<h3>Erreur</h3>';
        $html .= '<p>'.$notices[1].'</p></div>';
        $html .= '<script type="text/javascript">';
        $html .= '(function($) {';
        $html .= 'var  tm'.PLUGINCLICKTOCALL_NAME.'_notices = window.setInterval(function() {';
        $html .= '$(\'#'.PLUGINCLICKTOCALL_NAME.'_notices\').fadeOut(1000);';
        $html .= 'window.clearInterval(tm'.PLUGINCLICKTOCALL_NAME.'_notices);';
        $html .= 'tm'.PLUGINCLICKTOCALL_NAME.'_notices = null;';
        $html .= '},10000);';
        $html .= '})(jQuery);';
        $html .= '</script>';
		
		delete_transient(PLUGINCLICKTOCALL_NAME.'_notices');	
		echo $html;
	}
}
function wp_clicktocall_option() {
	$action = $_POST[PLUGINCLICKTOCALL_NAME.'_action'];
	if (!empty($action)) {
		$services = array('ovh','orange');
		$service = $_POST[PLUGINCLICKTOCALL_NAME.'_service'];
		if (!empty($service) && in_array($service, $services, true)) {
			switch ($service) {
				case 'ovh':
					$option = array(
						'service'			=> 'ovh',
						'nic'				=> $_POST[PLUGINCLICKTOCALL_NAME.'_ovh_nic'],
						'password'			=> $_POST[PLUGINCLICKTOCALL_NAME.'_ovh_password'],
						'phonenumber_to'	=> $_POST[PLUGINCLICKTOCALL_NAME.'_ovh_phonenumber_to']
					);
					break;
				case 'orange':
					$option = array(
						'service'			=> 'orange',
						'access_key'		=> $_POST[PLUGINCLICKTOCALL_NAME.'_orange_access_key'],
						'phonenumber_to'	=> $_POST[PLUGINCLICKTOCALL_NAME.'_orange_to']
					);
					break;
				case 'ribbit':
					$option = array(
						'service'			=> 'ribbit',
						'email'				=> $_POST[PLUGINCLICKTOCALL_NAME.'_ribbit_email'],
						'password'			=> $_POST[PLUGINCLICKTOCALL_NAME.'_ribbit_password'],
						'phonenumber_to'	=> $_POST[PLUGINCLICKTOCALL_NAME.'_ribbit_telephone_one']
					);
					break;
				default:
					set_transient(PLUGINCLICKTOCALL_NAME.'_notices', array('error','Service invalide!'));
					break;
			}
			$i = 0;
			while ($i <= 6) {
				$option = array_merge($option, array('day-'.$i => (bool) $_POST[PLUGINCLICKTOCALL_NAME.'_day_'.$i]));
				$option = array_merge($option, array('time-morning-start-'.$i => (int) $_POST[PLUGINCLICKTOCALL_NAME.'_time_morning_start_'.$i]));
				$option = array_merge($option, array('time-morning-end-'.$i => (int) $_POST[PLUGINCLICKTOCALL_NAME.'_time_morning_end_'.$i]));
				$option = array_merge($option, array('time-afternoon-start-'.$i => (int) $_POST[PLUGINCLICKTOCALL_NAME.'_time_afternoon_start_'.$i]));
				$option = array_merge($option, array('time-afternoon-end-'.$i => (int) $_POST[PLUGINCLICKTOCALL_NAME.'_time_afternoon_end_'.$i]));
				$i++;
			}
		}
		switch ($action) {
			case 'save':
				if (isset($option) && !empty($option)) {
					update_option(PLUGINCLICKTOCALL_NAME, $option);
					set_transient(PLUGINCLICKTOCALL_NAME.'_notices', array('updated','Configuration enregistrée!'));
				} else {
					set_transient(PLUGINCLICKTOCALL_NAME.'_notices', array('error','Impossible d\'enregistrer la configuration!'));
				}
				break;
			case 'delete':
				update_option(PLUGINCLICKTOCALL_NAME, array());
				set_transient(PLUGINCLICKTOCALL_NAME.'_notices', array('updated','Configuration supprimée!'));
				break;
			case 'testovh':
				$params = array_merge($option, array('phonenumber_from'=>$_POST[PLUGINCLICKTOCALL_NAME.'_ovh_phonenumber_from']));
				try {
					$result = ClickToCall_Service::ovh($params);
					set_transient(PLUGINCLICKTOCALL_NAME.'_notices', array('updated','Test OK!'));
				} catch (Exception $e) {
					set_transient(PLUGINCLICKTOCALL_NAME.'_notices', array('error',$e->getMessage()));
				}
				break;
			case 'testorange':
				$params = array_merge($option, array('phonenumber_from'=>$_POST[PLUGINCLICKTOCALL_NAME.'_orange_from']));
				try {
					$result = ClickToCall_Service::orange($params);
					set_transient(PLUGINCLICKTOCALL_NAME.'_notices', array('updated','Test OK!'));
				} catch (Exception $e) {
					set_transient(PLUGINCLICKTOCALL_NAME.'_notices', array('error',$e->getMessage()));
				}
				break;
			case 'testribbit':
				$params = array_merge($option, array('phonenomber_from'=>$_POST[PLUGINCLICKTOCALL_NAME.'_ribbit_telephone_two']));
				try {
					$result = ClickToCall_Service::ribbit($params);
					set_transient(PLUGINCLICKTOCALL_NAME.'_notices', array('updated','Test OK!'));
				} catch (Exception $e) {
					set_transient(PLUGINCLICKTOCALL_NAME.'_notices', array('error',$e->getMessage()));
				}
				break;
		}
	}

	/* Gets Plugin Settings, If exists */
	if (empty($option))
		$option = get_option(PLUGINCLICKTOCALL_NAME);

	/* Display Option */
	$html = '';
	$html .= '<div class="wrap" id="'.PLUGINCLICKTOCALL_NAME.'">';		
	$html .= '<div class="icon32" id="icon-edit-pages"><br/></div>';
	$html .= '<h2>Click To Call Options</h2>';
	$html .= '<p class="description">Ce Plugin permet à vos internautes de vous appeler via votre site Web par un simple click.</p>';
	$html .= '<form name="'.PLUGINCLICKTOCALL_NAME.'_form" method="post" action="' . str_replace('%7E', '~', $_SERVER['REQUEST_URI']) . '">';
	$html .= '<h3>Service</h3>';
	$html .= '<p class="description">sélectionnez un service, saisissez la configuration, effectuez un test pour valider la configuration, puis enregistrez.</p>';
	$html .= '<p><input type="radio" name="'.PLUGINCLICKTOCALL_NAME.'_service" id="service-ovh" value="ovh" '.(($option['service'] == 'ovh') ? 'checked="checked"': '').'/><label for="service-ovh">OVH</label></p>';
	$html .= '<p><input type="radio" name="'.PLUGINCLICKTOCALL_NAME.'_service" id="service-orange" value="orange" '.(($option['service'] == 'orange') ? 'checked="checked"': '').'/><label for="service-orange">Orange</label></p>';
	$html .= '<p><input type="radio" name="'.PLUGINCLICKTOCALL_NAME.'_service" id="service-ribbit" value="ribbit" '.(($option['service'] == 'ribbit') ? 'checked="checked"': '').'/><label for="service-ribbit">Ribbit</label></p>';
	$html .= '<h4>Heures d\'ouverture</h4>';
	$html .= '<p><input type="checkbox" name="'.PLUGINCLICKTOCALL_NAME.'_day_1" id="'.PLUGINCLICKTOCALL_NAME.'_day_1" '.(($option['day-1'] == true) ? 'checked="checked"' : '').'/><label for="'.PLUGINCLICKTOCALL_NAME.'_day_1">Lundi</label>';
	$html .= '<strong>matin</strong>: début: <select name="'.PLUGINCLICKTOCALL_NAME.'_time_morning_start_1" id="'.PLUGINCLICKTOCALL_NAME.'_time_morning_start_1">';
	$i = -1;
	while ($i < 24) {
		$html .= '<option value="'.$i.'"'.(($option['day-1'] == true && $option['time-morning-start-1']==$i) ? ' selected="selected"' : '').'>'.(($i==-1) ? '' : $i).'</option>';
		$i++;
	}
	$html .= '</select>';
	$html .= ' fin: <select name="'.PLUGINCLICKTOCALL_NAME.'_time_morning_end_1" id="'.PLUGINCLICKTOCALL_NAME.'_time_morning_end_1">';
	$i = -1;
	while ($i < 24) {
		$html .= '<option value="'.$i.'"'.(($option['day-1'] == true && $option['time-morning-end-1']==$i) ? ' selected="selected"' : '').'>'.(($i==-1) ? '' : $i).'</option>';
		$i++;
	}
	$html .= '</select>';
	$html .= '<strong>midi</strong> début: <select name="'.PLUGINCLICKTOCALL_NAME.'_time_afternoon_start_1" id="'.PLUGINCLICKTOCALL_NAME.'_time_afternoon_start_1">';
	$i = -1;
	while ($i < 24) {
		$html .= '<option value="'.$i.'"'.(($option['day-1'] == true && $option['time-afternoon-start-1']==$i) ? ' selected="selected"' : '').'>'.(($i==-1) ? '' : $i).'</option>';
		$i++;
	}
	$html .= '</select>';
	$html .= ' fin: <select name="'.PLUGINCLICKTOCALL_NAME.'_time_afternoon_end_1" id="'.PLUGINCLICKTOCALL_NAME.'_time_afternoon_end_1">';
	$i = -1;
	while ($i < 24) {
		$html .= '<option value="'.$i.'"'.(($option['day-1'] == true && $option['time-afternoon-end-1']==$i) ? ' selected="selected"' : '').'>'.(($i==-1) ? '' : $i).'</option>';
		$i++;
	}
	$html .= '</select>';
	$html .= '</p>';
	$html .= '<p><input type="checkbox" name="'.PLUGINCLICKTOCALL_NAME.'_day_2" id="'.PLUGINCLICKTOCALL_NAME.'_day_2" '.(($option['day-2'] == true) ? 'checked="checked"' : '').'/><label for="'.PLUGINCLICKTOCALL_NAME.'_day_2">Mardi</label>';
	$html .= '<strong>matin</strong>: début: <select name="'.PLUGINCLICKTOCALL_NAME.'_time_morning_start_2" id="'.PLUGINCLICKTOCALL_NAME.'_time_morning_start_2">';
	$i = -1;
	while ($i < 24) {
		$html .= '<option value="'.$i.'"'.(($option['day-2'] == true && $option['time-morning-start-2']==$i) ? ' selected="selected"' : '').'>'.(($i==-1) ? '' : $i).'</option>';
		$i++;
	}
	$html .= '</select>';
	$html .= ' fin: <select name="'.PLUGINCLICKTOCALL_NAME.'_time_morning_end_2" id="'.PLUGINCLICKTOCALL_NAME.'_time_morning_end_2">';
	$i = -1;
	while ($i < 24) {
		$html .= '<option value="'.$i.'"'.(($option['day-2'] == true && $option['time-morning-end-2']==$i) ? ' selected="selected"' : '').'>'.(($i==-1) ? '' : $i).'</option>';
		$i++;
	}
	$html .= '</select>';
	$html .= '<strong>midi</strong> début: <select name="'.PLUGINCLICKTOCALL_NAME.'_time_afternoon_start_2" id="'.PLUGINCLICKTOCALL_NAME.'_time_afternoon_start_2">';
	$i = -1;
	while ($i < 24) {
		$html .= '<option value="'.$i.'"'.(($option['day-2'] == true && $option['time-afternoon-start-2']==$i) ? ' selected="selected"' : '').'>'.(($i==-1) ? '' : $i).'</option>';
		$i++;
	}
	$html .= '</select>';
	$html .= ' fin: <select name="'.PLUGINCLICKTOCALL_NAME.'_time_afternoon_end_2" id="'.PLUGINCLICKTOCALL_NAME.'_time_afternoon_end_2">';
	$i = -1;
	while ($i < 24) {
		$html .= '<option value="'.$i.'"'.(($option['day-2'] == true && $option['time-afternoon-end-2']==$i) ? ' selected="selected"' : '').'>'.(($i==-1) ? '' : $i).'</option>';
		$i++;
	}
	$html .= '</select>';
	$html .= '</p>';
	$html .= '<p><input type="checkbox" name="'.PLUGINCLICKTOCALL_NAME.'_day_3" id="'.PLUGINCLICKTOCALL_NAME.'_day_3" '.(($option['day-3'] == true) ? 'checked="checked"' : '').'/><label for="'.PLUGINCLICKTOCALL_NAME.'_day_3">Mercredi</label>';
	$html .= '<strong>matin</strong>: début: <select name="'.PLUGINCLICKTOCALL_NAME.'_time_morning_start_3" id="'.PLUGINCLICKTOCALL_NAME.'_time_morning_start_3">';
	$i = -1;
	while ($i < 24) {
		$html .= '<option value="'.$i.'"'.(($option['day-3'] == true && $option['time-morning-start-3']==$i) ? ' selected="selected"' : '').'>'.(($i==-1) ? '' : $i).'</option>';
		$i++;
	}
	$html .= '</select>';
	$html .= ' fin: <select name="'.PLUGINCLICKTOCALL_NAME.'_time_morning_end_3" id="'.PLUGINCLICKTOCALL_NAME.'_time_morning_end_3">';
	$i = -1;
	while ($i < 24) {
		$html .= '<option value="'.$i.'"'.(($option['day-3'] == true && $option['time-morning-end-3']==$i) ? ' selected="selected"' : '').'>'.(($i==-1) ? '' : $i).'</option>';
		$i++;
	}
	$html .= '</select>';
	$html .= '<strong>midi</strong> début: <select name="'.PLUGINCLICKTOCALL_NAME.'_time_afternoon_start_3" id="'.PLUGINCLICKTOCALL_NAME.'_time_afternoon_start_3">';
	$i = -1;
	while ($i < 24) {
		$html .= '<option value="'.$i.'"'.(($option['day-3'] == true && $option['time-afternoon-start-3']==$i) ? ' selected="selected"' : '').'>'.(($i==-1) ? '' : $i).'</option>';
		$i++;
	}
	$html .= '</select>';
	$html .= ' fin: <select name="'.PLUGINCLICKTOCALL_NAME.'_time_afternoon_end_3" id="'.PLUGINCLICKTOCALL_NAME.'_time_afternoon_end_3">';
	$i = -1;
	while ($i < 24) {
		$html .= '<option value="'.$i.'"'.(($option['day-3'] == true && $option['time-afternoon-end-3']==$i) ? ' selected="selected"' : '').'>'.(($i==-1) ? '' : $i).'</option>';
		$i++;
	}
	$html .= '</select>';
	$html .= '</p>';
	$html .= '<p><input type="checkbox" name="'.PLUGINCLICKTOCALL_NAME.'_day_4" id="'.PLUGINCLICKTOCALL_NAME.'_day_4" '.(($option['day-4'] == true) ? 'checked="checked"' : '').'/><label for="'.PLUGINCLICKTOCALL_NAME.'_day_4">Jeudi</label>';
	$html .= '<strong>matin</strong>: début: <select name="'.PLUGINCLICKTOCALL_NAME.'_time_morning_start_4" id="'.PLUGINCLICKTOCALL_NAME.'_time_morning_start_4">';
	$i = -1;
	while ($i < 24) {
		$html .= '<option value="'.$i.'"'.(($option['day-4'] == true && $option['time-morning-start-4']==$i) ? ' selected="selected"' : '').'>'.(($i==-1) ? '' : $i).'</option>';
		$i++;
	}
	$html .= '</select>';
	$html .= ' fin: <select name="'.PLUGINCLICKTOCALL_NAME.'_time_morning_end_4" id="'.PLUGINCLICKTOCALL_NAME.'_time_morning_end_4">';
	$i = -1;
	while ($i < 24) {
		$html .= '<option value="'.$i.'"'.(($option['day-4'] == true && $option['time-morning-end-4']==$i) ? ' checked="checked"' : '').'>'.(($i==-1) ? '' : $i).'</option>';
		$i++;
	}
	$html .= '</select>';
	$html .= '<strong>midi</strong> début: <select name="'.PLUGINCLICKTOCALL_NAME.'_time_afternoon_start_4" id="'.PLUGINCLICKTOCALL_NAME.'_time_afternoon_start_4">';
	$i = -1;
	while ($i < 24) {
		$html .= '<option value="'.$i.'"'.(($option['day-4'] == true && $option['time-afternoon-start-4']==$i) ? ' selected="selected"' : '').'>'.(($i==-1) ? '' : $i).'</option>';
		$i++;
	}
	$html .= '</select>';
	$html .= ' fin: <select name="'.PLUGINCLICKTOCALL_NAME.'_time_afternoon_end_4" id="'.PLUGINCLICKTOCALL_NAME.'_time_afternoon_end_4">';
	$i = -1;
	while ($i < 24) {
		$html .= '<option value="'.$i.'"'.(($option['day-4'] == true && $option['time-afternoon-end-4']==$i) ? ' selected="selected"' : '').'>'.(($i==-1) ? '' : $i).'</option>';
		$i++;
	}
	$html .= '</select>';
	$html .= '</p>';
	$html .= '<p><input type="checkbox" name="'.PLUGINCLICKTOCALL_NAME.'_day_5" id="'.PLUGINCLICKTOCALL_NAME.'_day_5" '.(($option['day-5'] == true) ? 'checked="checked"' : '').'/><label for="'.PLUGINCLICKTOCALL_NAME.'_day_5">Vendredi</label>';
	$html .= '<strong>matin</strong>: début: <select name="'.PLUGINCLICKTOCALL_NAME.'_time_morning_start_5" id="'.PLUGINCLICKTOCALL_NAME.'_time_morning_start_5">';
	$i = -1;
	while ($i < 24) {
		$html .= '<option value="'.$i.'"'.(($option['day-5'] == true && $option['time-morning-start-5']==$i) ? ' selected="selected"' : '').'>'.(($i==-1) ? '' : $i).'</option>';
		$i++;
	}
	$html .= '</select>';
	$html .= ' fin: <select name="'.PLUGINCLICKTOCALL_NAME.'_time_morning_end_5" id="'.PLUGINCLICKTOCALL_NAME.'_time_morning_end_5">';
	$i = -1;
	while ($i < 24) {
		$html .= '<option value="'.$i.'"'.(($option['day-5'] == true && $option['time-morning-end-5']==$i) ? ' selected="selected"' : '').'>'.(($i==-1) ? '' : $i).'</option>';
		$i++;
	}
	$html .= '</select>';
	$html .= '<strong>midi</strong> début: <select name="'.PLUGINCLICKTOCALL_NAME.'_time_afternoon_start_5" id="'.PLUGINCLICKTOCALL_NAME.'_time_afternoon_start_5">';
	$i = -1;
	while ($i < 24) {
		$html .= '<option value="'.$i.'"'.(($option['day-5'] == true && $option['time-afternoon-start-5']==$i) ? ' selected="selected"' : '').'>'.(($i==-1) ? '' : $i).'</option>';
		$i++;
	}
	$html .= '</select>';
	$html .= ' fin: <select name="'.PLUGINCLICKTOCALL_NAME.'_time_afternoon_end_5" id="'.PLUGINCLICKTOCALL_NAME.'_time_afternoon_end_5">';
	$i = -1;
	while ($i < 24) {
		$html .= '<option value="'.$i.'"'.(($option['day-5'] == true && $option['time-afternoon-end-5']==$i) ? ' selected="selected"' : '').'>'.(($i==-1) ? '' : $i).'</option>';
		$i++;
	}
	$html .= '</select>';
	$html .= '</p>';
	$html .= '<p><input type="checkbox" name="'.PLUGINCLICKTOCALL_NAME.'_day_6" id="'.PLUGINCLICKTOCALL_NAME.'_day_6" '.(($option['day-6'] == true) ? 'checked="checked"' : '').'/><label for="'.PLUGINCLICKTOCALL_NAME.'_day_6">Samedi</label>';
	$html .= '<strong>matin</strong>: début: <select name="'.PLUGINCLICKTOCALL_NAME.'_time_morning_start_6" id="'.PLUGINCLICKTOCALL_NAME.'_time_morning_start_6">';
	$i = -1;
	while ($i < 24) {
		$html .= '<option value="'.$i.'"'.(($option['day-6'] == true && $option['time-morning-start-6']==$i) ? ' selected="selected"' : '').'>'.(($i==-1) ? '' : $i).'</option>';
		$i++;
	}
	$html .= '</select>';
	$html .= ' fin: <select name="'.PLUGINCLICKTOCALL_NAME.'_time_morning_end_6" id="'.PLUGINCLICKTOCALL_NAME.'_time_morning_end_6">';
	$i = -1;
	while ($i < 24) {
		$html .= '<option value="'.$i.'"'.(($option['day-6'] == true && $option['time-morning-end-6']==$i) ? ' selected="selected"' : '').'>'.(($i==-1) ? '' : $i).'</option>';
		$i++;
	}
	$html .= '</select>';
	$html .= '<strong>midi</strong> début: <select name="'.PLUGINCLICKTOCALL_NAME.'_time_afternoon_start_6" id="'.PLUGINCLICKTOCALL_NAME.'_time_afternoon_start_6">';
	$i = -1;
	while ($i < 24) {
		$html .= '<option value="'.$i.'"'.(($option['day-6'] == true && $option['time-afternoon-start-6']==$i) ? ' selected="selected"' : '').'>'.(($i==-1) ? '' : $i).'</option>';
		$i++;
	}
	$html .= '</select>';
	$html .= ' fin: <select name="'.PLUGINCLICKTOCALL_NAME.'_time_afternoon_end_6" id="'.PLUGINCLICKTOCALL_NAME.'_time_afternoon_end_6">';
	$i = -1;
	while ($i < 24) {
		$html .= '<option value="'.$i.'"'.(($option['day-6'] == true && $option['time-afternoon-end-6']==$i) ? ' selected="selected"' : '').'>'.(($i==-1) ? '' : $i).'</option>';
		$i++;
	}
	$html .= '</select>';
	$html .= '</p>';
	$html .= '<p><input type="checkbox" name="'.PLUGINCLICKTOCALL_NAME.'_day_0" id="'.PLUGINCLICKTOCALL_NAME.'_day_0" '.(($option['day-0'] == true) ? 'checked="checked"' : '').'/><label for="'.PLUGINCLICKTOCALL_NAME.'_day_0">Dimanche</label>';
	$html .= '<strong>matin</strong>: début: <select name="'.PLUGINCLICKTOCALL_NAME.'_time_morning_start_0" id="'.PLUGINCLICKTOCALL_NAME.'_time_morning_start_0">';
	$i = -1;
	while ($i < 24) {
		$html .= '<option value="'.$i.'"'.(($option['day-0'] == true && $option['time-morning-start-0']==$i) ? ' selected="selected"' : '').'>'.(($i==-1) ? '' : $i).'</option>';
		$i++;
	}
	$html .= '</select>';
	$html .= ' fin: <select name="'.PLUGINCLICKTOCALL_NAME.'_time_morning_end_0" id="'.PLUGINCLICKTOCALL_NAME.'_time_morning_end_0">';
	$i = -1;
	while ($i < 24) {
		$html .= '<option value="'.$i.'"'.(($option['day-0'] == true && $option['time-morning-end-0']==$i) ? ' selected="selected"' : '').'>'.(($i==-1) ? '' : $i).'</option>';
		$i++;
	}
	$html .= '</select>';
	$html .= '<strong>midi</strong> début: <select name="'.PLUGINCLICKTOCALL_NAME.'_time_afternoon_start_0" id="'.PLUGINCLICKTOCALL_NAME.'_time_afternoon_start_0">';
	$i = -1;
	while ($i < 24) {
		$html .= '<option value="'.$i.'"'.(($option['day-0'] == true && $option['time-afternoon-start-0']==$i) ? ' selected="selected"' : '').'>'.(($i==-1) ? '' : $i).'</option>';
		$i++;
	}
	$html .= '</select>';
	$html .= ' fin: <select name="'.PLUGINCLICKTOCALL_NAME.'_time_afternoon_end_0" id="'.PLUGINCLICKTOCALL_NAME.'_time_afternoon_end_0">';
	$i = -1;
	while ($i < 24) {
		$html .= '<option value="'.$i.'"'.(($option['day-0'] == true && $option['time-afternoon-end-0']==$i) ? ' selected="selected"' : '').'>'.(($i==-1) ? '' : $i).'</option>';
		$i++;
	}
	$html .= '</select>';
	$html .= '</p>';
	$html .= '<p class="description">pour une journée complète, sans interruption, laissez: &acute;<strong>matin</strong>: fin&acute; et &acute;<strong>midi</strong> début&acute; à vide</p>';
	$html .= '<input type="hidden" name="'.PLUGINCLICKTOCALL_NAME.'_action"/>';
	$html .= '<p class="submit">';
	$html .= '<input class="button-primary" type="submit" value="enregistrer" onclick="forms[\''.PLUGINCLICKTOCALL_NAME.'_form\'].elements[\''.PLUGINCLICKTOCALL_NAME.'_action\'].value=\'save\';"/>';
	$html .= '<input class="button-primary" type="submit" value="supprimer" onclick="forms[\''.PLUGINCLICKTOCALL_NAME.'_form\'].elements[\''.PLUGINCLICKTOCALL_NAME.'_action\'].value=\'delete\';"/></p>';
	$html .= '<ul id="'.PLUGINCLICKTOCALL_NAME.'-service-configuration">';
	$html .= '<li id="'.PLUGINCLICKTOCALL_NAME.'-configuration-ovh">';
	$html .= '<h2>OVH Configuration</h2>';
	$html .= '<p><label>NIC</label><input type="text" class="small" name="'.PLUGINCLICKTOCALL_NAME.'_ovh_nic" '.((!empty($option['nic'])) ? 'value="'.$option['nic'].'"' : '').'/><br/>';
	$html .= '<span class="description"><strong>Attention</strong>: vous devez définir un utilisateur du service Téléphonie OVH, il ne s\'agit pas de votre NIC identifiant OVH Manager.</span></p>';
	$html .= '<p><label>Mot de passe</label><input type="password" name="'.PLUGINCLICKTOCALL_NAME.'_ovh_password" '.((!empty($option['password'])&&$option['service'] == 'ovh') ? 'value="'.$option['password'].'"' : '').'/></p>';
	$html .= '<p><label>Numéro destination</label><input type="text" class="small" name="'.PLUGINCLICKTOCALL_NAME.'_ovh_phonenumber_to" '.((!empty($option['phonenumber_to'])&&$option['service'] == 'ovh') ? 'value="'.$option['phonenumber_to'].'"' : '').'/></p>';
	$html .= '<h3>Tester</h3>';
	$html .= '<p><label>Numéro à appeler</label><input type="text" class="small" name="'.PLUGINCLICKTOCALL_NAME.'_ovh_phonenumber_from"/>';
	$html .= '<input class="button-secondary" type="submit" value="tester" onclick="forms[\''.PLUGINCLICKTOCALL_NAME.'_form\'].elements[\''.PLUGINCLICKTOCALL_NAME.'_action\'].value=\'testovh\';"/></p>';
	$html .= '</li>';
	$html .= '<li id="'.PLUGINCLICKTOCALL_NAME.'-configuration-orange">';
	$html .= '<h2>Orange Configuration</h2>';
	$html .= '<p><label>Clef API</label><input type="text" class="small" name="'.PLUGINCLICKTOCALL_NAME.'_orange_access_key" '.((!empty($option['access_key'])) ? 'value="'.$option['access_key'].'"' : '').'/><br/>';
	$html .= '<span class="description">vous devez créer un compte orange pour obtenir la clef d\'accès au service orange</span></p>';
	$html .= '<p><label>Numéro destination</label><input type="text" class="small" name="'.PLUGINCLICKTOCALL_NAME.'_orange_to" '.((!empty($option['phonenumber_to'])&&$option['service'] == 'orange') ? 'value="'.$option['phonenumber_to'].'"' : '').'/></p>';
	$html .= '<h3>Tester</h3>';
	$html .= '<p><label>Numéro à appeler</label><input type="text" class="small" name="'.PLUGINCLICKTOCALL_NAME.'_orange_from"/>';
	$html .= '<input class="button-secondary" type="submit" value="tester" onclick="forms[\''.PLUGINCLICKTOCALL_NAME.'_form\'].elements[\''.PLUGINCLICKTOCALL_NAME.'_action\'].value=\'testorange\';"/></p>';
	$html .= '</li>';
	$html .= '<li id="'.PLUGINCLICKTOCALL_NAME.'-configuration-ribbit">';
	$html .= '<h2>Ribbit Configuration</h2>';
	$html .= '<p><label>Email</label><input type="text" class="small" name="'.PLUGINCLICKTOCALL_NAME.'_ribbit_email" '.((!empty($option['email'])) ? 'value="'.$option['email'].'"' : '').'/><br/>';
	$html .= '<span class="description"><strong>Attention</strong>: vous devez définir onsumer_key et secret_key dans le fichier de configuration situé classes/ribbit/ribbit_config.yml.</span></p>';
	$html .= '<p><label>Mot de passe</label><input type="password" name="'.PLUGINCLICKTOCALL_NAME.'_ribbit_password" '.((!empty($option['password'])&&$option['service'] == 'ribbit') ? 'value="'.$option['password'].'"' : '').'/></p>';
	$html .= '<p><label>Numéro destination</label><input type="text" class="small" name="'.PLUGINCLICKTOCALL_NAME.'_ribbit_telephone_one" '.((!empty($option['phonenumber_to'])&&$option['service'] == 'ribbit') ? 'value="'.$option['phonenumber_to'].'"' : '').'/></p>';
	$html .= '<h3>Tester</h3>';
	$html .= '<p><label>Numéro à appeler</label><input type="text" class="small" name="'.PLUGINCLICKTOCALL_NAME.'_ribbit_telephone_two"/>';
	$html .= '<input class="button-secondary" type="submit" value="tester" onclick="forms[\''.PLUGINCLICKTOCALL_NAME.'_form\'].elements[\''.PLUGINCLICKTOCALL_NAME.'_action\'].value=\'testribbit\';"/></p>';
	$html .= '</li>';
	$html .= '</ul>';
	$html .= '</form>';
	$html .= '</div>';
	$html .= '<script type="text/javascript">';
	$html .= '(function($) {';
	$html .= 'var services = $(\'input[name="'.PLUGINCLICKTOCALL_NAME.'_service"]\');';
	$html .= 'var configurations = $(\'#'.PLUGINCLICKTOCALL_NAME.'-service-configuration\').find(\'li\');';
	$html .= '$(services).change(function() {';
	$html .= '$(configurations).fadeOut(400);';
	$html .= '$(\'#'.PLUGINCLICKTOCALL_NAME.'-configuration-\'+$(this).val()).fadeIn(400);';
	$html .= '});';
	$html .= '$(configurations).fadeOut(400);';
	$html .= 'if ($(\'input[name="'.PLUGINCLICKTOCALL_NAME.'_service"]:checked\'))';
	$html .= '	$(\'#'.PLUGINCLICKTOCALL_NAME.'-configuration-\'+$(\'input[name="'.PLUGINCLICKTOCALL_NAME.'_service"]:checked\').val()).fadeIn(400);';
	if (!isset($option) || empty($option)) {
		$html .= '$(\'input[type="checkbox"]\').each(function(i) {';
		$html .= '	if (i > 0 && i < 6) {';
		$html .= '		$(this).attr(\'checked\', true);';
		$html .= '		$(\'time-morning-start-\'+i+\' option[value="9"]\').attr(\'selected\', \'selected\');';
		$html .= '		$(\'time-morning-end-\'+i+\' option[value="12"]\').attr(\'selected\', \'selected\');';
		$html .= '			$(\'time-afternoon-start-\'+i+\' option[value="14"]\').attr(\'selected\', \'selected\');';
		$html .= '		if (i == 5) {';
		$html .= '			$(\'time-afternoon-end-\'+i+\' option[value="17"]\').attr(\'selected\', \'selected\');';
		$html .= '		} else {';
		$html .= '			$(\'time-afternoon-end-\'+i+\' option[value="18"]\').attr(\'selected\', \'selected\');';	
		$html .= '		}';
		$html .= '	}';
		$html .= '});';
	}
	$html .= '})(jQuery);';
	$html .= '</script>';

	echo $html;
}
function wp_clicktocall_shortcode($attributes = null, $content = '') {
	$option = get_option(PLUGINCLICKTOCALL_NAME);
	$html = '';
	if (!empty($option)) {
		$action = $_POST[PLUGINCLICKTOCALL_NAME.'_action'];
		$phonenumber_from = $_POST[PLUGINCLICKTOCALL_NAME.'_phonenumber_from'];
		if ((!empty($action) && $action == 'call') 
		&& (!empty($phonenumber_from) && ClickToCall_Service::check_phonenumber($phonenumber_from))) {
			switch ($option['service']) {
				case 'ovh':
					$params = array_merge($option, array('phonenumber_from'=>$phonenumber_from));
					try {
						$result = ClickToCall_Service::ovh($params);
						$html .= '<div id="'.PLUGINCLICKTOCALL_NAME.'-shortcode-success" class="'.PLUGINCLICKTOCALL_NAME.'-success">Succès: Appel OK!</div>';
						$html .= '<script type="text/javascript">';
						$html .= 'jQuery(document).ready(function($) {';
						$html .= 'var tm = window.setInterval(function() {';
						$html .= '$(\'#'.PLUGINCLICKTOCALL_NAME.'-shortcode-success\').fadeOut(800);';
						$html .= 'tm = null;';
						$html .= '},10000);';
						$html .= '});';
						$html .= '</script>';
					} catch (Exception $e) {
						$html .= '<div id="'.PLUGINCLICKTOCALL_NAME.'-shortcode-error" class="'.PLUGINCLICKTOCALL_NAME.'-error">Erreur: Echec appel!</div>';
						$html .= '<script type="text/javascript">';
						$html .= 'jQuery(document).ready(function($) {';
						$html .= 'var tm = window.setInterval(function() {';
						$html .= '$(\'#'.PLUGINCLICKTOCALL_NAME.'-shortcode-error\').fadeOut(800);';
						$html .= 'tm = null;';
						$html .= '},10000);';
						$html .= '});';
						$html .= '</script>';
					}
					break;
				case 'orange':
					$params = array_merge($option, array('from'=>$phonenumber_from));
					try {
						$result = ClickToCall_Service::orange($params);
						$html .= '<div id="'.PLUGINCLICKTOCALL_NAME.'-shortcode-success" class="'.PLUGINCLICKTOCALL_NAME.'-success">Succès: Appel OK!</div>';
						$html .= '<script type="text/javascript">';
						$html .= 'jQuery(document).ready(function($) {';
						$html .= 'var tm = window.setInterval(function() {';
						$html .= '$(\'#'.PLUGINCLICKTOCALL_NAME.'-shortcode-success\').fadeOut(800);';
						$html .= 'tm = null;';
						$html .= '},10000);';
						$html .= '});';
						$html .= '</script>';
					} catch (Exception $e) {
						$html .= '<div id="'.PLUGINCLICKTOCALL_NAME.'-shortcode-error" class="'.PLUGINCLICKTOCALL_NAME.'-error">Erreur: Echec appel!</div>';
						$html .= '<script type="text/javascript">';
						$html .= 'jQuery(document).ready(function($) {';
						$html .= 'var tm = window.setInterval(function() {';
						$html .= '$(\'#'.PLUGINCLICKTOCALL_NAME.'-shorcode-error\').fadeOut(800);';
						$html .= 'tm = null;';
						$html .= '},10000);';
						$html .= '});';
						$html .= '</script>';
					}
					break;
			}
		}
		$html .= '<div id="'.PLUGINCLICKTOCALL_NAME.'-shortcode">';
		if (ClickToCall_Service::check_openning($option)) {
			$html .= '<form name="'.PLUGINCLICKTOCALL_NAME.'_form" method="post" action="' . str_replace('%7E', '~', $_SERVER['REQUEST_URI']) . '">';
			$html .= '<input type="text" name="'.PLUGINCLICKTOCALL_NAME.'_phonenumber_from">';
			$html .= '<input type="submit" name="'.PLUGINCLICKTOCALL_NAME.'_call" value="Appeler" id="'.PLUGINCLICKTOCALL_NAME.'-call">';
			$html .= '<input type="hidden" name="'.PLUGINCLICKTOCALL_NAME.'_action" value="call"/>';
		    $html .= '</form>';
		} else {
			$html .= 'Service fermé.';
		}
		$html .= '</div>';
	    $content .= $html;
	}
	return $content;
}
function wp_clicktocall_insert_to() {
	$option = get_option(PLUGINCLICKTOCALL_NAME);
	$html = '';
	if (!empty($option)) {
		$action = $_POST[PLUGINCLICKTOCALL_NAME.'_action'];
		$phonenumber_from = $_POST[PLUGINCLICKTOCALL_NAME.'_phonenumber_from'];
		if ((!empty($action) && $action == 'call') 
		&& (!empty($phonenumber_from) && CallService::check_phonenumber($phonenumber_from))) {
			switch ($option['service']) {
				case 'ovh':
					$params = array_merge($option, array('phonenumber_from'=>$phonenumber_from));
					try {
						$result = ClickToCall_Service::ovh($params);
						$html .= '<div id="'.PLUGINCLICKTOCALL_NAME.'-insert-to-success" class="'.PLUGINCLICKTOCALL_NAME.'-success">Succès: Appel OK!</div>';
						$html .= '<script type="text/javascript">';
						$html .= 'jQuery(document).ready(function($) {';
						$html .= 'var tm = window.setInterval(function() {';
						$html .= '$(\'#'.PLUGINCLICKTOCALL_NAME.'-insert-to-success\').fadeOut(800);';
						$html .= 'tm = null;';
						$html .= '},10000);';
						$html .= '});';
						$html .= '</script>';
					} catch (Exception $e) {
						$html .= '<div id="'.PLUGINCLICKTOCALL_NAME.'-insert-to-error" class="'.PLUGINCLICKTOCALL_NAME.'-error">Erreur: Echec appel!</div>';
						$html .= '<script type="text/javascript">';
						$html .= 'jQuery(document).ready(function($) {';
						$html .= 'var tm = window.setInterval(function() {';
						$html .= '$(\'#'.PLUGINCLICKTOCALL_NAME.'-insert-to-error\').fadeOut(800);';
						$html .= 'tm = null;';
						$html .= '},10000);';
						$html .= '});';
						$html .= '</script>';
					}
					break;
				case 'orange':
					$params = array_merge($option, array('from'=>$phonenumber_from));
					try {
						$result = ClickToCall_Service::orange($params);
						$html .= '<div id="'.PLUGINCLICKTOCALL_NAME.'-insert-to-success" class="'.PLUGINCLICKTOCALL_NAME.'-success">Succès: Appel OK!</div>';
						$html .= '<script type="text/javascript">';
						$html .= 'jQuery(document).ready(function($) {';
						$html .= 'var tm = window.setInterval(function() {';
						$html .= '$(\'#'.PLUGINCLICKTOCALL_NAME.'-insert-to-success\').fadeOut(800);';
						$html .= 'tm = null;';
						$html .= '},10000);';
						$html .= '});';
						$html .= '</script>';
					} catch (Exception $e) {
						$html .= '<div id="'.PLUGINCLICKTOCALL_NAME.'-insert-to-error" class="'.PLUGINCLICKTOCALL_NAME.'-error">Erreur: Echec appel!</div>';
						$html .= '<script type="text/javascript">';
						$html .= 'jQuery(document).ready(function($) {';
						$html .= 'var tm = window.setInterval(function() {';
						$html .= '$(\'#'.PLUGINCLICKTOCALL_NAME.'-insert-to-error\').fadeOut(800);';
						$html .= 'tm = null;';
						$html .= '},10000);';
						$html .= '});';
						$html .= '</script>';
					}
					break;
			}
		}

		$html .= '<div id="'.PLUGINCLICKTOCALL_NAME.'-insert-to">';
		if (ClickToCall_Service::check_openning($option)) {
			$html .= '<form name="'.PLUGINCLICKTOCALL_NAME.'_form" method="post" action="' . str_replace('%7E', '~', $_SERVER['REQUEST_URI']) . '">';
			$html .= '<input type="text" name="'.PLUGINCLICKTOCALL_NAME.'_phonenumber_from">';
			$html .= '<input type="submit" name="'.PLUGINCLICKTOCALL_NAME.'_call" value="Appeler">';
			$html .= '<input type="hidden" name="'.PLUGINCLICKTOCALL_NAME.'_action" value="call"/>';
		    $html .= '</form>';
		} else {
			$html .= 'Service fermé.';
		}
	    $html .= '</div>';
	    
	    echo $html;
	}
}
?>
