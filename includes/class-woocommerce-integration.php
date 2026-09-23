<?php
/**
 * WooCommerce Integration Helper
 * Optional file for enhanced WooCommerce email handling
 * 
 * This file is optional and not required for basic functionality.
 * Include it in your main plugin if you want advanced WooCommerce features.
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Only load if WooCommerce is active
if ( ! class_exists( 'WooCommerce' ) ) {
	return;
}

/**
 * WooCommerce Integration Class
 */
class Duck_Mail_WooCommerce {

	/**
	 * Single instance of the class
	 */
	private static $instance = null;

	/**
	 * Get singleton instance
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->init_hooks();
	}

	/**
	 * Initialize hooks
	 */
	private function init_hooks() {
		// Hook into WooCommerce emails
		add_filter( 'woocommerce_mail_callback_params', array( $this, 'handle_woocommerce_mail' ) );
		
		// Add admin notice for WooCommerce compatibility
		add_action( 'admin_notices', array( $this, 'show_woocommerce_notice' ) );
	}

	/**
	 * Handle WooCommerce mail
	 *
	 * @param array $params Mail parameters
	 * @return array
	 */
	public function handle_woocommerce_mail( $params ) {
		// WooCommerce uses wp_mail, which we already handle in the main handler
		// This is here for future enhancements
		return $params;
	}

	/**
	 * Show WooCommerce compatibility notice
	 */
	public function show_woocommerce_notice() {
		// Only show to admins
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Check if this is the plugin settings page
		if ( ! isset( $_GET['page'] ) || $_GET['page'] !== 'duck-mail' ) {
			return;
		}

		// Show notice
		?>
		<div class="notice notice-success is-dismissible">
			<p>
				<strong><?php esc_html_e( 'WooCommerce Compatibility', 'duck-mail' ); ?>:</strong>
				<?php esc_html_e( 'This plugin fully supports WooCommerce email sending. All order emails, customer notifications, and OTP emails will use PHP mail() function.', 'duck-mail' ); ?>
			</p>
		</div>
		<?php
	}
}

// Initialize WooCommerce integration if WooCommerce is active
if ( did_action( 'plugins_loaded' ) ) {
	Duck_Mail_WooCommerce::get_instance();
} else {
	add_action( 'plugins_loaded', array( 'Duck_Mail_WooCommerce', 'get_instance' ) );
}
