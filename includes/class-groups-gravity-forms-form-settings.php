<?php
/**
 * class-groups-gravity-forms-form-settings.php
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
 * Groups form settings.
 */
class Groups_Gravity_Forms_Form_Settings {

	const NONCE = 'groups-form-settings-nonce';
	const SAVE  = 'groups-form-settings-save';

	/**
	 * Initialization action on WordPress init.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'wp_init' ) );
	}

	/**
	 * Adds the Groups settings section.
	 */
	public static function wp_init() {
		if ( current_user_can( GROUPS_ADMINISTER_OPTIONS ) ) {
			// plugin settings
			add_filter( 'gform_addon_navigation', array( __CLASS__, 'gform_addon_navigation' ) );
			// form settings
			add_filter( 'gform_form_settings_menu', array( __CLASS__, 'gform_form_settings_menu' ) );
			add_action( 'gform_form_settings_page_groups', array( __CLASS__, 'gform_form_settings_page_groups' ) );
			add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_enqueue_scripts' ) );
		}
	}

	/**
	 * Enqueues the selectize script on the Groups form settings admin screen.
	 */
	public static function admin_enqueue_scripts() {

		// It would be nice if we could use
		// $page = GFForms::get_page();
		// but for our subview $page is false and it doesn't
		// allow to ask for the subview.
// 		if ( class_exists( 'GFForms' ) ) {
// 			$page = GFForms::get_page();
// 			switch( $page ) {
// 				case 'form_settings' :
// 					Groups_UIE::enqueue( 'select' );
// 					break;
// 			}
// 		}

		if ( function_exists( 'rgget' ) ) {
			if (
				rgget('page') == 'gf_edit_forms' &&
				rgget('view') == 'settings' &&
				rgget('subview') == 'groups'
			) {
				Groups_UIE::enqueue( 'select' );
			}
		}
	}

	/**
	 * Adds the Forms > Groups menu item.
	 * @param array $menus
	 * @return array
	 */
	public static function gform_addon_navigation( $menus ) {
		if ( current_user_can( GROUPS_ADMINISTER_OPTIONS ) ) {
			$menus[] = array(
				'name'       => 'gf_groups',
				'label'      => __( 'Groups', GROUPS_GF_PLUGIN_DOMAIN ),
				'callback'   =>  array( __CLASS__, 'groups_settings' ),
				'permission' => GROUPS_ADMINISTER_OPTIONS
			);
		}
		return $menus;
	}

	/**
	 * Adds the Groups section to the Form Settings.
	 * 
	 * @param array $menu_items
	 * @return array of menu items
	 */
	public static function gform_form_settings_menu( $menu_items ) {
		$menu_items[] = array(
			'name' => 'groups',
			'label' => __( 'Groups', GROUPS_GF_PLUGIN_DOMAIN )
		);
		return $menu_items;
	}

	/**
	 * General settings under Forms > Groups.
	 */
	public static function groups_settings() {

		$output = '';

		if ( isset( $_POST['groups-save'] ) ) {
			if ( wp_verify_nonce( $_POST[self::NONCE], self::SAVE ) ) {

				delete_option( 'groups-gf-debug' );
				if ( !empty( $_POST['groups-debug'] ) ) {
					add_option( 'groups-gf-debug', 'yes', '', 'no' );
				}

			}
		}

		$debug = get_option( 'groups-gf-debug', false ) == 'yes';

		$output .= '<div class="groups-gf-settings">';

		$output .= '<h2>';
		$output .= __( 'Groups', GROUPS_GF_PLUGIN_DOMAIN );
		$output .= '</h2>';

		$output .= '<form method="post">';
		$output .= '<div>';

		$output .= '<h3>';
		$output .= __( 'Debugging', GROUPS_GF_PLUGIN_DOMAIN );
		$output .= '</h3>';

		$output .= '<p>';
		$output .= __( 'Enable debugging for forms?', GROUPS_GF_PLUGIN_DOMAIN );
		$output .= '</p>';
		$output .= '<label>';
		$output .= sprintf( '<input type="checkbox" name="groups-debug" %s />', $debug ? ' checked="checked" ' : '' );
		$output .= ' ';
		$output .= __( 'Enabled', GROUPS_GF_PLUGIN_DOMAIN );
		$output .= '</label>';
		$error_log = ini_get( 'error_log' );
		$output .= '<p>';
		$output .= sprintf( __( 'If enabled, form submission details and action parameters are logged to <code>%s</code>.', GROUPS_GF_PLUGIN_DOMAIN ), esc_html( $error_log ) );
		$output .= '</p>';

		$output .= '<p>';
		$output .= __( 'WordPress debugging and logging must also be enabled for this to take effect.', GROUPS_GF_PLUGIN_DOMAIN );
		$output .= '</p>';

		$output .= '<p>';
		$output .= sprintf( __( 'Normally, this is done using the following definitions in <code>%s</code>:', GROUPS_GF_PLUGIN_DOMAIN ), esc_html( ABSPATH . 'wp-config.php' ) );
		$output .= '</p>';

		$output .= '<p>';
		$output .= __( '<code>define( "WP_DEBUG", true );</code></p><p><code>define( "WP_DEBUG_LOG", true );</code>', GROUPS_GF_PLUGIN_DOMAIN );
		$output .= '</p>';

		$output .= '<p>';
		$output .= __( '<strong>WARNING</strong> : On production sites, form debugging should <strong>not</strong> be enabled, otherwise sensitive data may be exposed. Make sure to clear the log file in any case.', GROUPS_GF_PLUGIN_DOMAIN );
		$output .= '</p>';

		$output .= wp_nonce_field( self::SAVE, self::NONCE, true, false );

		$output .= sprintf( '<input class="button-primary gfbutton" type="submit" name="groups-save" value="%s" />', __( 'Save', GROUPS_GF_PLUGIN_DOMAIN ) );

		$output .= '</div>';
		$output .= '</form>';

		$output .= '</div>'; // .groups-gf-settings

		echo $output;
	}

	/**
	 * Returns eligible input fields.
	 * 
	 * @param array $form
	 * @return array
	 */
	public static function get_form_fields( $form ){
		$fields = array();
		if ( is_array( $form['fields'] ) ) {
			foreach( $form['fields'] as $field ) {
				if( !rgar( $field, 'displayOnly' ) ) {
					$fields[] =  array( $field['id'], GFCommon::get_label( $field ) );
				}
			}
		}
		return $fields;
	}

	/**
	 * Renders the Groups section.
	 */
	public static function gform_form_settings_page_groups() {

		global $wpdb;

		$output = '';

		$groups_table = _groups_get_tablename( 'group' );
		if ( $groups = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $groups_table WHERE name != %s ORDER BY name", Groups_Registered::REGISTERED_GROUP_NAME ) ) ) {
			$output .= '<style type="text/css">';
			$output .= '.groups .selectize-input { font-size: inherit; }';
			$output .= '</style>';
		}

		$form_id = isset( $_GET['id'] ) ? $_GET['id'] : null;
		if ( $form = RGFormsModel::get_form_meta( $form_id ) ) {

			GFFormSettings::page_header();

			if ( isset( $_POST['groups-save'] ) ) {
				if ( wp_verify_nonce( $_POST[self::NONCE], self::SAVE ) ) {

					$form['groups']['on_submission']['enabled']   = !empty( $_POST['groups-on-submission'] );
					$form['groups']['on_registration']['enabled'] = !empty( $_POST['groups-on-registration'] );
					$form['groups']['on_payment']['enabled']      = !empty( $_POST['groups-on-payment'] );

					$form['groups']['on_submission']['add'] = array();
					if ( !empty( $_POST['groups-on-submission-add'] ) && is_array( $_POST['groups-on-submission-add'] ) ) {
						foreach( $_POST['groups-on-submission-add'] as $group_id ) {
							if ( $group = Groups_Group::read( $group_id ) ) {
								$form['groups']['on_submission']['add'][] = $group->group_id;
							}
						}
					}

					$form['groups']['on_submission']['field'] = !empty( $_POST['groups-on-submission-field'] ) ? $_POST['groups-on-submission-field'] : null;

					$form['groups']['on_registration']['add'] = array();
					if ( !empty( $_POST['groups-on-registration-add'] ) && is_array( $_POST['groups-on-registration-add'] ) ) {
						foreach( $_POST['groups-on-registration-add'] as $group_id ) {
							if ( $group = Groups_Group::read( $group_id ) ) {
								$form['groups']['on_registration']['add'][] = $group->group_id;
							}
						}
					}

					$form['groups']['on_payment']['add'] = array();
					if ( !empty( $_POST['groups-on-payment-add'] ) && is_array( $_POST['groups-on-payment-add'] ) ) {
						foreach( $_POST['groups-on-payment-add'] as $group_id ) {
							if ( $group = Groups_Group::read( $group_id ) ) {
								$form['groups']['on_payment']['add'][] = $group->group_id;
							}
						}
					}

					if ( RGFormsModel::update_form_meta( $form_id, $form ) ) {
						$output .= '<div class="updated below-h2"><p><strong>' . __( 'The settings have been saved.', GROUPS_GF_PLUGIN_DOMAIN ) . '</strong></p></div>';
					}

				}
			}

			$on_submission   = isset( $form['groups']['on_submission'] ) ? $form['groups']['on_submission']['enabled'] : false;
			$on_registration = isset( $form['groups']['on_registration']['enabled'] ) ? $form['groups']['on_registration']['enabled'] : false;
			$on_payment      = isset( $form['groups']['on_payment']['enabled'] ) ? $form['groups']['on_payment']['enabled'] : false;

			$on_submission_add   = isset( $form['groups']['on_submission']['add'] ) ? $form['groups']['on_submission']['add'] : array();
			$on_submission_field = isset( $form['groups']['on_submission']['field'] ) ? $form['groups']['on_submission']['field'] : null;
			$on_registration_add = isset( $form['groups']['on_registration']['add'] ) ? $form['groups']['on_registration']['add'] : array();
			$on_payment_add      = isset( $form['groups']['on_payment']['add'] ) ? $form['groups']['on_payment']['add'] : array();

			$output .= '<div class="groups-form-settings">';

			$output .= '<form method="post">';
			$output .= '<div>';

			$output .= '<h3>';
			$output .= __( 'Group Memberships for Form Submissions', GROUPS_GF_PLUGIN_DOMAIN );
			$output .= '</h3>';

			$output .= '<p>';
			$output .= __( 'Add users who submit this form to groups?', GROUPS_GF_PLUGIN_DOMAIN );
			$output .= '</p>';
			$output .= '<label>';
			$output .= sprintf( '<input type="checkbox" name="groups-on-submission" %s />', $on_submission ? ' checked="checked" ' : '' );
			$output .= ' ';
			$output .= __( 'Enabled', GROUPS_GF_PLUGIN_DOMAIN );
			$output .= '</label>';
			$output .= '<p>';
			$output .= __( 'If enabled, registered users who are logged in and submit this form will be added to the chosen groups.', GROUPS_GF_PLUGIN_DOMAIN );
			$output .= ' ';
			$output .= '<a style="text-decoration:none" href="#groups-note-1">*</a>';
			$output .= '</p>';

			$output .= '<p>';
			$output .= __( 'If new users are allowed to register through this form, the new user is also added to the groups that are chosen here.', GROUPS_GF_PLUGIN_DOMAIN );
			$output .= ' ';
			$output .= __( 'Otherwise, this has no effect for visitors who are not logged in and submit the form.', GROUPS_GF_PLUGIN_DOMAIN );
			$output .= '</p>';

			$output .= sprintf(
				'<select id="groups-on-submission-add" class="groups" name="groups-on-submission-add[]" multiple="multiple" placeholder="%s" data-placeholder="%s">',
				esc_attr( __( 'Choose groups &hellip;', GROUPS_GF_PLUGIN_DOMAIN ) ) ,
				esc_attr( __( 'Choose groups &hellip;', GROUPS_GF_PLUGIN_DOMAIN ) )
			);
			foreach( $groups as $group ) {
				$selected = in_array( Groups_Utility::id( $group->group_id ), $on_submission_add );
				$output .= sprintf( '<option value="%d" %s>%s</option>', Groups_Utility::id( $group->group_id ), $selected ? ' selected="selected" ' : '', wp_filter_nohtml_kses( $group->name ) );
			}
			$output .= '</select>';
			$output .= Groups_UIE::render_select( '#groups-on-submission-add' );

			$output .= '<p>';
			$output .= __( 'Add users who submit this form to groups based on a form field?', GROUPS_GF_PLUGIN_DOMAIN );
			$output .= '</p>';

			$form_fields = self::get_form_fields( $form );
			$output .= '<select name="groups-on-submission-field">';
			$output .= '<option value=""></option>';
			foreach ( $form_fields as $form_field ) {
				$field_id = $form_field[0];
				$field_label = esc_html( GFCommon::truncate_middle( $form_field[1], 40 ) );
				$selected = $field_id == $on_submission_field ? ' selected="selected" ' : '';
				$output .= sprintf( '<option value="%s" %s>', esc_attr( $field_id ), $selected );
				$output .= $field_label;
				$output .= '</option>';
			}
			$output .= '</select>';

			$output .= '<p>';
			$output .= __( 'If enabled and a field is selected, the submitted field values will be interpreted as group names and the user will be added to those groups if they exist.', GROUPS_GF_PLUGIN_DOMAIN );
			$output .= '</p>';

			$output .= '<br/>';

			$output .= '<h3>';
			$output .= __( 'Group Memberships for User Registrations', GROUPS_GF_PLUGIN_DOMAIN );
			$output .= '</h3>';

			$output .= '<p>';
			$output .= __( 'Add new users who register through this form to groups?', GROUPS_GF_PLUGIN_DOMAIN );
			$output .= '</p>';
			$output .= '<label>';
			$output .= sprintf( '<input type="checkbox" name="groups-on-registration" %s />', $on_registration ? ' checked="checked" ' : '' );
			$output .= ' ';
			$output .= __( 'Enabled', GROUPS_GF_PLUGIN_DOMAIN );
			$output .= '</label>';
			$output .= '<p>';
			$output .= __( 'If enabled, new users who register through this form will be added to the chosen groups.', GROUPS_GF_PLUGIN_DOMAIN );
			$output .= ' ';
			$output .= '<a style="text-decoration:none" href="#groups-note-1">*</a>';
			$output .= '</p>';
			$output .= '<p>';
			$output .= __( 'This <strong>requires</strong> the <em>Gravity Forms User Registration Add-On</em> and the form to be enabled for user registration, otherwise it will have no effect.', GROUPS_GF_PLUGIN_DOMAIN );
			$output .= '</p>';

			$output .= sprintf(
				'<select id="groups-on-registration-add" class="groups" name="groups-on-registration-add[]" multiple="multiple" placeholder="%s" data-placeholder="%s">',
				esc_attr( __( 'Choose groups &hellip;', GROUPS_GF_PLUGIN_DOMAIN ) ) ,
				esc_attr( __( 'Choose groups &hellip;', GROUPS_GF_PLUGIN_DOMAIN ) )
			);
			foreach( $groups as $group ) {
				$selected = in_array( Groups_Utility::id( $group->group_id ), $on_registration_add );
				$output .= sprintf( '<option value="%d" %s>%s</option>', Groups_Utility::id( $group->group_id ), $selected ? ' selected="selected" ' : '', wp_filter_nohtml_kses( $group->name ) );
			}
			$output .= '</select>';
			$output .= Groups_UIE::render_select( '#groups-on-registration-add' );

			$output .= '<br/>';

			$output .= '<h3>';
			$output .= __( 'Group Memberships for Payments', GROUPS_GF_PLUGIN_DOMAIN );
			$output .= '</h3>';

			$output .= '<p>';
			$output .= __( 'Add users who pay through this form to groups?', GROUPS_GF_PLUGIN_DOMAIN );
			$output .= '</p>';
			$output .= '<label>';
			$output .= sprintf( '<input type="checkbox" name="groups-on-payment" %s />', $on_payment ? ' checked="checked" ' : '' );
			$output .= ' ';
			$output .= __( 'Enabled', GROUPS_GF_PLUGIN_DOMAIN );
			$output .= '</label>';
			$output .= '<p>';
			$output .= __( 'If enabled, registered users who are logged in and pay through this form will be added to the chosen groups.', GROUPS_GF_PLUGIN_DOMAIN );
			$output .= ' ';
			$output .= '<a style="text-decoration:none" href="#groups-note-1">*</a>';
			$output .= '</p>';
			$output .= '<p>';
			$output .= __( 'This <strong>requires</strong> a <em>Gravity Forms Payment Add-On</em> and the form to be enabled for payments, otherwise it will have no effect.', GROUPS_GF_PLUGIN_DOMAIN );
			$output .= ' ';
			$output .= __( 'If new users are allowed to register through this form, the new user is added to the chosen groups if an approved payment is detected for the form.', GROUPS_GF_PLUGIN_DOMAIN );
			$output .= ' ';
			$output .= __( 'Otherwise, this has no effect for visitors who are not logged in and submit the form.', GROUPS_GF_PLUGIN_DOMAIN );
			$output .= '</p>';

			$output .= sprintf(
				'<select id="groups-on-payment-add" class="groups" name="groups-on-payment-add[]" multiple="multiple" placeholder="%s" data-placeholder="%s">',
				esc_attr( __( 'Choose groups &hellip;', GROUPS_GF_PLUGIN_DOMAIN ) ) ,
				esc_attr( __( 'Choose groups &hellip;', GROUPS_GF_PLUGIN_DOMAIN ) )
			);
			foreach( $groups as $group ) {
				$selected = in_array( Groups_Utility::id( $group->group_id ), $on_payment_add );
				$output .= sprintf( '<option value="%d" %s>%s</option>', Groups_Utility::id( $group->group_id ), $selected ? ' selected="selected" ' : '', wp_filter_nohtml_kses( $group->name ) );
			}
			$output .= '</select>';
			$output .= Groups_UIE::render_select( '#groups-on-payment-add' );

			$output .= '<br/>';

			$output .= wp_nonce_field( self::SAVE, self::NONCE, true, false );

			$output .= sprintf( '<input class="button-primary gfbutton" type="submit" name="groups-save" value="%s" />', __( 'Save', GROUPS_GF_PLUGIN_DOMAIN ) );

			$output .= '</div>';
			$output .= '</form>';

			$output .= '</div>'; // .groups-form-settings

			$output .= '<p id="groups-note-1">';
			$output .= '* ';
			$output .= __( 'Note that all users belong to the <em>Registered</em> group automatically.', GROUPS_GF_PLUGIN_DOMAIN );
			$output .= ' ';
			$output .= __( 'To choose a group, at least one group other than <em>Registered</em> must exist.', GROUPS_GF_PLUGIN_DOMAIN );
			$output .= '</p>';

			echo $output;

			GFFormSettings::page_footer();

		}
	}

}
Groups_Gravity_Forms_Form_Settings::init();
