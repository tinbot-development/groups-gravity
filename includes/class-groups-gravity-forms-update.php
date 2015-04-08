<?php
/**
 * class-groups-gravity-forms-update.php
 *
 * Copyright (c) "kento" Karim Rahimpur www.itthinx.com
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
 * Update manager.
 */
class Groups_Gravity_Forms_Update {
	
	const UPDATE_SERVICE_URL = 'http://service.itthinx.com/updates.php';
	
	private static $version = null;
	private static $file = null;
	private static $plugin = null;

	/**
	 * Update hooks.
	 */
	public static function init() {
		self::$file    = GROUPS_GF_FILE;
		self::$version = GROUPS_GF_VERSION;
		self::$plugin  = 'groups-gravityforms';
		add_filter( 'pre_set_site_transient_update_plugins', array( __CLASS__, 'pre_set_site_transient_update_plugins' ) );
		add_filter( 'plugins_api', array( __CLASS__, 'plugins_api' ), 10, 3 );
	}

	/**
	 * Adds the plugin info to the update_plugins transient if a new version is available.
	 * @param array $value update_plugins transient
	 * @return (possibly modified) update_plugins transient
	 */
	public static function pre_set_site_transient_update_plugins( $value ) {
		$info = self::get_info();
		if ( $info ) {
			if ( isset( $info->new_version ) && ( version_compare( self::$version , $info->new_version ) < 0 ) ) {
				$value->response[plugin_basename( self::$file )] = $info;
			}
		}
		return $value;
	}

	/**
	 * Returns plugin info when requested for this plugin, $result otherwise.
	 * @param object|boolean $result
	 * @param string $action
	 * @param array $args
	 * @return object|boolean plugin info for this plugin if requested, $result otherwise
	 */
	public static function plugins_api( $result, $action, $args ) {
		if ( $action == 'plugin_information' ) {
			if ( $args->slug === dirname( plugin_basename(  self::$file ) ) ) {
				$result = false;
				$info = self::get_info();
				if ( $info ) {
					$result = $info;
				}
			}
		}
		return $result; 
	}

	/**
	 * Retrieves plugin information from update server.
	 * @return object plugin information when successfully retrieved, null otherwise
	 */
	public static function get_info() {
		$result = null;
		$request = wp_remote_post(
			self::UPDATE_SERVICE_URL,
			array(
				'body' => array(
					'action' => 'info',
					'plugin' => self::$plugin
				)
			)
		);
		if ( !is_wp_error( $request ) || wp_remote_retrieve_response_code( $request ) === 200) {
			$result = unserialize( $request['body'] );
		}
		return $result;
	}

}
Groups_Gravity_Forms_Update::init();
