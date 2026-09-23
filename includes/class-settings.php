<?php
/**
 * Settings Class
 * Handles plugin settings and admin interface
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Duck_Mail_Settings {

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
		// Add settings page
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );

		// Register settings
		add_action( 'admin_init', array( $this, 'register_settings' ) );

		// Handle test email submission
		add_action( 'admin_post_duck_mail_send_test', array( $this, 'handle_test_email' ) );

		// Add plugin action links
		add_filter( 'plugin_action_links_' . plugin_basename( DUCK_MAIL_PLUGIN_FILE ), array( $this, 'add_action_links' ) );
	}

	/**
	 * Handle the "Send Test Email" form submission.
	 * Uses wp_mail() so it exercises the exact same code path as WooCommerce.
	 */
	public function handle_test_email() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized', 'duck-mail' ) );
		}
		check_admin_referer( 'duck_mail_test_email' );

		$to = isset( $_POST['duck_mail_test_to'] ) ? sanitize_email( wp_unslash( $_POST['duck_mail_test_to'] ) ) : '';

		if ( ! is_email( $to ) ) {
			$redirect = add_query_arg( array( 'page' => 'duck-mail', 'duck_mail_test' => 'invalid' ), admin_url( 'options-general.php' ) );
			wp_safe_redirect( $redirect );
			exit;
		}

		$subject = 'Duck Mail Test - ' . gmdate( 'Y-m-d H:i:s' );
		$message = "Hello,\n\nThis is a test email from Duck Mail plugin.\n\n"
				 . "If you received this, wp_mail() is working correctly.\n\n"
				 . "Sent at: " . gmdate( 'Y-m-d H:i:s' ) . " UTC\n"
				 . "Site: " . home_url() . "\n";

		$sent = wp_mail( $to, $subject, $message );

		$status = $sent ? 'sent' : 'failed';
		$redirect = add_query_arg( array( 'page' => 'duck-mail', 'duck_mail_test' => $status, 'duck_mail_to' => rawurlencode( $to ) ), admin_url( 'options-general.php' ) );
		wp_safe_redirect( $redirect );
		exit;
	}

	/**
	 * Add admin menu
	 */
	public function add_admin_menu() {
		add_options_page(
			__( 'Duck Mail Settings', 'duck-mail' ),
			__( 'Duck Mail', 'duck-mail' ),
			'manage_options',
			'duck-mail',
			array( $this, 'settings_page_callback' )
		);
	}

	/**
	 * Register settings
	 */
	public function register_settings() {
		// Register settings
		register_setting( 'duck_mail_settings', 'duck_mail_enabled' );
		register_setting( 'duck_mail_settings', 'duck_mail_from_email' );
		register_setting( 'duck_mail_settings', 'duck_mail_from_name' );

		// Add settings section
		add_settings_section(
			'duck_mail_main_section',
			__( 'Email Configuration', 'duck-mail' ),
			array( $this, 'settings_section_callback' ),
			'duck_mail_settings'
		);

		// Add settings fields
		add_settings_field(
			'duck_mail_enabled',
			__( 'Enable Plugin', 'duck-mail' ),
			array( $this, 'field_checkbox' ),
			'duck_mail_settings',
			'duck_mail_main_section',
			array( 'label_for' => 'duck_mail_enabled', 'name' => 'duck_mail_enabled' )
		);

		add_settings_field(
			'duck_mail_from_email',
			__( 'From Email Address', 'duck-mail' ),
			array( $this, 'field_text' ),
			'duck_mail_settings',
			'duck_mail_main_section',
			array( 'label_for' => 'duck_mail_from_email', 'name' => 'duck_mail_from_email', 'type' => 'email' )
		);

		add_settings_field(
			'duck_mail_from_name',
			__( 'From Name', 'duck-mail' ),
			array( $this, 'field_text' ),
			'duck_mail_settings',
			'duck_mail_main_section',
			array( 'label_for' => 'duck_mail_from_name', 'name' => 'duck_mail_from_name', 'type' => 'text' )
		);
	}

	/**
	 * Settings section callback
	 */
	public function settings_section_callback() {
		echo '<p>' . esc_html__( 'Configure how emails are sent using PHP mail() function.', 'duck-mail' ) . '</p>';
	}

	/**
	 * Text field callback
	 *
	 * @param array $args Field arguments
	 */
	public function field_text( $args ) {
		$name = $args['name'];
		$type = isset( $args['type'] ) ? $args['type'] : 'text';
		$value = get_option( $name );
		?>
		<input type="<?php echo esc_attr( $type ); ?>" 
		       id="<?php echo esc_attr( $name ); ?>" 
		       name="<?php echo esc_attr( $name ); ?>" 
		       value="<?php echo esc_attr( $value ); ?>" 
		       class="regular-text" />
		<?php
	}

	/**
	 * Checkbox field callback
	 *
	 * @param array $args Field arguments
	 */
	public function field_checkbox( $args ) {
		$name = $args['name'];
		$value = get_option( $name );
		?>
		<input type="checkbox" 
		       id="<?php echo esc_attr( $name ); ?>" 
		       name="<?php echo esc_attr( $name ); ?>" 
		       value="1" 
		       <?php checked( $value, 1 ); ?> />
		<label for="<?php echo esc_attr( $name ); ?>">
			<?php esc_html_e( 'Enable this plugin to handle WordPress email sending', 'duck-mail' ); ?>
		</label>
		<?php
	}

	/**
	 * Settings page callback
	 */
	public function settings_page_callback() {
		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'duck-mail' ) );
		}
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Duck Mail Settings', 'duck-mail' ); ?></h1>
			
			<div style="margin: 20px 0; padding: 20px; background-color: #f0f6fc; border: 1px solid #90caf9; border-radius: 5px;">
				<h2><?php esc_html_e( 'Status', 'duck-mail' ); ?></h2>
				<p>
					<?php
					$enabled = get_option( 'duck_mail_enabled' );
					if ( $enabled ) {
						echo '<span style="color: green; font-weight: bold;">✓ ' . esc_html__( 'Plugin is ACTIVE', 'duck-mail' ) . '</span>';
					} else {
						echo '<span style="color: red; font-weight: bold;">✗ ' . esc_html__( 'Plugin is INACTIVE', 'duck-mail' ) . '</span>';
					}
					?>
				</p>
				<p>
					<strong><?php esc_html_e( 'Mail Function:', 'duck-mail' ); ?></strong>
					<?php
					if ( function_exists( 'mail' ) ) {
						echo '<span style="color: green;">✓ ' . esc_html__( 'PHP mail() function available', 'duck-mail' ) . '</span>';
					} else {
						echo '<span style="color: red;">✗ ' . esc_html__( 'PHP mail() function NOT available', 'duck-mail' ) . '</span>';
					}
					?>
				</p>
			</div>

			<div style="margin: 20px 0; padding: 20px; background-color: #fff3cd; border: 1px solid #ffc107; border-radius: 5px;">
				<h3><?php esc_html_e( 'Important Notes', 'duck-mail' ); ?></h3>
				<ul style="margin-left: 20px;">
					<li><?php esc_html_e( 'This plugin uses PHP mail() function to send emails without any sending limits.', 'duck-mail' ); ?></li>
					<li><?php esc_html_e( 'Works perfectly for WooCommerce OTP emails and all WordPress email notifications.', 'duck-mail' ); ?></li>
					<li><?php esc_html_e( 'Make sure your server supports mail() function (most hosting providers do).', 'duck-mail' ); ?></li>
					<li><?php esc_html_e( 'Check spam folder if emails are not appearing in inbox.', 'duck-mail' ); ?></li>
					<li><?php esc_html_e( 'The "From" email address should match your server configuration for best delivery.', 'duck-mail' ); ?></li>
				</ul>
			</div>

			<form action="options.php" method="post">
				<?php
				settings_fields( 'duck_mail_settings' );
				do_settings_sections( 'duck_mail_settings' );
				submit_button();
				?>
			</form>

			<?php
			// Show result banner from test email submission
			if ( isset( $_GET['duck_mail_test'] ) ) {
				$result = sanitize_key( wp_unslash( $_GET['duck_mail_test'] ) );
				$to     = isset( $_GET['duck_mail_to'] ) ? sanitize_email( wp_unslash( $_GET['duck_mail_to'] ) ) : '';
				if ( $result === 'sent' ) {
					echo '<div class="notice notice-success"><p><strong>✓ ' . esc_html__( 'Test email sent successfully via wp_mail(). Check inbox and spam folder at:', 'duck-mail' ) . '</strong> ' . esc_html( $to ) . '</p></div>';
				} elseif ( $result === 'failed' ) {
					echo '<div class="notice notice-error"><p><strong>✗ ' . esc_html__( 'Test email FAILED. wp_mail() returned false. Check email.log.', 'duck-mail' ) . '</strong></p></div>';
				} elseif ( $result === 'invalid' ) {
					echo '<div class="notice notice-error"><p><strong>' . esc_html__( 'Invalid recipient email address.', 'duck-mail' ) . '</strong></p></div>';
				}
			}
			?>

			<div style="margin: 20px 0; padding: 20px; background-color: #e3f2fd; border: 1px solid #64b5f6; border-radius: 5px;">
				<h3><?php esc_html_e( 'Send Test Email', 'duck-mail' ); ?></h3>
				<p><?php esc_html_e( 'Send a test email using wp_mail() — the same function WooCommerce uses. This verifies the full plugin code path.', 'duck-mail' ); ?></p>
				<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
					<?php wp_nonce_field( 'duck_mail_test_email' ); ?>
					<input type="hidden" name="action" value="duck_mail_send_test">
					<label for="duck_mail_test_to" style="font-weight: 600;"><?php esc_html_e( 'Send to:', 'duck-mail' ); ?></label>
					<input type="email" id="duck_mail_test_to" name="duck_mail_test_to" required
						   placeholder="you@example.com"
						   value="<?php echo esc_attr( wp_get_current_user()->user_email ); ?>"
						   class="regular-text" />
					<button type="submit" class="button button-primary"><?php esc_html_e( 'Send Test Email', 'duck-mail' ); ?></button>
				</form>
			</div>

			<div style="margin: 20px 0; padding: 20px; background-color: #e8f5e9; border: 1px solid #81c784; border-radius: 5px;">
				<h3><?php esc_html_e( 'Recent Email Log', 'duck-mail' ); ?></h3>
				<?php
				$log_file = DUCK_MAIL_PLUGIN_DIR . 'logs/email.log';
				if ( file_exists( $log_file ) && is_readable( $log_file ) ) {
					$lines = @file( $log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES );
					if ( is_array( $lines ) && ! empty( $lines ) ) {
						$recent = array_slice( $lines, -15 );
						$recent = array_reverse( $recent );
						echo '<pre style="background: #fff; padding: 10px; border: 1px solid #ddd; border-radius: 4px; max-height: 300px; overflow-y: auto; font-size: 12px;">';
						foreach ( $recent as $line ) {
							echo esc_html( $line ) . "\n";
						}
						echo '</pre>';
					} else {
						echo '<p><em>' . esc_html__( 'No emails logged yet. Send a test email above.', 'duck-mail' ) . '</em></p>';
					}
				} else {
					echo '<p><em>' . esc_html__( 'Log file not created yet. Send a test email above.', 'duck-mail' ) . '</em></p>';
				}
				?>
			</div>
		</div>
		<?php
	}

	/**
	 * Add action links to plugin page
	 *
	 * @param array $links Existing links
	 * @return array
	 */
	public function add_action_links( $links ) {
		$settings_link = sprintf(
			'<a href="%s">%s</a>',
			admin_url( 'options-general.php?page=duck-mail' ),
			__( 'Settings', 'duck-mail' )
		);

		array_unshift( $links, $settings_link );

		return $links;
	}
}
