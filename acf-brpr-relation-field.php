<?php

/*
Plugin Name: Advanced Custom Fields: brpr relation field
Plugin URI: PLUGIN_URL
Description: brpr relation field
Version: 1.0.0
Author: oleg.brezgalov
Author URI: AUTHOR_URL
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

// exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;


// check if class already exists
if( !class_exists('brpr_acf_plugin_relation_field') ) :

class brpr_acf_plugin_relation_field {
	
	// vars
	var $settings;
	
	
	/*
	*  __construct
	*
	*  This function will setup the class functionality
	*
	*  @type	function
	*  @date	17/02/2016
	*  @since	1.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function __construct() {
		
		// settings
		// - these will be passed into the field class.
		$this->settings = array(
			'version'	=> '1.0.0',
			'url'		=> plugin_dir_url(__FILE__),
			'path'		=> plugin_dir_path(__FILE__)
		);
		
		
		// set text domain
		// https://codex.wordpress.org/Function_Reference/load_plugin_textdomain
		load_plugin_textdomain(
			'brpr_relation_field', 
			false, 
			plugin_basename(dirname(__FILE__)) . '/lang' 
		); 
		
		
		// include field
		add_action(
			'acf/include_field_types', 	
			array($this, 'include_field_types')
		); // v5
		add_action(
			'acf/register_fields', 		
			array($this, 'include_field_types')
		); // v4	
	}
	
	
	/*
	*  include_field_types
	*
	*  This function will include the field type class
	*
	*  @type	function
	*  @date	17/02/2016
	*  @since	1.0.0
	*
	*  @param	$version (int) major ACF version. Defaults to false
	*  @return	n/a
	*/
	
	function include_field_types( $version = false ) {
		$version = 4;
		// support empty $version
		//if( !$version ) $version = 4;
		
		
		// include
		include_once(
			'fields/class-brpr-acf-field-relation-field-v' . $version . '.php'
		);
		
	}
	
}

wp_register_script(
	'admin_post_new', 
	plugin_dir_url('acf-brpr-relation-field/assets/js').'post_new.js',
	array()
);
function brpr_relation_wp_admin($hook) {
        if($hook != 'post-new.php') {
            return;
        }
        wp_enqueue_script('admin_post_new');
}
add_action('admin_enqueue_scripts', 'brpr_relation_wp_admin');

// initialize
new brpr_acf_plugin_relation_field();

// class_exists check
endif;
	
?>