# Development Guide - Simple Mail via PHP mail()

This guide is for developers who want to contribute to or extend the Simple Mail plugin.

## Project Structure

```
smtp-alternative/
├── smtp-alternative.php           # Main plugin file
├── uninstall.php                  # Uninstall hook
├── README.md                       # User documentation
├── INSTALLATION.md                 # Installation guide
├── TROUBLESHOOTING.md              # Troubleshooting guide
├── DEVELOPMENT.md                  # This file
├── LICENSE                         # GPL-2.0+ license
├── package.json                    # Project metadata
├── .gitignore                      # Git ignore rules
├── includes/
│   ├── class-mail-handler.php     # Email sending logic
│   ├── class-settings.php          # Admin settings
│   └── class-woocommerce-integration.php # WooCommerce support
└── languages/
    └── smtp-alternative.pot        # Translation template
```

## Architecture

### Plugin Flow

```
WordPress Admin/WooCommerce
          ↓
    wp_mail() function
          ↓
    SMTP_Alternative
    (main plugin class)
          ↓
    Mail Handler
    (intercepted email)
          ↓
    Settings/Filters
    (apply config)
          ↓
    PHP mail() function
          ↓
    Server sends email
```

### Class Hierarchy

- **SMTP_Alternative** (main plugin)
  - Handles plugin initialization
  - Registers activation/deactivation hooks
  - Initializes mail handler and settings

- **SMTP_Alternative_Mail_Handler** (core)
  - Intercepts `wp_mail()` calls
  - Applies from email/name filters
  - Handles actual email sending via `mail()`
  - Logs email sending activity

- **SMTP_Alternative_Settings** (UI)
  - Creates admin settings page
  - Registers plugin options
  - Provides configuration interface

- **SMTP_Alternative_WooCommerce** (optional)
  - Enhanced WooCommerce integration
  - WooCommerce-specific hooks
  - Compatibility notices

## Development Setup

### Prerequisites

- PHP 7.2 or higher
- WordPress 5.0 or higher
- Composer (optional)
- Git (for version control)
- Code editor (VS Code recommended)

### Local Development

1. **Clone the repository**
   ```bash
   git clone https://github.com/yourusername/smtp-alternative.git
   cd smtp-alternative
   ```

2. **Install in WordPress locally**
   ```bash
   # Place in your local WordPress plugins directory
   cp -r . /path/to/wordpress/wp-content/plugins/smtp-alternative
   ```

3. **Activate in WordPress**
   - Go to WordPress Admin → Plugins
   - Find "Simple Mail via PHP mail()"
   - Click "Activate"

4. **Enable debugging**
   Add to `wp-config.php`:
   ```php
   define( 'WP_DEBUG', true );
   define( 'WP_DEBUG_LOG', true );
   define( 'WP_DEBUG_DISPLAY', false );
   ```

## Code Standards

### WordPress Coding Standards

Follow [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/):

- **Naming**: Use snake_case for functions/variables, CamelCase for classes
- **Spacing**: 1 tab indentation, 2 spaces in some contexts
- **Comments**: Use DocBlocks for functions and classes
- **Security**: Always sanitize, validate, and escape

### Example Function

```php
/**
 * Send email using PHP mail() function
 *
 * @param array  $to The recipient(s) email address(es)
 * @param string $subject The email subject
 * @param string $message The email message
 * @param string $headers The email headers
 * @param array  $attachments The email attachments
 * @return bool True if sent successfully, false otherwise
 */
private function send_email( $to, $subject, $message, $headers = '', $attachments = array() ) {
    // Implementation here
    return $sent;
}
```

### Security Best Practices

1. **Sanitize Input**
   ```php
   $email = sanitize_email( $_POST['email'] );
   ```

2. **Validate Email**
   ```php
   if ( is_email( $email ) ) {
       // Valid email
   }
   ```

3. **Escape Output**
   ```php
   echo esc_html( $value );
   echo esc_attr( $attribute );
   echo wp_kses_post( $html );
   ```

4. **Check Capabilities**
   ```php
   if ( ! current_user_can( 'manage_options' ) ) {
       wp_die( 'Unauthorized' );
   }
   ```

5. **Use Nonces** (when adding forms)
   ```php
   wp_verify_nonce( $_POST['nonce'], 'action_name' );
   ```

## Key Files and Functions

### smtp-alternative.php

Main entry point. Contains:
- Plugin header (must be at top)
- Plugin constants
- Main class initialization
- Activation/deactivation hooks

### includes/class-mail-handler.php

Core functionality:
- `handle_mail()` - Intercepts wp_mail calls
- `send_email()` - Actually sends via mail()
- `prepare_headers()` - Processes email headers
- `prepare_message()` - Formats email message
- `log_email()` - Logs sending activity

### includes/class-settings.php

Admin interface:
- `add_admin_menu()` - Adds settings page
- `register_settings()` - Registers WordPress options
- `settings_page_callback()` - Renders settings page
- `field_text()` - Text input field
- `field_checkbox()` - Checkbox field

## Extending the Plugin

### Adding a New Setting

1. **Add to register_settings()**
   ```php
   register_setting( 'smtp_alt_settings', 'smtp_alt_new_option' );
   add_settings_field( 'smtp_alt_new_option', ... );
   ```

2. **Use in mail handler**
   ```php
   $value = get_option( 'smtp_alt_new_option' );
   ```

### Adding a New Hook

1. **Add action/filter**
   ```php
   add_action( 'hook_name', array( $this, 'callback_function' ) );
   ```

2. **Create callback**
   ```php
   public function callback_function( $param ) {
       // Do something
       return $param;
   }
   ```

### Adding MIME Support

For attachment support, consider adding MIME library:

```php
// Add to composer.json
"require": {
    "phpmailer/phpmailer": "^6.5"
}
```

Then modify `send_email()` to use PHPMailer.

## Testing

### Manual Testing Checklist

- [ ] Plugin activates without errors
- [ ] Settings page loads
- [ ] Settings save correctly
- [ ] Test email sends successfully
- [ ] WooCommerce emails work
- [ ] Emails have correct From name/address
- [ ] Plugin deactivates cleanly
- [ ] Plugin uninstalls cleanly

### Testing Email Sending

1. Use "Test Email" plugin or similar
2. Send test email to your address
3. Verify email arrives in inbox
4. Check email headers and formatting
5. Test with WooCommerce emails

### Testing Settings

1. Change settings values
2. Reload settings page
3. Verify values are saved
4. Test actual email sending with new settings

## Common Tasks

### To Add a New Admin Page

```php
add_action( 'admin_menu', function() {
    add_menu_page(
        'Page Title',
        'Menu Title',
        'manage_options',
        'page-slug',
        'callback_function'
    );
} );
```

### To Add a Settings Field

```php
add_settings_field(
    'field_id',
    'Field Label',
    'field_callback_function',
    'settings_page_slug',
    'settings_section_slug'
);
```

### To Add a Filter

```php
add_filter( 'wp_mail_from', function( $from_email ) {
    return 'new@email.com';
} );
```

### To Log Debug Information

```php
if ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
    error_log( 'Debug message here' );
}
```

## Debugging

### Enable Debug Mode

Add to `wp-config.php`:
```php
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );
define( 'SCRIPT_DEBUG', true );
```

### Check Error Log

```bash
tail -f wp-content/debug.log
```

### Use WordPress Debug Functions

```php
// Log messages
error_log( print_r( $variable, true ) );

// Examine variable
var_dump( $variable );
```

## Performance Considerations

1. **Email Sending is Synchronous**
   - Currently blocking, may slow down requests
   - Consider async processing for high volume

2. **Logging Impact**
   - File operations can be slow
   - Only enable when needed

3. **Database Queries**
   - Currently minimal (just option retrieval)
   - Consider caching for repeated calls

## Compatibility

### WordPress Versions
- Tested: 5.0+
- Minimum: 5.0
- Compatible: 6.0+

### PHP Versions
- Tested: 7.2, 7.4, 8.0, 8.1
- Minimum: 7.2
- Compatible: 8.2+

### Popular Plugins
- ✓ WooCommerce (full support)
- ✓ Contact Form 7 (tested)
- ✓ Gravity Forms (tested)
- ✓ Most email-related plugins

## Contributing

### Before Submitting

1. Follow WordPress Coding Standards
2. Test thoroughly
3. Write clear commit messages
4. Update documentation
5. Test with latest WordPress and PHP

### Git Workflow

```bash
# Create feature branch
git checkout -b feature/feature-name

# Make changes and commit
git commit -m "Clear commit message"

# Push to repository
git push origin feature/feature-name

# Create Pull Request on GitHub
```

## Release Process

1. Update version number in:
   - `smtp-alternative.php` (plugin header)
   - `README.md` (Changelog section)
   - `package.json`

2. Update `CHANGELOG.md`

3. Commit and tag
   ```bash
   git tag -a v1.0.0 -m "Version 1.0.0"
   git push origin v1.0.0
   ```

4. Create GitHub release with notes

## Resources

- [WordPress Plugin Handbook](https://developer.wordpress.org/plugins/)
- [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/)
- [WordPress Security](https://developer.wordpress.org/plugins/security/)
- [WordPress Hooks](https://developer.wordpress.org/plugins/hooks/)
- [WordPress API](https://developer.wordpress.org/reference/)

## License

This plugin is licensed under GPL-2.0+. Any modifications must also be GPL-2.0+ compatible.

## Support

For development questions or issues:
1. Check existing issues on GitHub
2. Create new issue with detailed description
3. Include WordPress version, PHP version, and error logs
