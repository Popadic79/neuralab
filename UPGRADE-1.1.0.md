# Upgrade to Version 1.1.0

## What's New

This update brings significant improvements to the Neuralab Contact Form plugin with AJAX functionality and an admin settings page.

## New Features

### 1. AJAX Form Submission ⚡
- **No page reload** - Form submits instantly without refreshing the page
- **Instant feedback** - Success/error messages appear immediately
- **Loading state** - Submit button shows "Sending..." during submission
- **Auto-hide messages** - Success messages automatically fade out after 5 seconds
- **Smooth scrolling** - Page scrolls to message for better visibility
- **Graceful degradation** - Still works if JavaScript is disabled

### 2. Admin Settings Page 📊
- **New menu item** - "Contact Form" in WordPress admin sidebar
- **Shortcode display** - Easy copy-paste shortcode with visual styling
- **Form features list** - Overview of all form capabilities
- **Email settings** - Shows current admin email for submissions
- **Plugin information** - Version, author, and documentation links
- **Help section** - Troubleshooting tips and best practices

### 3. Admin Notice 🔔
- **Activation notice** - Appears on dashboard after plugin activation
- **Quick link** - Direct link to settings page
- **Dismissible** - Can be closed and won't show again

## Technical Improvements

### New Files
- `assets/js/form-handler.js` - AJAX form submission handler
- `UPGRADE-1.1.0.md` - This upgrade guide

### Updated Files
- `neuralab-contact-form.php` - Added AJAX handlers, admin page, constants
- `README.md` - Updated changelog and features list
- `assets/css/style.css` - (No changes, but version bumped)

### New Functions
- `neuralab_cf_enqueue_assets()` - Enqueues CSS and JavaScript
- `neuralab_cf_ajax_submit()` - Handles AJAX form submissions
- `neuralab_cf_add_admin_menu()` - Registers admin menu page
- `neuralab_cf_admin_page()` - Renders admin settings page
- `neuralab_cf_admin_notice()` - Shows activation notice
- `neuralab_cf_dismiss_notice()` - Handles notice dismissal

### New Constants
- `NEURALAB_CF_VERSION` - Plugin version (1.1.0)
- `NEURALAB_CF_PLUGIN_DIR` - Plugin directory path
- `NEURALAB_CF_PLUGIN_URL` - Plugin URL

### New AJAX Actions
- `wp_ajax_neuralab_cf_submit` - For logged-in users
- `wp_ajax_nopriv_neuralab_cf_submit` - For non-logged-in users
- `wp_ajax_neuralab_cf_dismiss_notice` - For dismissing admin notice

## How to Access New Features

### Admin Settings Page
1. Go to WordPress Admin Dashboard
2. Look for **"Contact Form"** in the left sidebar (with email icon)
3. Click to view settings and copy the shortcode

### Using AJAX Submission
- No action required! The form automatically uses AJAX if JavaScript is enabled
- If JavaScript is disabled, the form falls back to traditional POST submission

## Compatibility

- **WordPress:** 5.0 or higher
- **PHP:** 7.4 or higher
- **Browsers:** All modern browsers (Chrome, Firefox, Safari, Edge)
- **Mobile:** Fully responsive and mobile-friendly

## Breaking Changes

**None!** This is a fully backward-compatible update. All existing functionality remains unchanged.

## Migration Notes

No migration required. Simply update the plugin files and you're ready to go!

## Testing Checklist

After upgrading, test the following:

- [ ] Form submits without page reload
- [ ] Success message appears after submission
- [ ] Error messages display correctly
- [ ] Form resets after successful submission
- [ ] Admin receives emails
- [ ] User receives copy (if checkbox is checked)
- [ ] Admin settings page is accessible
- [ ] Shortcode can be copied from admin page
- [ ] Form still works with JavaScript disabled (fallback)

## Support

If you encounter any issues after upgrading:

1. Clear your browser cache
2. Clear WordPress cache (if using a caching plugin)
3. Check browser console for JavaScript errors
4. Verify WordPress and PHP versions meet requirements
5. Contact the developer if issues persist

## Developer Notes

### JavaScript API
The form handler uses jQuery and is localized with the following object:

```javascript
neuralabCF = {
    ajaxUrl: 'admin-ajax.php URL',
    submitText: 'Send Message',
    submitting: 'Sending...',
    errorMessage: 'An error occurred. Please try again.'
}
```

### Hooks (Unchanged)
All existing filters and actions remain functional:
- `neuralab_cf_admin_email`
- `neuralab_cf_email_subject`
- `neuralab_cf_email_body`
- `neuralab_cf_email_headers`
- `neuralab_cf_user_email_subject`
- `neuralab_cf_after_submission`

## What's Next?

Check the README.md for the roadmap. Version 1.2.0 will include:
- File upload support
- Google reCAPTCHA integration
- Email templates
- Form submission logging

---

**Enjoy the new features!** 🎉

For questions or feedback, contact Milan Popadic at [https://smashingpixel.net/](https://smashingpixel.net/)

