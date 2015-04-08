<?php
/**
 * class-groups-gravity-forms-handler.php
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
 * Form handler.
 * 
 * I. Gravity Forms action invocation sequences
 * --------------------------------------------
 * 
 * (1) Only when the form is enabled for user registration.
 * 
 * - Form Submission
 * 
 *   1. gform_user_registered (1)
 *   2. gform_after_submission
 * 
 * 
 * - User Registration
 * 
 *   1. gform_user_registered
 *   2. gform_after_submission
 * 
 * 
 * - Authorize.net
 * 
 *   1. gform_authorizenet_post_capture
 *   2. gform_user_registered (1)
 *   3. gform_after_submission
 * 
 * 
 * - PayPal
 * 
 *   1. gform_after_submission
 *   2. gform_post_payment_status
 * 
 * 
 * - PayPal Pro
 * 
 *   1. gform_after_submission
 *   2. gform_paypalpro_post_ipn
 * 
 * 
 * II. Gravity Forms Subscription actions & filters
 * ------------------------------------------------
 * 
 * Currently the actions triggered by most of the 'developer license'
 * payment add-ons are insufficient or prone to drastic changes if we were
 * to build a sound subscription-based membership solution. For that we need
 * at least an action for when a subscription ends. Currently (GF 1.8.x) no
 * actions are provided by any of the PayPal * add-ons for that.
 * 
 * Support for subscriptions is postponed until that is solved.
 * 
 * PayPal Payments Standard
 * 
 *   do_action("gform_subscription_canceled", $entry, $config, $transaction_id);
 *   do_action("gform_post_payment_status", $config, $entry, $status,  $transaction_id, $subscriber_id, $amount, $pending_reason, $reason);
 * 
 * PayPal Pro
 * 
 *   do_action("gform_subscription_canceled", $lead, $config, $lead["transaction_id"], "paypalpro");
 *   do_action("gform_paypalpro_fulfillment", $entry, $config, $transaction_id, $initial_payment_amount, $subscription_amount);
 * 
 * PayPal Payments Pro
 * 
 *   do_action("gform_paypalpaymentspro_subscription_canceled", $lead, $lead["transaction_id"]);  
 *   do_action("gform_paypalpaymentspro_after_subscription_payment", $entry, $subscription_id, $profile_status["AMT"]);
 *   do_action("gform_paypalpaymentspro_subscription_expired", $entry, $subscription_id);
 *   
 *   $subscription = apply_filters("gform_paypalpaymentspro_args_before_subscription", $subscription, $entry["form_id"]);
 * 
 * Authorize.net
 * 
 *   do_action("gform_authorizenet_post_create_subscription", $result["is_success"], $subscription, $arb_response, $entry, $form, $config);  
 *   do_action("gform_authorizenet_after_subscription_created", $subscription_id, $subscription->amount, $fee_amount);  
 *   do_action("gform_authorizenet_post_recurring_payment", $subscription_id, $entry, $new_payment_amount, $payment_count);  
 *   do_action("gform_authorizenet_after_recurring_payment", $entry, $subscription_id, $subscription_id, $new_payment_amount);  
 *   do_action("gform_authorizenet_post_expire_subscription", $subscription_id, $entry);  
 *   do_action("gform_authorizenet_subscription_ended", $entry, $subscription_id, $transaction_id, $new_payment_amount);  
 *   do_action("gform_authorizenet_post_suspend_subscription", $subscription_id, $entry);  
 *   do_action("gform_authorizenet_subscription_suspended", $entry, $subscription_id, $transaction_id, $new_payment_amount);  
 *   do_action("gform_authorizenet_post_cancel_subscription", $subscription_id, $entry);  
 *   do_action("gform_authorizenet_subscription_canceled", $entry, $subscription_id, $transaction_id, $new_payment_amount);  
 *   do_action("gform_authorizenet_post_suspend_subscription", $subscription_id, $entry);  
 *   do_action("gform_authorizenet_subscription_suspended", $entry, $subscription_id, $transaction_id, $new_payment_amount);  
 *   do_action("gform_subscription_canceled", $lead, $config, $lead["transaction_id"], "authorize.net");
 *   
 *   $subscription = apply_filters("gform_authorizenet_subscription_pre_create", $subscription, $form_data, $config, $form);  
 *   $subscription = apply_filters("gform_authorizenet_before_start_subscription", $subscription, $form_data, $config, $form);  
 */
class Groups_Gravity_Forms_Handler {

	/**
	 * Initialization action on WordPress init.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'wp_init' ) );
	}

	/**
	 * Form action and others.
	 */
	public static function wp_init() {

		// On registration
		// do_action('gform_user_registered', $user_id, $config, $lead, $user_data['password']);
		add_action( 'gform_user_registered', array( __CLASS__, 'gform_user_registered' ), 10, 4 );

		// On form submission
		add_action( 'gform_after_submission', array( __CLASS__, 'gform_after_submission' ), 10, 2 );

		// On payment : PayPal Payments Standard
		// do_action("gform_post_payment_status", $config, $entry, $status,  $transaction_id, $subscriber_id, $amount, $pending_reason, $reason);
		add_action( 'gform_post_payment_status', array( __CLASS__, 'gform_post_payment_status' ), 10, 8 );

		// On payment : PayPal Pro
		// do_action("gform_paypalpro_post_ipn", $_POST, $entry, $config, $cancel);
		add_action( 'gform_paypalpro_post_ipn', array( __CLASS__, 'gform_paypalpro_post_ipn' ), 10, 4 );

		// On payment : PayPal Payments Pro
		// @todo later - The current Gravity Forms PayPal Payments Pro Add-On
		// Version 1.0 does not provide any hooks that can be used for this.

		// On payment : Authorize.net
		// do_action("gform_authorizenet_post_capture", $result["is_success"], $form_data["amount"], $entry, $form, $config, $response);
		add_action( 'gform_authorizenet_post_capture', array( __CLASS__, 'gform_authorizenet_post_capture' ), 10, 6 );

		// Subscription cancellations - see note below on gform_post_payment_status
		//add_action( 'gform_subscription_canceled', array( __CLASS__, 'gform_subscription_canceled'), 10, 3 );
		//add_action( 'groups_gravityforms_scheduled_termination', array( __CLASS__, 'groups_gravityforms_scheduled_termination' ), 10, 2 );
	}

	/**
	 * On registration & for some approved payments.
	 * 
	 * @param int $user_id
	 * @param array $config
	 * @param array $lead
	 * @param string $password
	 */
	public static function gform_user_registered( $user_id, $config, $lead, $password ) {

		if ( defined( 'GROUPS_GF_DEBUG' ) && GROUPS_GF_DEBUG ) {
			error_log( __METHOD__ );
			error_log( __METHOD__ . ' ----- user_id ' . var_export( $user_id, true ) );
			error_log( __METHOD__ . ' ----- config ' . var_export( $config, true ) );
			error_log( __METHOD__ . ' ----- lead ' . var_export( $lead, true ) );
		}

		if ( isset( $lead['form_id'] ) && ( $form = RGFormsModel::get_form_meta( $lead['form_id'] ) ) ) {

			$on_registration     = isset( $form['groups']['on_registration']['enabled'] ) ? $form['groups']['on_registration']['enabled'] : false;
			$on_registration_add = isset( $form['groups']['on_registration']['add'] ) ? $form['groups']['on_registration']['add'] : array();
			if ( $on_registration ) {
				foreach( $on_registration_add as $group_id ) {
					Groups_User_Group::create( array( 'user_id' => $user_id, 'group_id' => $group_id ) );
				}
			}

			// If payment is approved and the user is registered through the form,
			// this is the only direct way (through the actions) to know that
			// the user needs to be assigned to groups based on payment (the
			// created_by field is null for all actions but we get the user_id
			// as method parameter).
			$on_payment     = isset( $form['groups']['on_payment']['enabled'] ) ? $form['groups']['on_payment']['enabled'] : false;
			$on_payment_add = isset( $form['groups']['on_payment']['add'] ) ? $form['groups']['on_payment']['add'] : array();
			if ( $on_payment ) {
				if ( self::is_payment_valid( $lead ) || self::is_subscription_active( $lead ) ) {
					foreach( $on_payment_add as $group_id ) {
						Groups_User_Group::create( array( 'user_id' => $user_id, 'group_id' => $group_id ) );
					}
				}
			}

		}
	}

	/**
	 * On payment : PayPal Payments Standard
	 * 
	 * @param array $config
	 * @param array $entry
	 * @param string $status
	 * @param string $transaction_id
	 * @param string $subscriber_id
	 * @param string $amount
	 * @param strintg $pending_reason
	 * @param string $reason
	 */
	public static function gform_post_payment_status( $config, $entry, $status,  $transaction_id, $subscriber_id, $amount, $pending_reason, $reason ) {

		if ( defined( 'GROUPS_GF_DEBUG' ) && GROUPS_GF_DEBUG ) {
			error_log( __METHOD__ );
			error_log( __METHOD__ . ' ----- config ' . var_export( $config, true ) );
			error_log( __METHOD__ . ' ----- entry ' . var_export( $entry, true ) );
			error_log( __METHOD__ . ' ----- status ' . var_export( $status, true ) );
			error_log( __METHOD__ . ' ----- transaction_id ' . var_export( $transaction_id, true ) );
			error_log( __METHOD__ . ' ----- subscriber_id ' . var_export( $subscriber_id, true ) );
			error_log( __METHOD__ . ' ----- amount ' . var_export( $amount, true ) );
			error_log( __METHOD__ . ' ----- pending_reason ' . var_export( $pending_reason, true ) );
			error_log( __METHOD__ . ' ----- reason ' . var_export( $reason, true ) );
		}

		if ( $user_id = self::get_user_id( $entry ) ) {
			if ( isset( $entry['form_id'] ) && ( $form = RGFormsModel::get_form_meta( $entry['form_id' ] ) ) ) {
				$on_payment     = isset( $form['groups']['on_payment']['enabled'] ) ? $form['groups']['on_payment']['enabled'] : false;
				$on_payment_add = isset( $form['groups']['on_payment']['add'] ) ? $form['groups']['on_payment']['add'] : array();
				if ( $on_payment ) {
					if ( self::is_payment_valid( $entry ) || self::is_subscription_active( $entry ) ) {
						foreach( $on_payment_add as $group_id ) {
							Groups_User_Group::create( array( 'user_id' => $user_id, 'group_id' => $group_id ) );
						}
					} else if ( self::is_payment_refunded( $entry ) || self::is_subscription_expired( $entry ) ) {
						foreach( $on_payment_add as $group_id ) {
							Groups_User_Group::delete( $user_id, $group_id );
						}
					}
					// Canceled subscriptions :
					// Schedule instead of removing directly?
					// Currently we can't do that because we don't know when it's due to terminate.
					// BUT we get informed when the subscription expires and
					// can remove the group membership as above.
					// Otherwise we would need: 
					// $timestamp = ?
					//wp_schedule_single_event( $timestamp, 'groups_gravityforms_scheduled_termination', array( $entry['id'], $user_id ) );
				}
			}
		}
	}

	/**
	 * On payment : PayPal Pro
	 * 
	 * @param array $post
	 * @param array $entry
	 * @param array $config
	 * @param boolean $cancel
	 */
	public static function gform_paypalpro_post_ipn( $post, $entry, $config, $cancel ) {

		if ( defined( 'GROUPS_GF_DEBUG' ) && GROUPS_GF_DEBUG ) {
			error_log( __METHOD__ );
			error_log( __METHOD__ . ' ----- post ' . var_export( $post, true ) );
			error_log( __METHOD__ . ' ----- entry ' . var_export( $entry, true ) );
			error_log( __METHOD__ . ' ----- config ' . var_export( $config, true ) );
			error_log( __METHOD__ . ' ----- cancel ' . var_export( $cancel, true ) );
		}

		if ( $user_id = self::get_user_id( $entry ) ) {
			if ( isset( $entry['form_id'] ) && ( $form = RGFormsModel::get_form_meta( $entry['form_id' ] ) ) ) {
				$on_payment     = isset( $form['groups']['on_payment']['enabled'] ) ? $form['groups']['on_payment']['enabled'] : false;
				$on_payment_add = isset( $form['groups']['on_payment']['add'] ) ? $form['groups']['on_payment']['add'] : array();
				if ( $on_payment ) {
					if ( self::is_payment_valid( $entry ) || self::is_subscription_active( $entry ) ) {
						foreach( $on_payment_add as $group_id ) {
							Groups_User_Group::create( array( 'user_id' => $user_id, 'group_id' => $group_id ) );
						}
					} else if ( self::is_payment_refunded( $entry ) || self::is_subscription_expired( $entry ) ) {
						foreach( $on_payment_add as $group_id ) {
							Groups_User_Group::delete( $user_id, $group_id );
						}
					}
				}
			}
		}
	}

	/**
	 * On payment : Authorize.net payment capture
	 * 
	 * Note that the user ID is only available here if the form has been
	 * submitted by a user who is logged in. If a new user registers, this
	 * will have no effect and
	 * Groups_Gravity_Forms_Handler::gform_user_registered() will handle the
	 * group assignment based on approved payment.
	 * 
	 * @see Groups_Gravity_Forms_Handler::gform_user_registered()
	 * 
	 * @param boolean $is_success
	 * @param string $amount
	 * @param array $entry
	 * @param array $form
	 * @param array $config
	 * @param string $response
	 */
	public static function gform_authorizenet_post_capture( $is_success, $amount, $entry, $form, $config, $response ) {

		if ( defined( 'GROUPS_GF_DEBUG' ) && GROUPS_GF_DEBUG ) {
			error_log( __METHOD__ );
			error_log( __METHOD__ . ' ----- is_success ' . var_export( $is_success, true ) );
			error_log( __METHOD__ . ' ----- amount ' . var_export( $amount, true ) );
			error_log( __METHOD__ . ' ----- entry ' . var_export( $entry, true ) );
			error_log( __METHOD__ . ' ----- form ' . var_export( $form, true ) );
			error_log( __METHOD__ . ' ----- config ' . var_export( $config, true ) );
			error_log( __METHOD__ . ' ----- response ' . var_export( $response, true ) );
		}

		if ( $user_id = self::get_user_id( $entry ) ) {
			$on_payment     = isset( $form['groups']['on_payment']['enabled'] ) ? $form['groups']['on_payment']['enabled'] : false;
			$on_payment_add = isset( $form['groups']['on_payment']['add'] ) ? $form['groups']['on_payment']['add'] : array();
			if ( $on_payment ) {
				if ( self::is_payment_valid( $entry ) || self::is_subscription_active( $entry ) ) {
					foreach( $on_payment_add as $group_id ) {
						Groups_User_Group::create( array( 'user_id' => $user_id, 'group_id' => $group_id ) );
					}
				}
			}
		}
	}

	/**
	 * On form submission & for some approved payments.
	 * 
	 * Invoked at the end of the form submission process, after form
	 * validation, notification and entry creation.
	 * 
	 * @param array $entry
	 * @param array $form
	 */
	public static function gform_after_submission( $entry, $form ) {

		if ( defined( 'GROUPS_GF_DEBUG' ) && GROUPS_GF_DEBUG ) {
			error_log( __METHOD__ );
			error_log( __METHOD__ . ' ----- entry ' . var_export( $entry, true ) );
			error_log( __METHOD__ . ' ----- form ' . var_export( $form, true ) );
		}

		if ( $user_id = self::get_user_id( $entry ) ) {

			// Add the user to the groups after form submission:
			$on_submission       = isset( $form['groups']['on_submission'] ) ? $form['groups']['on_submission']['enabled'] : false;
			$on_submission_add   = isset( $form['groups']['on_submission']['add'] ) ? $form['groups']['on_submission']['add'] : array();
			$on_submission_field = isset( $form['groups']['on_submission']['field'] ) ? $form['groups']['on_submission']['field'] : null;
			if ( $on_submission ) {
				// fixed
				foreach( $on_submission_add as $group_id ) {
					Groups_User_Group::create( array( 'user_id' => $user_id, 'group_id' => $group_id ) );
				}
				// by field
				if ( $on_submission_field ) {
					$value = rgar( $entry, $on_submission_field );
					$field = GFFormsModel::get_field( $form, $on_submission_field );
					$values = GFFormsModel::get_field_value( $field );
					if ( is_array( $values ) ) {
						foreach( $values as $key => $value ) {
							if ( $group = Groups_Group::read_by_name( $value ) ) {
								Groups_User_Group::create( array( 'user_id' => $user_id, 'group_id' => $group->group_id ) );
							}
						}
					} else {
						if ( $group = Groups_Group::read_by_name( $values ) ) {
							Groups_User_Group::create( array( 'user_id' => $user_id, 'group_id' => $group->group_id ) );
						}
					}
				}
			}

			// For payment gateway implementations (like GF's Authorize.net), where
			// the payment has already been processed before we get here, process
			// the group membership addition if there is an approved payment for
			// the entry - this here will cover for the case where the user is
			// logged in, it has no effect if the user is newly registered
			// while processing the form.
			$on_payment     = isset( $form['groups']['on_payment']['enabled'] ) ? $form['groups']['on_payment']['enabled'] : false;
			$on_payment_add = isset( $form['groups']['on_payment']['add'] ) ? $form['groups']['on_payment']['add'] : array();
			if ( $on_payment ) {
				if ( self::is_payment_valid( $entry ) || self::is_subscription_active( $entry ) ) {
					foreach( $on_payment_add as $group_id ) {
						Groups_User_Group::create( array( 'user_id' => $user_id, 'group_id' => $group_id ) );
					}
				}
			}
		}
	}

	//public static function gform_subscription_canceled( $lead, $config, $transaction_id, $gateway = null ) {
	//}

	//public static function groups_gravityforms_scheduled_termination( $entry_id, $user_id ) {
	//}

	/**
	 * Returns true if the payment status is 'Approved' or 'Paid'.
	 * 
	 * @param array $entry
	 * @return boolean
	 */
	private static function is_payment_valid( $entry ) {
		return self::is_payment_approved( $entry ) || self::is_payment_paid( $entry );
	}

	/**
	 * Evaluates to true if the payment status is set and its value is
	 * 'Paid'. In any other case, this will return false.
	 *
	 * If the payment status is not set, i.e. when
	 * $entry['payment_status'] === null, false is also returned.
	 *
	 * @param array $entry
	 * @return boolean
	 */
	private static function is_payment_paid( $entry ) {
		$result = false;
		if ( isset( $entry['id'] ) && isset( $entry['payment_status'] ) && ( $entry['payment_status'] !== null ) ) {
			switch( $entry['payment_status'] ) {
				case 'Paid' :
					$result = true;
					break;
			}
		}
		return $result;
	}

	/**
	 * Evaluates to true if the payment status is set and its value is
	 * 'Approved'. In any other case, this will return false.
	 * 
	 * If the payment status is not set, i.e. when
	 * $entry['payment_status'] === null, false is also returned.
	 * 
	 * @param array $entry
	 * @return boolean
	 */
	private static function is_payment_approved( $entry ) {
		$result = false;
		if ( isset( $entry['id'] ) && isset( $entry['payment_status'] ) && ( $entry['payment_status'] !== null ) ) {
			switch( $entry['payment_status'] ) {
				case 'Approved' :
					$result = true;
					break;
			}
		}
		return $result;
	}

	/**
	 * Evaluates to true if the payment status is set and its value is
	 * 'Refunded' or 'Reversed'. In any other case, this will return false.
	 * 
	 * If the payment status is not set, i.e. when
	 * $entry['payment_status'] === null, false is also returned.
	 * 
	 * @param array $entry
	 * @return boolean
	 */
	private static function is_payment_refunded( $entry ) {
		$result = false;
		if ( isset( $entry['id'] ) && isset( $entry['payment_status'] ) && ( $entry['payment_status'] !== null ) ) {
			switch( $entry['payment_status'] ) {
				case 'Refunded' :
				case 'Reversed' :
					$result = true;
					break;
			}
		}
		return $result;
	}

	/**
	 * Evaluates to true if the payment status is set and its value is
	 * 'Active'. In any other case, this will return false.
	 * 
	 * If the payment status is not set, i.e. when
	 * $entry['payment_status'] === null, false is also returned.
	 * 
	 * @param array $entry
	 * @return boolean
	 */
	private static function is_subscription_active( $entry ) {
		$result = false;
		if (
			isset( $entry['id'] ) &&
			isset( $entry['payment_status'] ) &&
			( $entry['payment_status'] !== null ) &&
			isset( $entry['transaction_type'] ) &&
			( $entry['transaction_type'] == '2' ) // subscription (magic number)
		) {
			switch( $entry['payment_status'] ) {
				case 'Active' :
				case 'Processing' :
					$result = true;
					break;
			}
		}
		return $result;
	}

	/**
	 * Evaluates to true if the payment status is set and its value is
	 * 'Expired'. In any other case, this will return false.
	 * 
	 * If the payment status is not set, i.e. when
	 * $entry['payment_status'] === null, false is also returned.
	 * 
	 * @param array $entry
	 * @return boolean
	 */
	private static function is_subscription_expired( $entry ) {
		$result = false;
		if ( isset( $entry['id'] ) && isset( $entry['payment_status'] ) && ( $entry['payment_status'] !== null ) ) {
			switch( $entry['payment_status'] ) {
				case 'Expired' : // PayPal subscription
					$result = true;
					break;
			}
		}
		return $result;
	}

	/**
	 * Evaluates to true if the payment status is set and its value is
	 * 'Canceled'. In any other case, this will return false.
	 * 
	 * If the payment status is not set, i.e. when
	 * $entry['payment_status'] === null, false is also returned.
	 * 
	 * @param array $entry
	 * @return boolean
	 */
	private static function is_subscription_canceled( $entry ) {
		$result = false;
		if ( isset( $entry['id'] ) && isset( $entry['payment_status'] ) && ( $entry['payment_status'] !== null ) ) {
			switch( $entry['payment_status'] ) {
				case 'Canceled' : // PayPal subscription
					$result = true;
					break;
			}
		}
		return $result;
	}

	/**
	 * Returns the user ID related to an entry.
	 * 
	 * @param array $entry
	 * @return user ID or null
	 */
	private static function get_user_id( $entry ) {

		$user_id = isset( $entry['created_by'] ) ? $entry['created_by'] : null;

		// Otherwise try to obtain the user id from user registration.
		if ( !$user_id ) {
			// This method (and the usermeta it gets the info from) exists
			// only if the User Registration Add-On is available.
			if ( method_exists( 'GFUserData', 'get_user_by_entry_id' ) ) {
				if ( $user = GFUserData::get_user_by_entry_id( $entry['id'] ) ) {
					$user_id = $user->ID;
				}
			}
		}
		return $user_id;
	}

}
Groups_Gravity_Forms_Handler::init();
