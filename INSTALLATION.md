# Installation Guide - Simple Mail via PHP mail()

This guide will help you install and activate the Simple Mail plugin on your WordPress site hosted on Hostinger.

## Quick Start (5 minutes)

### Step 1: Download the Plugin

You can either:
- **Option A**: Download from GitHub or your source
- **Option B**: Get the ready-to-upload ZIP file

### Step 2: Upload to WordPress

**Method 1: Using WordPress Admin (Easiest)**

1. Log in to your WordPress Dashboard
2. Go to **Plugins** → **Add New**
3. Click the **Upload Plugin** button
4. Click **Choose File** and select `smtp-alternative.zip`
5. Click **Install Now**
6. Click **Activate Plugin**
7. Done! ✓

**Method 2: Using FTP (If upload method doesn't work)**

1. Extract the `smtp-alternative.zip` file on your computer
2. Connect to your Hostinger account via FTP:
   - Use FileZilla or similar FTP client
   - Server: ftp.yourdomain.com (from Hostinger control panel)
   - Username: FTP username (from Hostinger control panel)
   - Password: FTP password (from Hostinger control panel)
3. Navigate to `/public_html/wp-content/plugins/`
4. Upload the entire `smtp-alternative` folder
5. Go to WordPress Admin → **Plugins**
6. Find "Simple Mail via PHP mail()" and click **Activate**

### Step 3: Configure Settings

1. In WordPress Admin, go to **Settings** → **Simple Mail**
2. Make sure **Enable Plugin** is checked ✓
3. Set **From Email Address**: Use an email on your domain (e.g., `noreply@yourdomain.com`)
4. Set **From Name**: Your business or website name
5. Click **Save Changes**

### Step 4: Verify Installation

On the plugin settings page, you'll see:
- ✓ Plugin is ACTIVE (should be green)
- ✓ PHP mail() function available (should be green)

If both show green, you're all set!

---

## Detailed Setup Instructions

### For Hostinger Users

**Step 1: Create an Email Account (Optional but Recommended)**

1. Log in to Hostinger Control Panel
2. Go to **Email** → **Email Accounts**
3. Create a new email like `noreply@yourdomain.com`
4. Set a password
5. Click **Create**

**Step 2: Install the Plugin**

See "Quick Start" section above for plugin installation.

**Step 3: Configure Plugin Settings**

1. Go to **Settings** → **Simple Mail**
2. Enter the email you created: `noreply@yourdomain.com`
3. Set From Name to your site name
4. Save Changes

**Step 4: Test Email Sending**

1. Install "Test Email" plugin:
   - Go to **Plugins** → **Add New**
   - Search for "Test Email"
   - Install and Activate
2. Use the test plugin to send an email to yourself
3. Check inbox (and spam folder!)

---

## Installation Troubleshooting

### Problem: "Cannot activate plugin" error

**Solution:**
1. Make sure you have WordPress 5.0 or higher
2. Check that PHP version is 7.2 or higher:
   - Go to **Tools** → **Site Health**
   - Look for PHP version information
3. Contact Hostinger support if you need to upgrade PHP

### Problem: Plugin doesn't appear after upload

**Solution:**
1. Verify file upload completed (check FTP)
2. Ensure folder is named exactly `smtp-alternative`
3. Check file permissions (should be 755 for folders, 644 for files)
4. Clear browser cache and reload

### Problem: "White screen" after activation

**Solution:**
1. Deactivate all other plugins via FTP:
   - Connect via FTP to `/public_html/wp-content/plugins/`
   - Rename the plugin folder temporarily
2. Reactivate one by one to find conflict
3. Contact plugin support with error from error log

### Problem: Hostinger says mail() function is disabled

**Solution:**
1. Email Hostinger support asking to enable `mail()` function
2. Mention you need it for WordPress email sending
3. Most Hostinger plans have it enabled by default
4. Premium plans definitely have it

---

## Uninstalling the Plugin

### If You Need to Remove It

1. Go to **Plugins** in WordPress Admin
2. Find "Simple Mail via PHP mail()"
3. Click **Deactivate**
4. Click **Delete**
5. Confirm deletion

### To Keep Settings for Re-installation

The plugin stores settings in WordPress database options:
- `smtp_alt_enabled`
- `smtp_alt_from_email`
- `smtp_alt_from_name`

These are kept even after deletion if you choose not to delete data.

---

## Next Steps

After installation:

1. **Test Email Sending** - Use Test Email plugin to verify it works
2. **Check Spam Settings** - Make sure emails aren't going to spam
3. **Set Up Backups** - Consider backing up your WordPress installation
4. **Monitor Logs** - Enable debug logging if you want to track emails

---

## Support

If you encounter issues:

1. **Check Plugin Settings Page** - It shows if everything is working
2. **Review WordPress Logs** - Check `wp-content/debug.log` for errors
3. **Contact Hostinger** - They can verify mail() function is working
4. **Check Email Deliverability** - Review DKIM, SPF, DMARC settings in Hostinger

---

**Note:** This plugin is designed to work seamlessly on Hostinger. If you have any issues, Hostinger support team can help verify your server configuration.
