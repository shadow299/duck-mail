<?php
/**
 * Plugin Name: Duck Mail
 * Plugin URI: https://github.com/yourusername/duck-mail
 * Description: A lightweight WordPress plugin that sends emails using PHP's native mail() function, replacing WP Mail SMTP without sending limits.
 * Version: 1.3.0
 * Author: Your Name
 * Author URI: https://yourwebsite.com
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: duck-mail
 * Domain Path: /languages
 * Requires at least: 5.0
 * Requires PHP: 7.2
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Check if already loaded
if ( defined( 'DUCK_MAIL_VERSION' ) ) {
	return;
}

// Define plugin constants
define( 'DUCK_MAIL_VERSION', '1.3.0' );
define( 'DUCK_MAIL_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'DUCK_MAIL_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'DUCK_MAIL_PLUGIN_FILE', __FILE__ );

// Include required files with error checking
if ( ! function_exists( 'duck_mail_require_file' ) ) {
	function duck_mail_require_file( $file ) {
		if ( ! file_exists( $file ) ) {
			wp_die( 'Duck Mail: Required file missing: ' . esc_html( basename( $file ) ) );
		}
		require_once $file;
	}
}

// Include class files
duck_mail_require_file( DUCK_MAIL_PLUGIN_DIR . 'includes/class-mail-handler.php' );
duck_mail_require_file( DUCK_MAIL_PLUGIN_DIR . 'includes/class-settings.php' );

/**
 * Main plugin class
 */
class Duck_Mail {

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
		// Plugin activation/deactivation
		register_activation_hook( DUCK_MAIL_PLUGIN_FILE, array( $this, 'activate' ) );
		register_deactivation_hook( DUCK_MAIL_PLUGIN_FILE, array( $this, 'deactivate' ) );

		// Initialize on plugins_loaded with higher priority
		add_action( 'plugins_loaded', array( $this, 'init_plugin' ), 1 );

		// Load plugin text domain
		add_action( 'init', array( $this, 'load_textdomain' ) );
	}

	/**
	 * Initialize plugin modules
	 */
	public function init_plugin() {
		// Check if required classes exist
		if ( class_exists( 'Duck_Mail_Handler' ) ) {
			Duck_Mail_Handler::get_instance();
		}
		if ( class_exists( 'Duck_Mail_Settings' ) ) {
			Duck_Mail_Settings::get_instance();
		}
	}

	/**
	 * Activate plugin
	 */
	public function activate() {
		// Set default options
		if ( ! get_option( 'duck_mail_from_name' ) ) {
			update_option( 'duck_mail_from_name', get_bloginfo( 'name' ) );
		}
		if ( ! get_option( 'duck_mail_from_email' ) ) {
			update_option( 'duck_mail_from_email', get_option( 'admin_email' ) );
		}
		if ( ! get_option( 'duck_mail_enabled' ) ) {
			update_option( 'duck_mail_enabled', 1 );
		}

		// Flush rewrite rules
		flush_rewrite_rules();
	}

	/**
	 * Deactivate plugin
	 */
	public function deactivate() {
		// Flush rewrite rules
		flush_rewrite_rules();
	}

	/**
	 * Load plugin text domain
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'duck-mail', false, dirname( plugin_basename( DUCK_MAIL_PLUGIN_FILE ) ) . '/languages' );
	}
}

// Initialize the plugin
if ( function_exists( 'add_action' ) ) {
	Duck_Mail::get_instance();
}
