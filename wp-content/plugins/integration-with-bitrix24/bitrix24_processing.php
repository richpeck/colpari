<?php

/**
 * Bitrix24 connector's processing.
 *
 * @version     1.0.0
 * @author      Bitrix24
 * @copyright   2016 Bitrix24
 * @link        https://bitrix24.com
 */
class Bitrix24Processing
{

	/**
	 * Processing for plugin Woocommerce.
	 * @see https://wordpress.org/plugins/woocommerce/
	 * @param int $order order id
	 */
	public function processing_woocommerce($order) {
		global $wpdb;
		$order = intval($order);
		//select data
		$meta_val = array();
		$meta_res = $wpdb->get_results($wpdb->prepare("SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id = '%d'", $order), ARRAY_A);
		foreach ((array)$meta_res as $meta_item) {
			$meta_val[$meta_item['meta_key']] = $meta_item['meta_value'];
		}
		//send data
		if (!empty($meta_val)) {
			B24Connector::getCurrent()->sendActivity(array(
				'AGENT' => array(
					'ORIGIN_ID' => $meta_val['_customer_user'] ? $meta_val['_customer_user'] : md5($meta_val['_billing_email']),
					'NAME' => $meta_val['_billing_first_name'] ? $meta_val['_billing_first_name'] : 'Guest',
					'LAST_NAME' => $meta_val['_billing_last_name'] ? $meta_val['_billing_last_name'] : 'Guest',
					'PHONE' => $meta_val['_billing_phone'],
					'EMAIL' => $meta_val['_billing_email'],

					'ADDRESS_COUNTRY_CODE' => $meta_val['_billing_country'],
					'ADDRESS_CITY' => $meta_val['_billing_city'],
					'ADDRESS_POSTAL_CODE' => $meta_val['_billing_postcode'],
					'ADDRESS_PROVINCE' => $meta_val['_billing_state'],
					'ADDRESS' => $meta_val['_billing_address_1'] . ' ' . $meta_val['_billing_address_2']
				),
				'ACTIVITY' => array(
					'ORIGIN_ID' => $order,
					'NUMBER' => $order,
					'SUBJECT' => 'New order',
					'DESCRIPTION' => 'New order',
					'RESULT_SUM' => $meta_val['_order_total'],
					'EXTERNAL_URL' => '/wp-admin/post.php?post='. $order .'&action=edit',
					'RESULT_CURRENCY_ID' => $meta_val['_order_currency'],
				)
			));
		}
	}

	/**
	 * Processing for plugin WP eCommerce.
	 * @see https://wordpress.org/plugins/wp-e-commerce/
	 * @param int $order order id
	 */
	public function processing_ecommerce($order) {
		global $wpdb;
		$order = intval($order);
		//select order data
		$order_res = $wpdb->get_results($wpdb->prepare("SELECT *, id, totalprice, user_ID FROM " . WPSC_TABLE_PURCHASE_LOGS . " WHERE sessionid = '%d'", $order), ARRAY_A);
		if (!empty($order_res)) {
			$order_res = array_shift($order_res);
			//select order fields
			$fields = array();
			$form_fields_res = $wpdb->get_results("SELECT * FROM " . WPSC_TABLE_CHECKOUT_FORMS, ARRAY_A);
			foreach ((array)$form_fields_res as $item) {
				$fields[$item['id']] = $item;
			}
			//select order values
			$values = array();
			$order_fields_res = $wpdb->get_results($wpdb->prepare("SELECT form_id, value FROM " . WPSC_TABLE_SUBMITTED_FORM_DATA . " WHERE log_id = '%d'", $order_res['id']), ARRAY_A);
			foreach ((array)$order_fields_res as $item) {
				if ($fields[$item['form_id']] && $fields[$item['form_id']]['unique_name']) {
					$values[$fields[$item['form_id']]['unique_name']] = $item['value'];
				}
			}
			//send data
			if (!empty($values)) {
				B24Connector::getCurrent()->sendActivity($r=array(
					'AGENT' => array(
						'ORIGIN_ID' => $order_res['user_ID'] ? $order_res['user_ID'] : md5($values['billingemail']),
						'NAME' => $values['billingfirstname'] ? $values['billingfirstname'] : 'Guest',
						'LAST_NAME' => $values['billinglastname'] ? $values['billinglastname'] : 'Guest',
						'PHONE' => $values['billingphone'],
						'EMAIL' => $values['billingemail'],

						'ADDRESS_COUNTRY_CODE' => $values['billingcountry'],
						'ADDRESS_CITY' => $values['billingcity'],
						'ADDRESS_POSTAL_CODE' => $values['billingpostcode'],
						'ADDRESS_PROVINCE' => $values['billingstate'],
						'ADDRESS' => $values['billingaddress']
					),
					'ACTIVITY' => array(
						'ORIGIN_ID' => $order_res['id'],
						'NUMBER' => $order_res['id'],
						'SUBJECT' => 'New order',
						'DESCRIPTION' => 'New order',
						'RESULT_SUM' => $order_res['totalprice'],
						'EXTERNAL_URL' => '',
					)
				));
			}
		}
	}

	/**
	 * Processing for plugin Easy Digital Downloads.
	 * @see https://wordpress.org/plugins/easy-digital-downloads/
	 */
	public function processing_digital_downloads() {

		if (function_exists('edd_get_purchase_session')) {
			if (($session = edd_get_purchase_session()) && $session['purchase_key']) {
				$purId = edd_get_purchase_id_by_key($session['purchase_key']);
				if ($payment = get_post($purId)) {
					$meta = edd_get_payment_meta($payment->ID);
					$cart = edd_get_payment_meta_cart_details($payment->ID);
					$user = edd_get_payment_meta_user_info($payment->ID);
					//send data
					if ($cart && $user) {
						$sum = 0;
						foreach ($cart as $item) {
							$sum += $item['price'];
						}
						B24Connector::getCurrent()->sendActivity($r=array(
							'AGENT' => array(
								'ORIGIN_ID' => $user['id'] ? $user['id'] : md5($user['email']),
								'NAME' => $user['first_name'] ? $user['first_name'] : 'Guest',
								'LAST_NAME' => $user['last_name'] ? $user['last_name'] : 'Guest',
								'EMAIL' => $user['email'],
							),
							'ACTIVITY' => array(
								'ORIGIN_ID' => $purId,
								'NUMBER' => $purId,
								'SUBJECT' => 'New order',
								'DESCRIPTION' => 'New order',
								'RESULT_SUM' => $sum,
								'RESULT_CURRENCY_ID' => $meta['currency'],
								'EXTERNAL_URL' => '/wp-admin/edit.php?post_type=download&page=edd-payment-history&view=view-order-details&id='.$purId,
							)
						));
					}
				}
			}
		}
	}
}


