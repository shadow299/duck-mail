# Duck Mail via PHP mail() - WordPress Plugin

A lightweight WordPress plugin that replaces WP Mail SMTP by using PHP's native `mail()` function to send emails without any sending limits.

## Features

✅ **No Sending Limits** - Uses PHP mail() function directly, no premium restrictions
✅ **WooCommerce Compatible** - Perfect for sending OTP verification emails
✅ **Easy Setup** - Works out of the box with minimal configuration
✅ **Hostinger Compatible** - Tested and working on Hostinger servers
✅ **Simple Settings** - Configure sender email and name from WordPress admin
✅ **Plugin Replacement** - Completely replaces WP Mail SMTP functionality
✅ **No Dependencies** - No external libraries or complex setup required

## Requirements

- WordPress 5.0 or higher
- PHP 7.2 or higher
- PHP `mail()` function enabled (standard on most hosting providers)

## Installation

### Method 1: Manual Installation

1. Download the plugin files
2. Extract the files to `/wp-content/plugins/smtp-wp-plugin/`
3. Go to WordPress Admin Dashboard
4. Navigate to **Plugins**
5. Find "Duck Mail via PHP mail()" and click **Activate**
6. Go to **Settings > Duck Mail** to configure

### Method 2: Upload from Dashboard

1. Go to WordPress Admin Dashboard → **Plugins → Add New**
2. Click **Upload Plugin**
3. Choose the plugin ZIP file
4. Click **Install Now**
5. Click **Activate Plugin**

## Configuration

1. Go to **WordPress Admin Dashboard**
2. Navigate to **Settings → Duck Mail**
3. Enable the plugin (if not already enabled)
4. Set your **From Email Address** (should match your server configuration)
5. Set your **From Name** (your website or business name)
6. Click **Save Changes**

### Recommended Settings for Hostinger

- **Enable Plugin**: ✓ Checked
- **From Email Address**: Use your main domain email (e.g., `noreply@yourdomain.com`)
- **From Name**: Your business or website name

## How It Works

This plugin hooks into WordPress's `wp_mail()` function and intercepts all email sending requests. Instead of using SMTP (which has sending limits in free versions), it uses PHP's built-in `mail()` function:

1. User action triggers email (OTP verification, password reset, etc.)
2. WordPress calls `wp_mail()` 
3. Our plugin intercepts it
4. Email is sent via `mail()` function
5. No limits, no restrictions!

## Testing

### Test Email Sending

1. Install a test email plugin: **Plugins → Add New** → Search "Test Email"
2. Use the test plugin to send a test email
3. Check your inbox (and spam folder)
4. Verify the email arrives

### Enable Debug Logging

Add this to `wp-config.php` to log email sending:

```php
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );
```

Logs will be saved in `/wp-content/plugins/smtp-wp-plugin/logs/email.log`

## WooCommerce OTP Integration

This plugin automatically integrates with WooCommerce:

- **Order Notifications** - Sent to customers
- **Admin Notifications** - Sent to store admin
- **OTP Emails** - Sent for email verification
- **Password Reset** - Sent for account recovery
- **Status Updates** - Sent for order status changes

No additional configuration needed!

## Troubleshooting

### Emails Not Sending

1. **Check if plugin is enabled** - Go to Settings → Duck Mail
2. **Verify mail() function** - Settings page shows if mail() is available
3. **Check sender email** - Should match your server configuration
4. **Review server logs** - Ask Hostinger support to check server error logs

### Emails Going to Spam

1. Make sure "From Email" matches your domain
2. Use proper domain email (not generic like gmail.com)
3. Add proper headers in email content
4. Check DKIM, SPF, DMARC records in domain settings

### Permission Denied Errors

1. Ensure `/wp-content/plugins/smtp-wp-plugin/logs/` directory exists
2. Check WordPress directory permissions (755 for directories, 644 for files)
3. Contact Hostinger support if permissions are restricted

## FAQ

**Q: Will this work on Hostinger?**
A: Yes! Hostinger servers have mail() function enabled by default. This plugin is optimized for Hostinger.

**Q: Can I use custom SMTP settings?**
A: No, this plugin specifically uses PHP mail() function. For custom SMTP, you need a different plugin.

**Q: Will it replace WP Mail SMTP completely?**
A: Yes! Just deactivate WP Mail SMTP and activate this plugin. No conflicts.

**Q: Is there a sending limit?**
A: No! PHP mail() has no built-in limits. Hostinger's server policies apply.

**Q: Will it work with WooCommerce?**
A: Yes! Perfect for WooCommerce OTP emails and all notifications.

**Q: Can I attach files to emails?**
A: The basic version supports text/HTML emails. For attachments, you need to modify the code.

## Support

For issues or questions:
1. Check the Troubleshooting section above
2. Contact Hostinger support - mention you're using PHP mail() function
3. Check WordPress error logs in `wp-content/debug.log`

## License

This plugin is licensed under GPL-2.0+. See LICENSE file for details.

## Changelog

### Version 1.0.0
- Initial release
- Support for PHP mail() function
- WooCommerce integration
- Admin settings page
- Email logging support

## Credits

Created for WordPress and WooCommerce users on Hostinger who need reliable email sending without SMTP limits.

---

**Note:** If your Hostinger account has mail() disabled (rare), contact Hostinger support to enable it. Most accounts have it enabled by default.
