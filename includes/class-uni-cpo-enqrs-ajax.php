<?php
/*
*   Ajax Class
*
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Uni_Cpo_Enqrs_Ajax {

	/**
	 * Hook in ajax handlers.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'define_ajax' ), 0 );
		add_action( 'template_redirect', array( __CLASS__, 'do_cpo_ajax' ), 0 );
		self::add_ajax_events();
	}

	/**
	 * Get Ajax Endpoint.
	 */
	public static function get_endpoint( $request = '' ) {
		return esc_url_raw( add_query_arg( 'cpo-enqrs-ajax', $request ) );
	}

	/**
	 * Set CPO AJAX constant and headers.
	 */
	public static function define_ajax() {
		if ( ! empty( $_GET['cpo-enqrs-ajax'] ) ) {
			if ( ! defined( 'DOING_AJAX' ) ) {
				define( 'DOING_AJAX', true );
			}
			if ( ! defined( 'CPO_URLS_DOING_AJAX' ) ) {
				define( 'CPO_ENQRS_DOING_AJAX', true );
			}
			if ( ! WP_DEBUG || ( WP_DEBUG && ! WP_DEBUG_DISPLAY ) ) {
				@ini_set( 'display_errors', 0 ); // Turn off display_errors during AJAX events to prevent malformed JSON
			}
			$GLOBALS['wpdb']->hide_errors();
		}
	}

	/**
	 * Send headers for CPO Ajax Requests
	 */
	private static function cpo_ajax_headers() {
		send_origin_headers();
		@header( 'Content-Type: text/html; charset=' . get_option( 'blog_charset' ) );
		@header( 'X-Robots-Tag: noindex' );
		send_nosniff_header();
		nocache_headers();
		status_header( 200 );
	}

	/**
	 * Check for CPO Ajax request and fire action.
	 */
	public static function do_cpo_ajax() {
		global $wp_query;

		if ( ! empty( $_GET['cpo-enqrs-ajax'] ) ) {
			$wp_query->set( 'cpo-enqrs-ajax', sanitize_text_field( $_GET['cpo-enqrs-ajax'] ) );
		}

		if ( $action = $wp_query->get( 'cpo-enqrs-ajax' ) ) {
			self::cpo_ajax_headers();
			do_action( 'cpo_ajax_' . sanitize_text_field( $action ) );
			die();
		}
	}

	/**
	 *   Hook in methods
	 */
	public static function add_ajax_events() {

		$ajax_events = array(
			'uni_cpo_enqrs_create_enquiry' => true
		);

		foreach ( $ajax_events as $ajax_event => $priv ) {
			add_action( 'wp_ajax_' . $ajax_event, array( __CLASS__, $ajax_event ) );

			if ( $priv ) {
				add_action( 'wp_ajax_nopriv_' . $ajax_event, array( __CLASS__, $ajax_event ) );
			}
		}

	}

	/**
	 *   uni_cpo_enqrs_create_enquiry
	 */
	public static function uni_cpo_enqrs_create_enquiry() {
		try {
			if ( ! empty( $_POST['json'] ) ) {
				$data            = stripslashes_deep( $_POST['json'] );
				$data            = json_decode( $data, true );
				$data['options'] = uni_enqrs_get_nice_options_data( $data['options'] );

				uni_enqrs_send_email( $data, $_POST['pid'] );

				wp_send_json_success( array( 'message' => __( 'Successfully sent!', 'uni-cpo-enqrs' ) ) );
			} else {
				wp_send_json_error( array( 'error' => __( 'Error!', 'uni-cpo-enqrs' ) ) );
			}
		} catch ( Exception $e ) {
			wp_send_json_error( array( 'error' => $e->getMessage() ) );
		}
	}
}

Uni_Cpo_Enqrs_Ajax::init();
