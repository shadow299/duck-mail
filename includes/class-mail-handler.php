<?php
/**
 * Mail Handler Class
 * Handles email sending using PHP's mail() function
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Duck_Mail_Handler {

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
		// Use pre_wp_mail to SHORT-CIRCUIT wp_mail (WP 5.7+)
		// This is the CORRECT way to replace wp_mail entirely.
		add_filter( 'pre_wp_mail', array( $this, 'pre_wp_mail' ), 10, 2 );
	}

	/**
	 * Short-circuit wp_mail and send via PHP mail() function.
	 *
	 * @param null|bool $return Short-circuit return value (null = continue normal wp_mail).
	 * @param array     $atts   wp_mail arguments (to, subject, message, headers, attachments).
	 * @return null|bool True on success, false on failure, null to let wp_mail run normally.
	 */
	public function pre_wp_mail( $return, $atts ) {
		// If plugin is disabled, let WordPress handle it normally
		if ( ! get_option( 'duck_mail_enabled' ) ) {
			return $return;
		}

		// If another plugin already short-circuited, respect that
		if ( null !== $return ) {
			return $return;
		}

		$to          = isset( $atts['to'] ) ? $atts['to'] : '';
		$subject     = isset( $atts['subject'] ) ? $atts['subject'] : '';
		$message     = isset( $atts['message'] ) ? $atts['message'] : '';
		$headers     = isset( $atts['headers'] ) ? $atts['headers'] : '';
		$attachments = isset( $atts['attachments'] ) ? $atts['attachments'] : array();

		return $this->send_email( $to, $subject, $message, $headers, $attachments );
	}

	/**
	 * Send email using PHP mail() function.
	 *
	 * @param string|array $to          Recipient email address(es).
	 * @param string       $subject     Email subject.
	 * @param string       $message     Email body.
	 * @param string|array $headers     Email headers.
	 * @param array        $attachments Attachments (not supported by basic mail()).
	 * @return bool True if at least one email was sent successfully.
	 */
	private function send_email( $to, $subject, $message, $headers = '', $attachments = array() ) {
		// Normalize recipients into a flat array of trimmed email addresses
		$recipients = $this->parse_recipients( $to );
		if ( empty( $recipients ) ) {
			return false;
		}

		// Parse incoming headers and separate cc/bcc/from/content-type
		$parsed = $this->parse_headers( $headers );

		// Determine content type (HTML vs plain)
		$blog_charset = get_option( 'blog_charset' );
		if ( empty( $blog_charset ) ) {
			$blog_charset = 'UTF-8';
		}

		$content_type = ! empty( $parsed['content_type'] ) ? $parsed['content_type'] : 'text/plain';
		$charset      = ! empty( $parsed['charset'] ) ? $parsed['charset'] : $blog_charset;

		// Determine From address & name
		$from_email = ! empty( $parsed['from_email'] ) ? $parsed['from_email'] : get_option( 'duck_mail_from_email', get_option( 'admin_email' ) );
		$from_name  = ! empty( $parsed['from_name'] ) ? $parsed['from_name'] : get_option( 'duck_mail_from_name', get_bloginfo( 'name' ) );

		// Apply WordPress filters so other plugins still work
		$from_email   = apply_filters( 'wp_mail_from', $from_email );
		$from_name    = apply_filters( 'wp_mail_from_name', $from_name );
		$content_type = apply_filters( 'wp_mail_content_type', $content_type );
		$charset      = apply_filters( 'wp_mail_charset', $charset );

		// Normalize message line endings (mail() prefers \n on Linux/Hostinger)
		$message = str_replace( array( "\r\n", "\r" ), "\n", $message );

		$is_html = ( stripos( $content_type, 'html' ) !== false );

		// Build the message body. For HTML emails, wrap in multipart/alternative
		// so mail clients also get a plain-text version. Gmail heavily favors this
		// for deliverability (HTML-only OTP emails are commonly filtered as spam).
		if ( $is_html ) {
			$boundary          = 'duckmail-boundary-' . bin2hex( random_bytes( 8 ) );
			$plain_version     = $this->html_to_plain( $message );
			$body              = $this->build_multipart_body( $plain_version, $message, $charset, $boundary );
			$body_content_type = 'multipart/alternative; boundary="' . $boundary . '"';
		} else {
			$body              = $message;
			$body_content_type = $content_type . '; charset=' . $charset;
		}

		// Build final headers - keep it MINIMAL like the working test-gmail.php
		$final_headers  = "MIME-Version: 1.0\r\n";
		$final_headers .= 'Date: ' . date( 'r' ) . "\r\n";
		$final_headers .= 'Message-ID: <' . $this->generate_message_id( $from_email ) . ">\r\n";
		$final_headers .= 'Content-Type: ' . $body_content_type . "\r\n";

		if ( ! empty( $from_name ) ) {
			$final_headers .= 'From: ' . $this->encode_header_name( $from_name ) . ' <' . $from_email . ">\r\n";
		} else {
			$final_headers .= 'From: ' . $from_email . "\r\n";
		}

		$reply_to = ! empty( $parsed['reply_to'] ) ? $parsed['reply_to'] : $from_email;
		$final_headers .= 'Reply-To: ' . $reply_to . "\r\n";
		$final_headers .= 'Return-Path: <' . $from_email . ">\r\n";

		if ( ! empty( $parsed['cc'] ) ) {
			$final_headers .= 'Cc: ' . implode( ', ', $parsed['cc'] ) . "\r\n";
		}
		if ( ! empty( $parsed['bcc'] ) ) {
			$final_headers .= 'Bcc: ' . implode( ', ', $parsed['bcc'] ) . "\r\n";
		}

		// Preserve any other custom headers (X-*, etc.)
		if ( ! empty( $parsed['extra'] ) ) {
			foreach ( $parsed['extra'] as $extra_header ) {
				$final_headers .= $extra_header . "\r\n";
			}
		}

		$final_headers .= 'X-Mailer: Duck Mail ' . ( defined( 'DUCK_MAIL_VERSION' ) ? DUCK_MAIL_VERSION : '1.0.0' ) . "\r\n";

		// Encode subject line for UTF-8 support
		$encoded_subject = $this->encode_subject( $subject, $charset );

		// Envelope sender (-f): tells mail server the real sender for SPF/bounce handling.
		// Without this, Hostinger uses the Unix user (e.g. u123@srv1) which fails SPF and
		// causes Gmail/Outlook to silently drop the message. This is the #1 fix for
		// "mail() returns true but email never arrives".
		$additional_params = '-f' . $from_email;

		$sent_count = 0;
		foreach ( $recipients as $recipient ) {
			if ( ! is_email( $recipient ) ) {
				$this->log_email( $recipient, $subject, 'invalid-email' );
				continue;
			}

			$result = @mail( $recipient, $encoded_subject, $body, trim( $final_headers ), $additional_params );

			// Some hosts block -f. Retry once without it if the first attempt failed.
			if ( ! $result ) {
				$result = @mail( $recipient, $encoded_subject, $body, trim( $final_headers ) );
			}

			if ( $result ) {
				$sent_count++;
				$this->log_email( $recipient, $subject, 'success' );
			} else {
				$last_error = error_get_last();
				$err_msg    = $last_error && isset( $last_error['message'] ) ? $last_error['message'] : 'unknown error';
				$this->log_email( $recipient, $subject, 'failed: ' . $err_msg );
			}
		}

		if ( ! empty( $attachments ) && is_array( $attachments ) ) {
			$this->log_email( is_array( $to ) ? implode( ',', $to ) : $to, $subject, 'notice: attachments not sent (mail() basic mode)' );
		}

		return $sent_count > 0;
	}

	/**
	 * Parse recipient(s) into a flat array of email addresses.
	 *
	 * @param string|array $to Recipients.
	 * @return array
	 */
	private function parse_recipients( $to ) {
		if ( ! is_array( $to ) ) {
			$to = explode( ',', $to );
		}

		$recipients = array();
		foreach ( $to as $address ) {
			$address = trim( $address );
			if ( empty( $address ) ) {
				continue;
			}

			// Handle "Name <email@example.com>" format
			if ( preg_match( '/<([^>]+)>/', $address, $matches ) ) {
				$address = $matches[1];
			}

			$recipients[] = $address;
		}

		return $recipients;
	}

	/**
	 * Parse headers (string or array) into structured data.
	 *
	 * @param string|array $headers Raw headers.
	 * @return array
	 */
	private function parse_headers( $headers ) {
		$result = array(
			'from_email'   => '',
			'from_name'    => '',
			'reply_to'     => '',
			'content_type' => '',
			'charset'      => '',
			'cc'           => array(),
			'bcc'          => array(),
			'extra'        => array(),
		);

		if ( empty( $headers ) ) {
			return $result;
		}

		if ( ! is_array( $headers ) ) {
			// Remove BOM if present
			if ( substr( $headers, 0, 3 ) === "\xEF\xBB\xBF" ) {
				$headers = substr( $headers, 3 );
			}
			$headers = preg_split( '/\r\n|\r|\n/', $headers );
		}

		foreach ( $headers as $header ) {
			if ( empty( $header ) || strpos( $header, ':' ) === false ) {
				continue;
			}

			list( $name, $value ) = explode( ':', $header, 2 );
			$name  = trim( $name );
			$value = trim( $value );

			switch ( strtolower( $name ) ) {
				case 'from':
					if ( preg_match( '/(.*)<(.+)>/', $value, $matches ) ) {
						$result['from_name']  = trim( trim( $matches[1] ), '"' );
						$result['from_email'] = trim( $matches[2] );
					} else {
						$result['from_email'] = $value;
					}
					break;

				case 'reply-to':
					if ( preg_match( '/<(.+)>/', $value, $matches ) ) {
						$result['reply_to'] = trim( $matches[1] );
					} else {
						$result['reply_to'] = $value;
					}
					break;

				case 'content-type':
					// Content-Type: text/html; charset=UTF-8
					if ( strpos( $value, ';' ) !== false ) {
						list( $type, $charset_part ) = explode( ';', $value, 2 );
						$result['content_type'] = trim( $type );
						if ( preg_match( '/charset\s*=\s*["\']?([^"\';\s]+)["\']?/i', $charset_part, $matches ) ) {
							$result['charset'] = $matches[1];
						}
					} else {
						$result['content_type'] = trim( $value );
					}
					break;

				case 'cc':
					foreach ( explode( ',', $value ) as $addr ) {
						$addr = trim( $addr );
						if ( ! empty( $addr ) ) {
							$result['cc'][] = $addr;
						}
					}
					break;

				case 'bcc':
					foreach ( explode( ',', $value ) as $addr ) {
						$addr = trim( $addr );
						if ( ! empty( $addr ) ) {
							$result['bcc'][] = $addr;
						}
					}
					break;

				case 'mime-version':
				case 'content-transfer-encoding':
				case 'x-mailer':
					// Skip - we set these ourselves
					break;

				default:
					// Preserve any other custom headers (e.g. X-* headers)
					$result['extra'][] = $name . ': ' . $value;
					break;
			}
		}

		return $result;
	}

	/**
	 * Encode subject line for UTF-8 support.
	 *
	 * @param string $subject Subject.
	 * @param string $charset Charset.
	 * @return string
	 */
	private function encode_subject( $subject, $charset = 'UTF-8' ) {
		// Only encode if subject contains non-ASCII characters
		if ( preg_match( '/[^\x20-\x7e]/', $subject ) ) {
			return '=?' . $charset . '?B?' . base64_encode( $subject ) . '?=';
		}
		return $subject;
	}

	/**
	 * Generate a unique Message-ID header value.
	 * Format: <unique@domain-part-of-from>.
	 *
	 * @param string $from_email From email.
	 * @return string
	 */
	private function generate_message_id( $from_email ) {
		$domain = 'localhost';
		if ( ! empty( $from_email ) && strpos( $from_email, '@' ) !== false ) {
			$domain = substr( $from_email, strpos( $from_email, '@' ) + 1 );
		}
		return sprintf( '%s.%s@%s', time(), bin2hex( random_bytes( 8 ) ), $domain );
	}

	/**
	 * Convert an HTML message into a reasonable plain-text version.
	 * Used to build multipart/alternative bodies for better deliverability.
	 *
	 * @param string $html HTML content.
	 * @return string
	 */
	private function html_to_plain( $html ) {
		// Convert <a href="URL">text</a> to "text (URL)"
		$text = preg_replace( '/<a[^>]*href=["\']([^"\']+)["\'][^>]*>(.*?)<\/a>/is', '$2 ($1)', $html );

		// Line-break tags become newlines
		$text = preg_replace( '/<(br|br\s*\/)>/i', "\n", $text );
		$text = preg_replace( '/<\/(p|div|h[1-6]|li|tr)>/i', "\n", $text );
		$text = preg_replace( '/<li[^>]*>/i', '* ', $text );

		// Strip remaining tags
		$text = wp_strip_all_tags( $text );

		// Decode HTML entities
		$text = html_entity_decode( $text, ENT_QUOTES | ENT_HTML5, 'UTF-8' );

		// Collapse whitespace
		$text = preg_replace( "/[ \t]+/", ' ', $text );
		$text = preg_replace( "/\n{3,}/", "\n\n", $text );
		$text = trim( $text );

		return $text;
	}

	/**
	 * Build a multipart/alternative message body containing both plain-text and HTML parts.
	 *
	 * @param string $plain    Plain-text version.
	 * @param string $html     HTML version.
	 * @param string $charset  Charset (e.g. UTF-8).
	 * @param string $boundary Boundary string.
	 * @return string
	 */
	private function build_multipart_body( $plain, $html, $charset, $boundary ) {
		$eol  = "\r\n";
		$body = 'This is a multi-part message in MIME format.' . $eol . $eol;

		$body .= '--' . $boundary . $eol;
		$body .= 'Content-Type: text/plain; charset=' . $charset . $eol;
		$body .= 'Content-Transfer-Encoding: 8bit' . $eol . $eol;
		$body .= $plain . $eol . $eol;

		$body .= '--' . $boundary . $eol;
		$body .= 'Content-Type: text/html; charset=' . $charset . $eol;
		$body .= 'Content-Transfer-Encoding: 8bit' . $eol . $eol;
		$body .= $html . $eol . $eol;

		$body .= '--' . $boundary . '--' . $eol;

		return $body;
	}

	/**
	 * Encode a display name for use in From/To headers if it contains non-ASCII.
	 *
	 * @param string $name Display name.
	 * @return string
	 */
	private function encode_header_name( $name ) {
		if ( preg_match( '/[^\x20-\x7e]/', $name ) ) {
			return '=?UTF-8?B?' . base64_encode( $name ) . '?=';
		}
		// Quote if it contains special chars like commas or dots after a space
		if ( preg_match( '/[,;<>@()\[\]]/', $name ) ) {
			return '"' . str_replace( '"', '\\"', $name ) . '"';
		}
		return $name;
	}

	/**
	 * Log email sending.
	 *
	 * @param string $recipient The recipient email.
	 * @param string $subject   The email subject.
	 * @param string $status    The send status.
	 */
	private function log_email( $recipient, $subject, $status ) {
		$log_dir = DUCK_MAIL_PLUGIN_DIR . 'logs';
		if ( ! is_dir( $log_dir ) ) {
			@wp_mkdir_p( $log_dir );
			// Add index.html to prevent directory listing
			@file_put_contents( $log_dir . '/index.html', '' );
		}

		if ( is_writable( $log_dir ) ) {
			$log_file  = $log_dir . '/email.log';
			$timestamp = current_time( 'mysql' );
			$log_entry = sprintf( "[%s] %s | To: %s | Subject: %s\n", $timestamp, strtoupper( $status ), $recipient, $subject );

			@file_put_contents( $log_file, $log_entry, FILE_APPEND );
		}

		if ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
			error_log( "Duck Mail [{$status}] To: {$recipient} | Subject: {$subject}" );
		}
	}
}
