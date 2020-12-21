<?php
/**
 * Plugin Name: Uni CPO Inquiries
 * Plugin URI: https://builderius.io
 * Description: Adds 'request inquiry' form on the product page.
 * Version: 1.0.0
 * Author: MooMoo Agency
 * Author URI: http://moomoo.agency
 * Domain Path: /languages/
 * Text Domain: uni-cpo-enquiries
 * Requires PHP: 7.2
 * WC requires at least: 4.5
 * WC tested up to: 4.7.0
 * License: GPL v3
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! function_exists( 'UniCpoEnqrs' ) ) {

	// Include the main class.
	if ( ! class_exists( 'UniCpoEnqrs' ) ) {
		include_once dirname( __FILE__ ) . '/class-uni-cpo-enquiries.php';
	}
	/**
	 * Main instance of Uni_Cpo_Enqrs.
	 *
	 * Returns the main instance of Uni_Cpo_Enqrs to prevent the need to use globals.
	 *
	 * @return Uni_Cpo_Enqrs
	 * @since  1.0.0
	 */
	function UniCpoEnqrs() {
		return Uni_Cpo_Enqrs::instance();
	}

	// Global for backwards compatibility.
	$GLOBALS['unicpoenqrs'] = UniCpoEnqrs();

}