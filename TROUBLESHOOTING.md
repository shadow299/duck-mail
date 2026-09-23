# Troubleshooting Guide - Simple Mail via PHP mail()

This guide helps resolve common issues with the Simple Mail plugin on Hostinger.

## Email Not Sending

### Diagnosis

1. Go to **Settings** → **Simple Mail**
2. Check the Status section:
   - Is "Plugin is ACTIVE" showing?
   - Is "PHP mail() function available" showing green?

### Issue #1: Plugin Shows as INACTIVE

**Symptom:** Red "✗ Plugin is INACTIVE" message

**Solutions:**
1. Check the checkbox for "Enable Plugin"
2. Click "Save Changes"
3. Reload the page

**If still inactive:**
- Deactivate and reactivate the plugin
- Check WordPress error logs at `/wp-content/debug.log`

---

### Issue #2: mail() Function Not Available

**Symptom:** Red "✗ PHP mail() function NOT available" message

**Solutions:**
1. This is extremely rare on Hostinger
2. Contact Hostinger support:
   - Say: "PHP mail() function is disabled on my account"
   - Ask them to enable it
   - Provide your domain name
3. Wait for their response (usually 5-15 minutes)

**Alternative:** If Hostinger can't enable it, ask them about SMTP alternatives.

---

### Issue #3: Tests Show Everything OK But Emails Don't Send

**Steps to troubleshoot:**

1. **Verify Email Format**
   - Make sure "From Email Address" is valid (e.g., `noreply@yourdomain.com`)
   - Don't use random or non-existent email addresses

2. **Check WordPress Logs**
   - Add this to `wp-config.php`:
     ```php
     define( 'WP_DEBUG_LOG', true );
     define( 'WP_DEBUG_DISPLAY', false );
     ```
   - Check `/wp-content/debug.log` for errors
   - Look for lines mentioning "mail" or "SMTP"

3. **Test with Simple Email**
   - Install "Test Email" plugin from WordPress.org
   - Use it to send a test email
   - Check if it arrives

4. **Check Hostinger Server Logs**
   - Log in to Hostinger control panel
   - Go to **Tools** → **Logs** (if available)
   - Look for mail-related errors
   - Contact Hostinger support with the error log excerpt

---

## Emails Going to Spam/Junk

### Most Common Causes

1. **"From" email doesn't match domain**
   - ✗ Bad: `support@gmail.com` sending from `yourdomain.com`
   - ✓ Good: `noreply@yourdomain.com` sending from `yourdomain.com`

2. **Email has no proper headers**
   - Make sure subject line is clear
   - Message should be properly formatted
   - Use text/HTML format

3. **Domain reputation issues**
   - Your domain is new (less than 3 months old)
   - Your domain has low/no sending history
   - Previous spam complaints from this domain

### Solutions

**Solution 1: Fix "From" Email (Most Important)**

1. Go to **Settings** → **Simple Mail**
2. Change "From Email Address" to: `noreply@yourdomain.com`
3. Make sure this email exists on your domain:
   - Go to Hostinger → **Email Accounts**
   - Create this email if it doesn't exist
4. Save and resend test email

**Solution 2: Improve Domain Reputation**

1. In Hostinger control panel, find your domain settings
2. Check/add these DNS records:
   - **SPF Record**: `v=spf1 include:mail.hostinger.com ~all`
   - **DKIM**: Enable in Hostinger control panel
   - **DMARC**: Optional but helpful

3. How to add these:
   - Go to Hostinger → **Domains** → **Your Domain** → **DNS**
   - Add the records there
   - Wait 24-48 hours for propagation

**Solution 3: Use HTML Email Format**

Make sure your emails use HTML format:
- Better formatting
- Better deliverability
- Better reader experience

---

## Specific Error Messages

### "mail(): Failed to connect to mailserver"

**Cause:** Hostinger server configuration issue

**Fix:**
1. Contact Hostinger support
2. Tell them: "mail() function is not connecting properly"
3. Ask them to check server mail configuration
4. May need to upgrade hosting plan

### "Permission denied" errors in logs

**Cause:** WordPress directory permissions issue

**Fix:**
1. Connect via FTP to `/wp-content/plugins/smtp-alternative/`
2. Right-click on the folder → Permissions
3. Set to: `755` for directories, `644` for files
4. If you can't change permissions, contact Hostinger

### "Class not found" error

**Cause:** Plugin files not uploaded correctly

**Fix:**
1. Deactivate plugin
2. Via FTP, delete the entire `smtp-alternative` folder
3. Re-upload all files
4. Activate plugin again

---

## WooCommerce Specific Issues

### OTP Emails Not Sending

1. Make sure Simple Mail plugin is **active**
2. Go to **WooCommerce** → **Settings** → **Email**
3. Verify email addresses are set
4. Send a test WooCommerce email:
   - Place a test order
   - Check if email arrives
   - If not, see "Email Not Sending" section above

### Order Notification Going to Customer

If customer says they didn't get order confirmation:

1. First check the plugin is active
2. Verify "From Email" is configured correctly
3. Check if email went to customer's spam folder
4. Have customer add your from email to their contacts
5. May need to improve domain reputation (see Spam section)

---

## Performance Issues

### Emails Sending Too Slow

**Solutions:**
1. This is usually a server configuration issue, not plugin issue
2. Contact Hostinger support to check:
   - Mail server performance
   - Server load
   - Any mail queue backup

### Too Many "Failed" Log Entries

1. Check recipient email addresses are valid
2. Make sure domain reputation is good (see Spam section)
3. Verify mailbox isn't full
4. Check Hostinger server status for mail issues

---

## Advanced Troubleshooting

### Enable Debug Logging

1. Add to `wp-config.php`:
   ```php
   define( 'WP_DEBUG', true );
   define( 'WP_DEBUG_LOG', true );
   define( 'WP_DEBUG_DISPLAY', false );
   ```

2. Resend a test email

3. Check `/wp-content/debug.log` for details

4. Remove these lines when done (for security)

### Test PHP mail() Directly

Create a test file at `/public_html/test-mail.php`:

```php
<?php
$to = 'your-email@gmail.com';
$subject = 'Test Email';
$message = 'This is a test email from PHP mail()';
$result = mail($to, $subject, $message);

if ($result) {
    echo "Email sent successfully!";
} else {
    echo "Failed to send email";
}
?>
```

1. Upload this file via FTP
2. Visit `yoursite.com/test-mail.php` in browser
3. Check if you receive the email
4. Delete the test file after testing

### Check Mail Server Status

1. Ask Hostinger support: "Is mail server running?"
2. Check with them if there's any known issue
3. Ask them to verify mail() function works

---

## Hostinger Specific Help

### Hostinger Control Panel Navigation

1. Log in to Hostinger
2. Click your domain name (not Hosting)
3. You'll find:
   - **Email** → Email account management
   - **DNS** → DNS records for SPF/DKIM
   - **Tools** → Logs and diagnostics
   - **SSL** → SSL certificate status

### Contacting Hostinger Support

1. Log in to Hostinger
2. Click **Help** (bottom left)
3. Click **Contact Support** or **Open Ticket**
4. Choose **Chat** for fastest response
5. Tell them:
   - Your domain name
   - Your issue
   - You're using PHP mail() function

### What Hostinger Can Help With

✓ Enable/disable mail() function
✓ Check server mail configuration
✓ Review mail server logs
✓ Help with DNS records
✓ Troubleshoot email deliverability
✓ Check server status

---

## Common Misconfigurations

### ✗ Wrong Setup

- Using Gmail address as "From Email"
- Not creating email account on your domain
- Having multiple domains and wrong "From Email" for domain
- Not enabling the plugin after installation

### ✓ Correct Setup

- Use email on your domain: `noreply@yourdomain.com`
- Create this email account in Hostinger Email
- Make sure email exists on the right domain
- Enable plugin in settings

---

## When to Contact Support

**Contact Hostinger support if:**
- mail() function shows as unavailable
- You see "mailserver" or "SMTP" errors
- mail() worked before but stopped working
- You see permission denied errors and can't fix them

**Contact plugin author if:**
- Plugin won't activate at all
- You see PHP syntax errors
- Multiple plugins conflict with this one

**Check WordPress support if:**
- WordPress isn't sending emails
- You see WordPress-specific errors
- Email format/HTML issues

---

## Quick Reference

| Issue | First Check | Quick Fix |
|-------|------------|----------|
| Emails not sending | Plugin enabled? | Enable in settings |
| mail() unavailable | Hostinger limits | Contact Hostinger support |
| Going to spam | "From" email correct? | Use `noreply@domain.com` |
| Slow sending | Server issue | Contact Hostinger |
| Permission error | File permissions | Set to 755 via FTP |
| WooCommerce OTP | Plugin active? | Activate plugin |
| Test email fails | Plugin settings | Configure from email |

---

**Remember:** Most issues are configuration-related, not plugin bugs. Double-check your settings first!
