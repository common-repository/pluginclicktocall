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

/**
 * Classe ClickToCall_Widget.
 *
 * Implementation class telephony services
 *
 * @package Plugins
 * @subpackage ClickToCall
 * @version 2.0.0
 * @author Dimitri GOY
 * @copyright 2011 - DimGoTo
 * @link http://www.dimgoto.com/
 */
class ClickToCall_Widget extends WP_Widget {
	function ClickToCall_Widget() {
		$widget_options = array(
			'classname'		=> 'clicktocall-widget', 
			'description'	=> 'Widget Click To Call. Faites vous appeler à partir de votre site Web d\'un simple Click.');
		$control_options = array(
			'title'	=> 'ClickToCall');

        $this->WP_Widget('widgetclicktocall', 'Click To Call Widget', $widget_options, $control_options);	
    }
	function widget($args, $instance) {
		extract($args);
		$option = get_option(PLUGINCLICKTOCALL_NAME);
		$html = '';
		$html .= $args['before_widget'];
		$title = apply_filters('widget_title', $instance['title']);
		if (isset($title) && !empty($title))
	    	$html .= $args['before_title'].$title.$args['after_title'];
	    
		if (!empty($option) && ClickToCall_Service::check_openning($option)) {
			$action = $_POST[PLUGINCLICKTOCALL_NAME.'_action'];
			$phonenumber_from = $_POST[PLUGINCLICKTOCALL_NAME.'_phonenumber_from'];
			
			
	    		
			if ((!empty($action) && $action == 'call') 
			&& (!empty($phonenumber_from) && ClickToCall_Service::check_phonenumber($phonenumber_from))) {
				switch ($option['service']) {
					case 'ovh':
						$params = array_merge($option, array('phonenumber_from'=>$phonenumber_from));
						try {
							$result = ClickToCall_Service::ovh($params);
							$html .= '<div id="'.PLUGINCLICKTOCALL_NAME.'-widget-success" class="'.PLUGINCLICKTOCALL_NAME.'-success">Succès: Appel OK!</div>';
							$html .= '<script type="text/javascript">';
							$html .= 'jQuery(document).ready(function($) {';
							$html .= 'var tm = window.setInterval(function() {';
							$html .= '$(\'#'.PLUGINCLICKTOCALL_NAME.'-widget-success\').fadeOut(800);';
							$html .= 'tm = null;';
							$html .= '},10000);';
							$html .= '});';
							$html .= '</script>';
						} catch (Exception $e) {
							$html .= '<div id="'.PLUGINCLICKTOCALL_NAME.'-widget-error" class="'.PLUGINCLICKTOCALL_NAME.'-error">Erreur: Echec appel!</div>';
							$html .= '<script type="text/javascript">';
							$html .= 'jQuery(document).ready(function($) {';
							$html .= 'var tm = window.setInterval(function() {';
							$html .= '$(\'#'.PLUGINCLICKTOCALL_NAME.'-widget-error\').fadeOut(800);';
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
							$html .= '<div id="'.PLUGINCLICKTOCALL_NAME.'-widget-success" class="'.PLUGINCLICKTOCALL_NAME.'-success">Succès: Appel OK!</div>';
							$html .= '<script type="text/javascript">';
							$html .= 'jQuery(document).ready(function($) {';
							$html .= 'var tm = window.setInterval(function() {';
							$html .= '$(\'#'.PLUGINCLICKTOCALL_NAME.'-widget-success\').fadeOut(800);';
							$html .= 'tm = null;';
							$html .= '},10000);';
							$html .= '});';
							$html .= '</script>';
						} catch (Exception $e) {
							$html .= '<div id="'.PLUGINCLICKTOCALL_NAME.'-widget-error" class="'.PLUGINCLICKTOCALL_NAME.'-error">Erreur: Echec appel!</div>';
							$html .= '<script type="text/javascript">';
							$html .= 'jQuery(document).ready(function($) {';
							$html .= 'var tm = window.setInterval(function() {';
							$html .= '$(\'#'.PLUGINCLICKTOCALL_NAME.'-widget-error\').fadeOut(800);';
							$html .= 'tm = null;';
							$html .= '},10000);';
							$html .= '});';
							$html .= '</script>';
						}
						break;
				}
			}
			

			
			$html .= '<div id="'.PLUGINCLICKTOCALL_NAME.'-widget">';
			$html .= '<form name="'.PLUGINCLICKTOCALL_NAME.'_form" method="post" action="">';
			$html .= '<input type="text" name="'.PLUGINCLICKTOCALL_NAME.'_phonenumber_from">';
			$html .= '<input type="submit" name="'.PLUGINCLICKTOCALL_NAME.'_call" id="'.PLUGINCLICKTOCALL_NAME.'-call" value="Appeler">';
			$html .= '<input type="hidden" name="'.PLUGINCLICKTOCALL_NAME.'_action" value="call"/>';
		    $html .= '</form>';
		    $html .= '</div>';
			
	    	
		} elseif (!ClickToCall_Service::check_openning($option)) {
			$html .= '<p>Service fermé.</p>';
		}
		$html .= $args['after_widget'];
		
	    echo $html;
	}
    function update($new_instance, $old_instance) {				
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
        return $instance;
    }
    function form($instance) {				
        $title = esc_attr($instance['title']);
        $html = '';
        $html .= '<p>';
        $html .= '<label for="'.PLUGINCLICKTOCALL_NAME.'-widget-title">Titre:</label>';
        $html .= '<input class="widefat" id="'.$this->get_field_name('title').'" name="'.$this->get_field_name('title').'" type="text" value="'.$title.'" />';
        $html .= '</p>';
        
        echo $html;
    }
}
function clicktocall_init() {
	return register_widget('ClickToCall_Widget');
}
add_action('widgets_init','clicktocall_init');
?>
