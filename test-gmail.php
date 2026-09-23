<?php
/**
 * Direct Mail Test - Send to Gmail
 * Upload to: /public_html/test-gmail.php
 * Access: https://utsavlibaas.com/test-gmail.php
 * 
 * DELETE AFTER TESTING!
 */

$to = 'chandramanish900@gmail.com';
$subject = 'Direct Test Email - ' . date('Y-m-d H:i:s');
$from_email = 'info@utsavlibaas.com';
$from_name = 'Utsav Libaas';

$message = "Hi,\n\n";
$message .= "This is a direct test email sent via PHP mail() function.\n\n";
$message .= "Timestamp: " . date('Y-m-d H:i:s') . "\n";
$message .= "From: " . $from_email . "\n";
$message .= "To: " . $to . "\n";
$message .= "Subject: " . $subject . "\n\n";
$message .= "If you receive this email, the mail() function is working correctly!\n\n";
$message .= "---\n";
$message .= "Sent from: " . $_SERVER['HTTP_HOST'] . "\n";
$message .= "Server IP: " . $_SERVER['SERVER_ADDR'] . "\n";

// Prepare headers
$headers = "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
$headers .= "From: " . $from_name . " <" . $from_email . ">\r\n";
$headers .= "Reply-To: " . $from_email . "\r\n";
$headers .= "X-Mailer: PHP-Mail-Test\r\n";
$headers .= "X-Priority: 3\r\n";

// Send email
$result = @mail($to, $subject, $message, $headers);

?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Direct Gmail Test</title>
	<style>
		body {
			font-family: Arial, sans-serif;
			background: #f5f5f5;
			padding: 20px;
		}
		.container {
			max-width: 600px;
			margin: 0 auto;
			background: white;
			padding: 30px;
			border-radius: 8px;
			box-shadow: 0 2px 10px rgba(0,0,0,0.1);
		}
		h1 {
			color: #333;
		}
		.result {
			padding: 20px;
			border-radius: 5px;
			margin: 20px 0;
			font-weight: bold;
		}
		.success {
			background: #d4edda;
			border: 1px solid #c3e6cb;
			color: #155724;
		}
		.failed {
			background: #f8d7da;
			border: 1px solid #f5c6cb;
			color: #721c24;
		}
		.details {
			background: #f9f9f9;
			padding: 15px;
			border-left: 4px solid #667eea;
			margin: 20px 0;
			font-family: monospace;
			font-size: 12px;
		}
		.warning {
			background: #fff3cd;
			border: 1px solid #ffc107;
			color: #856404;
			padding: 15px;
			border-radius: 5px;
			margin-bottom: 20px;
		}
	</style>
</head>
<body>
	<div class="container">
		<h1>📧 Direct Gmail Test</h1>

		<div class="warning">
			<strong>⚠️ Important:</strong> This sends a test email directly to chandramanish900@gmail.com<br>
			Delete this file (test-gmail.php) after you're done testing!
		</div>

		<?php if ($result): ?>
			<div class="result success">
				✓ SUCCESS: mail() returned TRUE<br>
				<small>Email submitted to server. Check Gmail inbox/spam in 5 minutes.</small>
			</div>
		<?php else: ?>
			<div class="result failed">
				✗ FAILED: mail() returned FALSE<br>
				<small>Server couldn't send the email. Contact Hostinger support.</small>
			</div>
		<?php endif; ?>

		<div class="details">
			<strong>📨 Email Details:</strong><br>
			To: <?php echo $to; ?><br>
			From: <?php echo $from_name; ?> &lt;<?php echo $from_email; ?>&gt;<br>
			Subject: <?php echo $subject; ?><br>
			Timestamp: <?php echo date('Y-m-d H:i:s'); ?><br>
			<br>
			<strong>🖥️ Server Info:</strong><br>
			Domain: <?php echo $_SERVER['HTTP_HOST']; ?><br>
			IP: <?php echo $_SERVER['SERVER_ADDR']; ?><br>
			PHP: <?php echo phpversion(); ?><br>
			mail() exists: <?php echo function_exists('mail') ? 'YES' : 'NO'; ?><br>
		</div>

		<div style="background: #e7f3ff; padding: 15px; border-radius: 5px; margin-top: 20px;">
			<strong>Next Steps:</strong>
			<ol>
				<li>Open Gmail and check inbox for an email from info@utsavlibaas.com</li>
				<li>Also check Gmail SPAM folder</li>
				<li>Wait 5-10 minutes if not there yet</li>
				<li>If received → mail() function works fine</li>
				<li>If NOT received → Contact Hostinger to check mail server logs</li>
				<li><strong>Delete test-gmail.php after testing</strong></li>
			</ol>
		</div>
	</div>
</body>
</html>
