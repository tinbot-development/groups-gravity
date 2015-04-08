<?php
/**
 * groups-gravityforms.php
 *
 * Copyright (c) 2014-2015 "kento" Karim Rahimpur www.itthinx.com
 * 
 * =============================================================================
 * 
 *                             LICENSE RESTRICTIONS
 * 
 *           This plugin is provided subject to the license granted.
 *              Unauthorized use and distribution is prohibited.
 *                     See COPYRIGHT.txt and LICENSE.txt.
 * 
 * Files licensed under the GNU General Public License state so explicitly in
 * their header or where implied. Other files are not licensed under the GPL
 * and the license obtained applies.
 * 
 * =============================================================================
 * 
 * You MUST be granted a license by the copyright holder for those parts that
 * are not provided under the GPLv3 license.
 * 
 * If you have not been granted a license DO NOT USE this plugin until you have
 * BEEN GRANTED A LICENSE.
 * 
 * Use of this plugin without a granted license constitutes an act of COPYRIGHT
 * INFRINGEMENT and LICENSE VIOLATION and may result in legal action taken
 * against the offending party.
 * 
 * Being granted a license is GOOD because you will get support and contribute
 * to the development of useful free and premium themes and plugins that you
 * will be able to enjoy.
 * 
 * Thank you!
 * 
 * Visit www.itthinx.com for more information.
 * 
 * =============================================================================
 *
 * This code is released under the GNU General Public License.
 *
 * This code is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This header and all notices must be kept intact.
 *
 * @author Karim Rahimpur
 * @package groups-gravityforms
 * @since 1.0.0
 *
 * Plugin Name: Groups Gravity Forms Integration
 * Plugin URI: http://www.itthinx.com/plugins/groups-gravityforms/
 * Description: Integrates <a href="http://www.itthinx.com/plugins/groups">Groups</a> with Gravity Forms.
 * Author: itthinx
 * Author URI: http://www.itthinx.com/
 * Version: 1.2.1
 * License: GPLv3
 */
define( 'GROUPS_GF_VERSION', '1.2.1' );
define( 'GROUPS_GF_PLUGIN_DOMAIN', 'groups-gravityforms' );
define( 'GROUPS_GF_FILE', __FILE__ );

define( 'GROUPS_GF_DEBUG', defined( 'WP_DEBUG' ) && defined( 'WP_DEBUG_LOG' ) && ( get_option( 'groups-gf-debug', false ) == 'yes' ) ? WP_DEBUG && WP_DEBUG_LOG : false );

include_once 'includes/class-groups-gravity-forms.php';
