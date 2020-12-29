<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Uni_Cpo_Enqrs Class
 */
final class Uni_Cpo_Enqrs {

	public $version = '1.0.2';

	protected static $_instance = null;

	/**
	 * Throw error on object clone
	 *
	 * The whole idea of the singleton design pattern is that there is a single
	 * object therefore, we don't want the object to be cloned.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'uni-cpo-enquiries' ), '1.0.0' );
	}

	/**
	 * Disable unserializing of the class
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'uni-cpo-enquiries' ), '1.0.0' );
	}

	/**
	 * Main Uni_Cpo_Enqrs Instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Uni_Cpo_Enqrs Constructor.
	 */
	public function __construct() {
		$this->init_hooks();
		include_once( $this->plugin_path() . '/includes/class-uni-cpo-enqrs-ajax.php' );
		include_once( $this->plugin_path() . '/includes/uni-cpo-enqrs-functions.php' );
		if ( ! class_exists( 'WP_Mail' ) ) {
			include_once( $this->plugin_path() . '/includes/WP_Mail.php' );
		}
	}

	/**
	 *  Init hooks
	 */
	private function init_hooks() {
		add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain' ), 0 );
		add_action( 'wp_enqueue_scripts', array( $this, 'load_scripts' ) );

		add_action( 'woocommerce_product_options_pricing', array( $this, 'display_fields' ) );
		add_action( 'woocommerce_process_product_meta', array( $this, 'save_fields' ) );

		register_activation_hook( __FILE__, array( $this, 'activation' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivation' ) );
	}

	function load_scripts() {
		if ( is_singular( 'product' ) ) {
			global $post;
			$is_enabled              = get_post_meta( $post->ID, 'uni_cpo_enqrs_enable', true );
			$btn_label               = get_post_meta( $post->ID, 'uni_cpo_enqrs_btn_label', true );
			$is_disabled_add_to_cart = get_post_meta( $post->ID, 'uni_cpo_enqrs_disable_add_to_cart', true );

			if ( empty( $btn_label ) ) {
				$btn_label = __( 'Send inquiry', 'uni-cpo-enqrs' );
			}

			if ( isset( $is_enabled ) && $is_enabled === 'yes' ) {
				wp_enqueue_style(
					'uni-cpo-enqrs-styles',
					$this->plugin_url() . '/assets/css/frontend.css',
					array(),
					$this->version
				);

				wp_register_script(
					'uni-cpo-enqrs-script',
					$this->plugin_url() . '/assets/js/frontend.js',
					array( 'jquery' ),
					$this->version,
					true
				);

				wp_enqueue_script( 'uni-cpo-enqrs-script' );

				$enqrsData = [
					'btnLabel'         => $btn_label,
					'disableAddToCart' => $is_disabled_add_to_cart,
					'name'             => __( 'Name', 'uni-cpo-enqrs' ),
					'email'            => __( 'Email', 'uni-cpo-enqrs' ),
					'phone'            => __( 'Phone', 'uni-cpo-enqrs' ),
					'notes'            => __( 'Notes', 'uni-cpo-enqrs' ),
					'submit'           => __( 'Submit', 'uni-cpo-enqrs' ),
					'formTitle'        => __( 'Inquiry form', 'uni-cpo-enqrs' )
				];

				wp_localize_script( 'uni-cpo-enqrs-script', 'enqrsData', $enqrsData );
			}
		}
	}

	/**
	 * Display product settings fields
	 */
	public function display_fields() {
		woocommerce_wp_checkbox(
			array(
				'id'          => 'uni_cpo_enqrs_enable',
				'label'       => __( 'Enable "send inquiry"', 'uni-cpo-enqrs' ),
				'description' => __( 'Enable "send inquiry" functionality for this specific product.', 'uni-cpo-enqrs' ),
			)
		);

		woocommerce_wp_text_input(
			array(
				'id'          => 'uni_cpo_enqrs_btn_label',
				'label'       => __( 'Custom "send inquiry" button label', 'uni-cpo-enqrs' ),
				'description' => __( 'Set your own label instead of default "Send inquiry" when using "send inquiry" functionality', 'uni-cpo-enqrs' ),
			)
		);

		woocommerce_wp_checkbox(
			array(
				'id'          => 'uni_cpo_enqrs_disable_add_to_cart',
				'label'       => __( 'Disable "add to cart" button', 'uni-cpo-enqrs' ),
				'description' => __( 'Disable adding to cart functionality and hide "add to cart" button', 'uni-cpo-enqrs' ),
			)
		);
	}

	/**
	 * Save the custom fields using CRUD method
	 *
	 * @param $post_id
	 */
	public function save_fields( $post_id ) {

		$product = wc_get_product( $post_id );
		$enable  = isset( $_POST['uni_cpo_enqrs_enable'] ) ? 'yes' : 'no';
		$product->update_meta_data( 'uni_cpo_enqrs_enable', sanitize_text_field( $enable ) );
		$label = isset( $_POST['uni_cpo_enqrs_btn_label'] ) ? $_POST['uni_cpo_enqrs_btn_label'] : '';
		$product->update_meta_data( 'uni_cpo_enqrs_btn_label', sanitize_text_field( $label ) );
		$disable_add_to_cart = isset( $_POST['uni_cpo_enqrs_disable_add_to_cart'] ) ? 'yes' : 'no';
		$product->update_meta_data( 'uni_cpo_enqrs_disable_add_to_cart', sanitize_text_field( $disable_add_to_cart ) );

		$product->save();

	}

	/**
	 * load_plugin_textdomain()
	 */
	public function load_plugin_textdomain() {
		load_textdomain( 'uni-cpo-enqrs', WP_LANG_DIR . '/uni-cpo-enquiries/uni-cpo-enqrs-' . get_locale() . '.mo' );
		load_plugin_textdomain( 'uni-cpo-enqrs', false, plugin_basename( dirname( __FILE__ ) ) . "/languages" );
	}

	/**
	 * plugin_url()
	 */
	public function plugin_url() {
		return untrailingslashit( plugins_url( '/', __FILE__ ) );
	}

	/**
	 * plugin_path()
	 */
	public function plugin_path() {
		return untrailingslashit( plugin_dir_path( __FILE__ ) );
	}

	/**
	 * Get Ajax URL.
	 * @return string
	 */
	public function ajax_url() {
		return admin_url( 'admin-ajax.php', 'relative' );
	}

	/**
	 * on plugin activation
	 */
	public function activation() {
	}

	/**
	 * on plugin activation
	 */
	public function deactivation() {
	}
}
