<?php
/**
 * class-groups-gravity-forms.php
 * 
 * Copyright (c) 2014 "kento" Karim Rahimpur www.itthinx.com
 * 
 * This code is provided subject to the license granted.
 * Unauthorized use and distribution is prohibited.
 * See COPYRIGHT.txt and LICENSE.txt
 * 
 * This code is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * 
 * This header and all notices must be kept intact.
 * 
 * @author Karim Rahimpur
 * @package groups-gravityforms
 * @since 1.0.0
 */

/**
 * Plugin main class.
 */
class Groups_Gravity_Forms {

	const PLUGIN_OPTIONS    = 'groups_gravityforms';
	const NONCE             = 'groups_gf_nonce';
	const SET_ADMIN_OPTIONS = 'set_admin_options';

	private static $admin_messages = array();

	/**
	 * Activation handler.
	 */
	public static function activate() {
		$options = get_option( self::PLUGIN_OPTIONS , null );
		if ( $options === null ) {
			$options = array();
			// add the options and there's no need to autoload these
			add_option( self::PLUGIN_OPTIONS, $options, null, 'no' );
		}
	}

	/**
	 * Prints admin notices.
	 */
	public static function admin_notices() {
		if ( !empty( self::$admin_messages ) ) {
			foreach ( self::$admin_messages as $msg ) {
				echo $msg;
			}
		}
	}

	/**
	 * Initializes the integration if dependencies are verified.
	 */
	public static function init() {
		add_action( 'admin_notices', array( __CLASS__, 'admin_notices' ) );
		add_action( 'init', array( __CLASS__, 'wp_init' ) );
		include_once 'class-groups-gravity-forms-update.php';
		if ( self::check_dependencies() ) {
			register_activation_hook( __FILE__, array( __CLASS__, 'activate' ) );
			include_once 'class-groups-gravity-forms-handler.php';
			if ( is_admin() ) {
				include_once 'class-groups-gravity-forms-form-settings.php';
			}
		}
	}

	/**
	 * Loads translations.
	 */
	public static function wp_init() {
		load_plugin_textdomain( GROUPS_GF_PLUGIN_DOMAIN, null, 'groups-gravityforms/languages' );
	}

	/**
	 * Check dependencies and print notices if they are not met.
	 * @return true if ok, false if plugins are missing
	 */
	public static function check_dependencies() {

		$result = true;

		$active_plugins = get_option( 'active_plugins', array() );
		if ( is_multisite() ) {
			$active_sitewide_plugins = get_site_option( 'active_sitewide_plugins', array() );
			$active_sitewide_plugins = array_keys( $active_sitewide_plugins );
			$active_plugins = array_merge( $active_plugins, $active_sitewide_plugins );
		}

		// required plugins
		$groups_is_active = in_array( 'groups/groups.php', $active_plugins );
		if ( !$groups_is_active ) {
			self::$admin_messages[] =
				"<div class='error'>" .
				__( 'The <strong>Groups Gravity Forms Integration</strong> plugin requires <a href="http://www.itthinx.com/plugins/groups" target="_blank">Groups</a> to be installed and activated.', GROUPS_GF_PLUGIN_DOMAIN ) .
				"</div>";
		}

// 		$gf_is_active = in_array( 'gravityforms/gravityforms.php', $active_plugins );
// 		if ( !$gf_is_active ) {
// 			self::$admin_messages[] =
// 				"<div class='error'>" .
// 				__( 'The <strong>Groups Gravity Forms Integration</strong> plugin requires <a href="http://www.gravityforms.com" target="_blank">Gravity Forms</a>.', GROUPS_GF_PLUGIN_DOMAIN ) .
// 				"</div>";
// 		}
// 		if ( !$groups_is_active || !$gf_is_active ) {
// 			$result = false;
// 		}

		if ( !$groups_is_active ) {
			$result = false;
		}

		return $result;
	}
}
Groups_Gravity_Forms::init();
