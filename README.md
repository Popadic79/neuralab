# Neuralab Contact Form

A simple, secure contact form plugin for WordPress that sends emails to the site administrator with optional user copy functionality.

## Description

Neuralab Contact Form provides a lightweight, secure contact form solution for WordPress websites. The plugin includes built-in spam protection via honeypot fields and follows WordPress coding standards and security best practices.

## Features

- ✅ **AJAX Submission** - No page reload, instant feedback (NEW in v1.1.0)
- ✅ **Admin Settings Page** - Easy access to shortcode and settings (NEW in v1.1.0)
- ✅ **Simple Integration** - Easy to use via shortcode
- ✅ **Spam Protection** - Honeypot field to prevent bot submissions
- ✅ **User-Friendly** - Clean, responsive form design with smooth animations
- ✅ **Email Notifications** - Sends submissions to site admin
- ✅ **Copy to User** - Optional email copy sent to the submitter
- ✅ **Form Validation** - Server-side validation for all fields
- ✅ **Error Handling** - User-friendly error messages
- ✅ **Graceful Degradation** - Works without JavaScript enabled
- ✅ **Security First** - Nonce verification, input sanitization, and output escaping
- ✅ **Extensible** - Hooks and filters for customization
- ✅ **Translatable** - Internationalization ready

## Requirements

- WordPress 5.8 or higher
- PHP 8.2 or higher

**Note:** The plugin will not activate on PHP versions below 8.2 and will display a clear error message.

## Installation

1. Download the plugin folder
2. Upload the `neuralab-contact-form` folder to `/wp-content/plugins/` directory
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Use the shortcode `[nc_form]` on any page or post

## Usage

### Basic Usage

Simply add the shortcode to any page, post, or widget:

```
[nc_form]
```

**Quick Access:** After activation, go to **Contact Form** in the WordPress admin menu to view the shortcode and plugin settings.

### Form Fields

The contact form includes the following fields:

1. **First Name** (required)
2. **Last Name** (required)
3. **Email Address** (required)
4. **Inquiry Type** (optional) - Options:
   - General Inquiry
   - Sales Question
   - Refund
5. **Message** (optional)
6. **Send a message to myself** (checkbox) - User receives a copy of the submission

## Email Configuration

The plugin uses WordPress's built-in `wp_mail()` function and sends emails to the site administrator email (set in Settings > General).

### Email Details

- **To:** Site admin email (`get_option('admin_email')`)
- **Subject:** "New Contact Form Submission: [Inquiry Type]"
- **Reply-To:** Submitter's email address
- **Content:** All form field values

## Customization

### Available Filters

The plugin provides several filters for developers to customize functionality:

```php
// Modify admin email recipient
add_filter( 'neuralab_cf_admin_email', function( $admin_email ) {
    return 'custom@example.com';
});

// Customize email subject
add_filter( 'neuralab_cf_email_subject', function( $subject, $inquiry_type ) {
    return "New Form: $inquiry_type";
}, 10, 2);

// Modify email body
add_filter( 'neuralab_cf_email_body', function( $body, $first_name, $last_name, $email, $inquiry_type, $message ) {
    return "Custom formatted body...";
}, 10, 6);

// Customize email headers
add_filter( 'neuralab_cf_email_headers', function( $headers, $email ) {
    $headers[] = 'Cc: manager@example.com';
    return $headers;
}, 10, 2);

// Customize user copy subject
add_filter( 'neuralab_cf_user_email_subject', function( $subject ) {
    return 'Thank you for contacting us!';
});
```

### Available Actions

```php
// Execute custom code after successful submission
add_action( 'neuralab_cf_after_submission', function( $first_name, $last_name, $email, $inquiry_type, $message ) {
    // Log to database, send to CRM, etc.
}, 10, 5);
```

## File Structure

```
neuralab-contact-form/
├── assets/
│   ├── css/
│   │   └── style.css          # Form styling
│   └── js/
│       └── form-handler.js    # AJAX form submission
├── neuralab-contact-form.php  # Main plugin file
└── README.md                  # This file
```

## Security Features

- **Nonce Verification** - Protects against CSRF attacks
- **Honeypot Field** - Spam bot prevention
- **Input Sanitization** - All user input is sanitized
- **Output Escaping** - All output is properly escaped
- **Email Validation** - Server-side email format validation
- **Direct Access Prevention** - Files cannot be accessed directly

## Browser Support

The contact form is tested and works on:

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)
- Mobile browsers (iOS Safari, Chrome Mobile)

## Troubleshooting

### Emails Not Sending

1. Check your WordPress email configuration
2. Install a SMTP plugin like WP Mail SMTP
3. Verify the admin email in Settings > General
4. Check spam/junk folders

### Form Not Displaying

1. Verify the plugin is activated
2. Check for JavaScript conflicts
3. Ensure the shortcode `[nc_form]` is correctly placed

### Styling Issues

The plugin uses minimal CSS. If you need custom styling:

1. Edit `assets/css/style.css`
2. Or add custom CSS to your theme

## Changelog

### Version 1.1.0 (December 9, 2024)
- ✨ **NEW:** AJAX form submission (no page reload required)
- ✨ **NEW:** Admin settings page in WordPress dashboard
- ✨ **NEW:** Shortcode display in admin panel for easy copying
- ✨ **NEW:** Admin notice on activation with quick setup link
- ✨ **NEW:** Smooth scroll to success/error messages
- ✨ **NEW:** Auto-hide success messages after 5 seconds
- ✨ **NEW:** Loading state on submit button ("Sending...")
- ✅ Improved user experience with instant feedback
- ✅ JavaScript file for AJAX handling
- ✅ Graceful degradation (works without JavaScript)
- ✅ Plugin constants for version and paths
- ✅ Enhanced admin interface with helpful information

### Version 1.0.0 (December 9, 2024)
- ✨ Initial release
- ✅ Contact form with 6 fields
- ✅ Email to admin functionality
- ✅ Optional copy to user
- ✅ Spam protection via honeypot
- ✅ Nonce security
- ✅ Form validation and error handling
- ✅ Data preservation on errors
- ✅ Success/error messages
- ✅ Responsive design
- ✅ Internationalization support
- ✅ Developer hooks (filters and actions)
- ✅ Separate CSS file for easy customization

## Roadmap

Planned features for future versions:

### Version 1.2.0 (Planned)
- [ ] File upload support
- [ ] Google reCAPTCHA integration
- [ ] Email templates
- [ ] Form submission logging to database

### Version 1.3.0 (Planned)
- [ ] Multiple form support
- [ ] Custom fields via settings
- [ ] Export submissions to CSV
- [ ] Integration with popular email services

### Version 2.0.0 (Planned)
- [ ] Form builder with drag-and-drop
- [ ] Conditional fields
- [ ] Multi-step forms
- [ ] Analytics and reporting
- [ ] Dashboard widget with submission stats

## Support

For bug reports, feature requests, or questions:

- **Author:** Milan Popadic
- **Website:** [https://smashingpixel.net/](https://smashingpixel.net/)

## License

This plugin is licensed under the GPLv2 or later.

```
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
```

You should have received a copy of the GNU General Public License
along with this program. If not, see [https://www.gnu.org/licenses/gpl-2.0.html](https://www.gnu.org/licenses/gpl-2.0.html).

## Credits

Developed by Milan Popadic for Neuralab.

---

**Note:** This plugin follows the [WordPress Plugin Developer Handbook](https://developer.wordpress.org/plugins/) guidelines and best practices.

