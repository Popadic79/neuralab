<?php
/**
 * Plugin Name: Neuralab Contact Form
 * Description: A simple contact form for Neuralab. It sends an email to the admin and optionally to the user. Features AJAX submission and admin settings page.
 * Version: 1.1.0
 * Requires at least: 5.8
 * Requires PHP: 8.2
 * Author: Milan Popadic
 * Author URI: https://smashingpixel.net/
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: neuralab
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

// Check PHP version requirement
if ( version_compare( PHP_VERSION, '8.2.0', '<' ) ) {
    add_action( 'admin_notices', function() {
        ?>
        <div class="notice notice-error">
            <p>
                <?php
                printf(
                    /* translators: 1: Required PHP version, 2: Current PHP version */
                    esc_html__( 'Neuralab Contact Form requires PHP %1$s or higher. You are running PHP %2$s. Please upgrade PHP to activate this plugin.', 'neuralab' ),
                    '8.2',
                    PHP_VERSION
                );
                ?>
            </p>
        </div>
        <?php
    });
    return;
}

// Define plugin constants
define( 'NEURALAB_CF_VERSION', '1.1.0' );
define( 'NEURALAB_CF_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'NEURALAB_CF_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'NEURALAB_CF_MIN_PHP', '8.2.0' );
define( 'NEURALAB_CF_MIN_WP', '5.8' );

/**
 * Render the contact form shortcode
 * 
 * @return string Form HTML
 */
function neuralab_cf_render_form(): string {
    ob_start();
    
    // Preserve form data on error (with proper type checking)
    $form_data = [
        'first_name' => isset( $_GET['cf_error'] ) && isset( $_POST['first_name'] ) ? sanitize_text_field( wp_unslash( $_POST['first_name'] ) ) : '',
        'last_name' => isset( $_GET['cf_error'] ) && isset( $_POST['last_name'] ) ? sanitize_text_field( wp_unslash( $_POST['last_name'] ) ) : '',
        'email' => isset( $_GET['cf_error'] ) && isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '',
        'inquiry_type' => isset( $_GET['cf_error'] ) && isset( $_POST['inquiry_type'] ) ? sanitize_text_field( wp_unslash( $_POST['inquiry_type'] ) ) : '',
        'message' => isset( $_GET['cf_error'] ) && isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '',
        'send_copy' => isset( $_GET['cf_error'] ) && isset( $_POST['send_copy'] ),
    ];
    ?>

    <form class="neuralab-contact-form" method="post" action="">
        <?php wp_nonce_field( 'nc_form', 'nc_form_nonce' ); ?>
        
        <!-- Honeypot -->
        <input type="text" name="website" style="display:none" tabindex="-1" autocomplete="off">

        <div class="neuralab-cf-field">
            <label for="neuralab_cf_first_name"><?php echo esc_html__( 'First Name', 'neuralab' ); ?> <span class="required">*</span></label>
            <input type="text" id="neuralab_cf_first_name" name="first_name" value="<?php echo esc_attr( $form_data['first_name'] ); ?>" required>
        </div>

        <div class="neuralab-cf-field">
            <label for="neuralab_cf_last_name"><?php echo esc_html__( 'Last Name', 'neuralab' ); ?> <span class="required">*</span></label>
            <input type="text" id="neuralab_cf_last_name" name="last_name" value="<?php echo esc_attr( $form_data['last_name'] ); ?>" required>
        </div>

        <div class="neuralab-cf-field">
            <label for="neuralab_cf_email"><?php echo esc_html__( 'Email Address', 'neuralab' ); ?> <span class="required">*</span></label>
            <input type="email" id="neuralab_cf_email" name="email" value="<?php echo esc_attr( $form_data['email'] ); ?>" required>
        </div>

        <div class="neuralab-cf-field">
            <label for="neuralab_cf_inquiry_type"><?php echo esc_html__( 'Inquiry Type', 'neuralab' ); ?></label>
            <select id="neuralab_cf_inquiry_type" class="neuralab-cf-select" name="inquiry_type">
                <button>
                    <selectedcontent></selectedcontent>
                </button>
                <option value="General Inquiry" <?php selected( $form_data['inquiry_type'], 'General Inquiry' ); ?>><?php echo esc_html__( 'General Inquiry', 'neuralab' ); ?></option>
                <option value="Sales Question" <?php selected( $form_data['inquiry_type'], 'Sales Question' ); ?>><?php echo esc_html__( 'Sales Question', 'neuralab' ); ?></option>
                <option value="Refund" <?php selected( $form_data['inquiry_type'], 'Refund' ); ?>><?php echo esc_html__( 'Refund', 'neuralab' ); ?></option>
            </select>
        </div>

        <div class="neuralab-cf-field">
            <label for="neuralab_cf_message"><?php echo esc_html__( 'Message', 'neuralab' ); ?></label>
            <textarea id="neuralab_cf_message" name="message" rows="5"><?php echo esc_textarea( $form_data['message'] ); ?></textarea>
        </div>

        <div class="neuralab-cf-field neuralab-cf-checkbox">
            <label>
                <input type="checkbox" name="send_copy" value="1" <?php checked( $form_data['send_copy'], true ); ?>> 
                <?php echo esc_html__( 'Send a message to myself', 'neuralab' ); ?>
            </label>
        </div>

        <button type="submit" name="nc_form_submit" class="neuralab-cf-submit"><?php echo esc_html__( 'Send Message', 'neuralab' ); ?></button>
    </form>
    
    <?php
    // Fallback messages for non-AJAX submissions (when JavaScript is disabled)
    if ( isset( $_GET['cf_sent'] ) && $_GET['cf_sent'] === '1' ) {
        echo '<div class="neuralab-cf-message neuralab-cf-success">' . esc_html__( 'Thank you! Your message has been sent successfully.', 'neuralab' ) . '</div>';
    }
    
    if ( isset( $_GET['cf_error'] ) ) {
        $error_message = '';
        switch ( $_GET['cf_error'] ) {
            case 'invalid_email':
                $error_message = __( 'Please provide a valid email address.', 'neuralab' );
                break;
            case 'missing_fields':
                $error_message = __( 'Please fill in all required fields.', 'neuralab' );
                break;
            default:
                $error_message = __( 'An error occurred. Please try again.', 'neuralab' );
        }
        echo '<div class="neuralab-cf-message neuralab-cf-error">' . esc_html( $error_message ) . '</div>';
    }

    return ob_get_clean();
}
/**
 * Register the shortcode
 */
add_shortcode( 'nc_form', 'neuralab_cf_render_form' );

/**
 * Process the contact form submission (non-AJAX fallback)
 * 
 * @return void
 */
function neuralab_cf_process_form(): void {
    // Check if form was submitted
    if ( ! isset( $_POST['nc_form_submit'] ) ) {
        return;
    }

    // Verify nonce for security
    if ( ! isset( $_POST['nc_form_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nc_form_nonce'] ) ), 'nc_form' ) ) {
        wp_safe_redirect( add_query_arg( 'cf_error', 'security', wp_get_referer() ) );
        exit;
    }

    // Check honeypot field (spam prevention)
    if ( ! empty( $_POST['website'] ) ) {
        // Bot detected, silently fail
        wp_safe_redirect( wp_get_referer() );
        exit;
    }

    // Sanitize and validate input with wp_unslash to prevent double slashing
    $first_name = isset( $_POST['first_name'] ) ? sanitize_text_field( wp_unslash( $_POST['first_name'] ) ) : '';
    $last_name = isset( $_POST['last_name'] ) ? sanitize_text_field( wp_unslash( $_POST['last_name'] ) ) : '';
    $email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
    $inquiry_type = isset( $_POST['inquiry_type'] ) ? sanitize_text_field( wp_unslash( $_POST['inquiry_type'] ) ) : '';
    $message = isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '';
    $send_copy = isset( $_POST['send_copy'] );

    // Validate required fields
    if ( empty( $first_name ) || empty( $last_name ) || empty( $email ) ) {
        wp_safe_redirect( add_query_arg( 'cf_error', 'missing_fields', wp_get_referer() ) );
        exit;
    }

    // Validate email address
    if ( ! is_email( $email ) ) {
        wp_safe_redirect( add_query_arg( 'cf_error', 'invalid_email', wp_get_referer() ) );
        exit;
    }
    
    // Rate limiting check (prevent spam)
    $user_ip = neuralab_cf_get_user_ip();
    $rate_limit_key = 'neuralab_cf_submit_' . md5( $user_ip );
    $recent_submissions = get_transient( $rate_limit_key );
    
    if ( $recent_submissions && $recent_submissions >= 3 ) {
        wp_safe_redirect( add_query_arg( 'cf_error', 'rate_limit', wp_get_referer() ) );
        exit;
    }

    // Prepare email content
    $admin_email = get_option( 'admin_email' );
    $subject = sprintf( 
        /* translators: %s: inquiry type */
        __( 'New Contact Form Submission: %s', 'neuralab' ), 
        $inquiry_type 
    );
    
    $body = sprintf(
        __( "First Name: %s\nLast Name: %s\nEmail: %s\nInquiry Type: %s\n\nMessage:\n%s", 'neuralab' ),
        $first_name,
        $last_name,
        $email,
        $inquiry_type,
        $message
    );

    // Set email headers
    $headers = [
        'Reply-To: ' . sanitize_email( $email ),
        'Content-Type: text/plain; charset=UTF-8',
    ];

    // Allow filtering of email parameters
    $admin_email = apply_filters( 'neuralab_cf_admin_email', $admin_email );
    $subject = apply_filters( 'neuralab_cf_email_subject', $subject, $inquiry_type );
    $body = apply_filters( 'neuralab_cf_email_body', $body, $first_name, $last_name, $email, $inquiry_type, $message );
    $headers = apply_filters( 'neuralab_cf_email_headers', $headers, $email );

    // Send email to admin
    $admin_sent = wp_mail( $admin_email, $subject, $body, $headers );

    if ( ! $admin_sent ) {
        wp_safe_redirect( add_query_arg( 'cf_error', 'send_failed', wp_get_referer() ) );
        exit;
    }

    // Send copy to user if requested
    if ( $send_copy ) {
        $user_subject = __( 'Copy of your Contact Form Submission', 'neuralab' );
        $user_subject = apply_filters( 'neuralab_cf_user_email_subject', $user_subject );
        
        wp_mail( $email, $user_subject, $body );
    }
    
    // Update rate limit counter
    $new_count = $recent_submissions ? $recent_submissions + 1 : 1;
    set_transient( $rate_limit_key, $new_count, HOUR_IN_SECONDS );

    // Fire action after successful submission
    do_action( 'neuralab_cf_after_submission', $first_name, $last_name, $email, $inquiry_type, $message );

    // Redirect with success message
    wp_safe_redirect( add_query_arg( 'cf_sent', '1', wp_get_referer() ) );
    exit;
}
add_action( 'wp_loaded', 'neuralab_cf_process_form' );

/**
 * Enqueue styles and scripts for the contact form
 * 
 * @return void
 */
function neuralab_cf_enqueue_assets(): void {
    if ( ! is_admin() ) {
        // Enqueue CSS
        wp_enqueue_style(
            'neuralab-contact-form',
            NEURALAB_CF_PLUGIN_URL . 'assets/css/style.css',
            [],
            NEURALAB_CF_VERSION,
            'all'
        );

        // Enqueue jQuery (WordPress core)
        wp_enqueue_script( 'jquery' );

        // Enqueue custom JavaScript
        wp_enqueue_script(
            'neuralab-contact-form',
            NEURALAB_CF_PLUGIN_URL . 'assets/js/form-handler.js',
            [ 'jquery' ],
            NEURALAB_CF_VERSION,
            true
        );

        // Localize script with AJAX URL and translations
        wp_localize_script(
            'neuralab-contact-form',
            'neuralabCF',
            [
                'ajaxUrl' => admin_url( 'admin-ajax.php' ),
                'nonce' => wp_create_nonce( 'neuralab_cf_ajax' ),
                'submitText' => __( 'Send Message', 'neuralab' ),
                'submitting' => __( 'Sending...', 'neuralab' ),
                'errorMessage' => __( 'An error occurred. Please try again.', 'neuralab' ),
            ]
        );
    }
}
add_action( 'wp_enqueue_scripts', 'neuralab_cf_enqueue_assets' );

/**
 * Get user IP address securely
 * 
 * @return string User IP address
 */
function neuralab_cf_get_user_ip(): string {
    $ip = '';
    
    if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
        $ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_CLIENT_IP'] ) );
    } elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
        $ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) );
    } elseif ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
        $ip = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
    }
    
    // Validate IP address
    $validated_ip = filter_var( $ip, FILTER_VALIDATE_IP );
    
    return $validated_ip !== false ? $validated_ip : '0.0.0.0';
}

/**
 * Handle AJAX form submission
 * 
 * @return void
 */
function neuralab_cf_ajax_submit(): void {
    // Verify nonce
    if ( ! isset( $_POST['nc_form_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nc_form_nonce'] ) ), 'nc_form' ) ) {
        wp_send_json_error( [
            'message' => __( 'Security verification failed. Please refresh the page and try again.', 'neuralab' ),
        ] );
    }

    // Check honeypot field (spam prevention)
    if ( ! empty( $_POST['website'] ) ) {
        // Bot detected, return success but don't send email
        wp_send_json_success( [
            'message' => __( 'Thank you! Your message has been sent successfully.', 'neuralab' ),
        ] );
    }

    // Rate limiting check (prevent spam)
    $user_ip = neuralab_cf_get_user_ip();
    $rate_limit_key = 'neuralab_cf_submit_' . md5( $user_ip );
    $recent_submissions = get_transient( $rate_limit_key );
    
    if ( $recent_submissions && $recent_submissions >= 3 ) {
        wp_send_json_error( [
            'message' => __( 'Too many submissions. Please try again later.', 'neuralab' ),
        ] );
    }

    // Sanitize and validate input with wp_unslash
    $first_name = isset( $_POST['first_name'] ) ? sanitize_text_field( wp_unslash( $_POST['first_name'] ) ) : '';
    $last_name = isset( $_POST['last_name'] ) ? sanitize_text_field( wp_unslash( $_POST['last_name'] ) ) : '';
    $email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
    $inquiry_type = isset( $_POST['inquiry_type'] ) ? sanitize_text_field( wp_unslash( $_POST['inquiry_type'] ) ) : '';
    $message = isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '';
    $send_copy = isset( $_POST['send_copy'] );

    // Validate required fields
    if ( empty( $first_name ) || empty( $last_name ) || empty( $email ) ) {
        wp_send_json_error( [
            'message' => __( 'Please fill in all required fields.', 'neuralab' ),
        ] );
    }

    // Validate email address
    if ( ! is_email( $email ) ) {
        wp_send_json_error( [
            'message' => __( 'Please provide a valid email address.', 'neuralab' ),
        ] );
    }

    // Prepare email content
    $admin_email = get_option( 'admin_email' );
    $subject = sprintf(
        /* translators: %s: inquiry type */
        __( 'New Contact Form Submission: %s', 'neuralab' ),
        $inquiry_type
    );

    $body = sprintf(
        __( "First Name: %s\nLast Name: %s\nEmail: %s\nInquiry Type: %s\n\nMessage:\n%s", 'neuralab' ),
        $first_name,
        $last_name,
        $email,
        $inquiry_type,
        $message
    );

    // Set email headers
    $headers = [
        'Reply-To: ' . sanitize_email( $email ),
        'Content-Type: text/plain; charset=UTF-8',
    ];

    // Allow filtering of email parameters
    $admin_email = apply_filters( 'neuralab_cf_admin_email', $admin_email );
    $subject = apply_filters( 'neuralab_cf_email_subject', $subject, $inquiry_type );
    $body = apply_filters( 'neuralab_cf_email_body', $body, $first_name, $last_name, $email, $inquiry_type, $message );
    $headers = apply_filters( 'neuralab_cf_email_headers', $headers, $email );

    // Send email to admin
    $admin_sent = wp_mail( $admin_email, $subject, $body, $headers );

    if ( ! $admin_sent ) {
        wp_send_json_error( [
            'message' => __( 'Failed to send your message. Please try again later.', 'neuralab' ),
        ] );
    }

    // Send copy to user if requested
    if ( $send_copy ) {
        $user_subject = __( 'Copy of your Contact Form Submission', 'neuralab' );
        $user_subject = apply_filters( 'neuralab_cf_user_email_subject', $user_subject );

        wp_mail( $email, $user_subject, $body );
    }
    
    // Update rate limit counter
    $new_count = $recent_submissions ? $recent_submissions + 1 : 1;
    set_transient( $rate_limit_key, $new_count, HOUR_IN_SECONDS );

    // Fire action after successful submission
    do_action( 'neuralab_cf_after_submission', $first_name, $last_name, $email, $inquiry_type, $message );

    // Return success response
    wp_send_json_success( [
        'message' => __( 'Thank you! Your message has been sent successfully.', 'neuralab' ),
    ] );
}
add_action( 'wp_ajax_neuralab_cf_submit', 'neuralab_cf_ajax_submit' );
add_action( 'wp_ajax_nopriv_neuralab_cf_submit', 'neuralab_cf_ajax_submit' );

/**
 * Add admin menu page
 * 
 * @return void
 */
function neuralab_cf_add_admin_menu(): void {
    add_menu_page(
        __( 'Neuralab Contact Form', 'neuralab' ),
        __( 'Neuralab Form', 'neuralab' ),
        'manage_options',
        'neuralab-contact-form',
        'neuralab_cf_admin_page',
        'dashicons-email-alt',
        30
    );
}
add_action( 'admin_menu', 'neuralab_cf_add_admin_menu' );

/**
 * Render admin settings page
 * 
 * @return void
 */
function neuralab_cf_admin_page(): void {
    // Check user capabilities
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'neuralab' ) );
    }
    ?>
    <div class="wrap">
        <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
        
        <div class="neuralab-cf-admin-container" style="max-width: 800px;">
            
            <div class="card" style="margin-top: 20px;">
                <h2><?php _e( 'Shortcode', 'neuralab' ); ?></h2>
                <p><?php _e( 'Use the following shortcode to display the contact form on any page or post:', 'neuralab' ); ?></p>
                
                <div style="background: #f5f5f5; padding: 15px; border-left: 4px solid #2271b1; margin: 15px 0;">
                    <code style="font-size: 16px; color: #d63638;">[nc_form]</code>
                </div>
                
                <p class="description">
                    <?php _e( 'Simply copy and paste this shortcode into any page, post, or widget area where you want the contact form to appear.', 'neuralab' ); ?>
                </p>
            </div>

            <div class="card" style="margin-top: 20px;">
                <h2><?php _e( 'Form Features', 'neuralab' ); ?></h2>
                <ul style="list-style: disc; margin-left: 20px;">
                    <li><?php _e( 'AJAX submission (no page reload)', 'neuralab' ); ?></li>
                    <li><?php _e( 'Required fields: First Name, Last Name, Email', 'neuralab' ); ?></li>
                    <li><?php _e( 'Optional fields: Inquiry Type, Message', 'neuralab' ); ?></li>
                    <li><?php _e( 'Built-in spam protection with honeypot', 'neuralab' ); ?></li>
                    <li><?php _e( 'User can receive a copy of their submission', 'neuralab' ); ?></li>
                    <li><?php _e( 'All submissions are sent to the admin email', 'neuralab' ); ?></li>
                </ul>
            </div>

            <div class="card" style="margin-top: 20px;">
                <h2><?php _e( 'Email Settings', 'neuralab' ); ?></h2>
                <p>
                    <?php 
                    printf(
                        __( 'Contact form submissions will be sent to: <strong>%s</strong>', 'neuralab' ),
                        esc_html( get_option( 'admin_email' ) )
                    ); 
                    ?>
                </p>
                <p class="description">
                    <?php 
                    printf(
                        __( 'You can change this email address in <a href="%s">WordPress Settings</a>.', 'neuralab' ),
                        admin_url( 'options-general.php' )
                    ); 
                    ?>
                </p>
            </div>

            <div class="card" style="margin-top: 20px;">
                <h2><?php _e( 'Plugin Information', 'neuralab' ); ?></h2>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e( 'Version', 'neuralab' ); ?></th>
                        <td><?php echo esc_html( NEURALAB_CF_VERSION ); ?></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e( 'Author', 'neuralab' ); ?></th>
                        <td><a href="https://smashingpixel.net/" target="_blank">Milan Popadic</a></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e( 'Documentation', 'neuralab' ); ?></th>
                        <td>
                            <a href="<?php echo esc_url( NEURALAB_CF_PLUGIN_URL . 'README.md' ); ?>" target="_blank">
                                <?php _e( 'View README', 'neuralab' ); ?>
                            </a>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="card" style="margin-top: 20px; background: #f0f6fc; border-left: 4px solid #2271b1;">
                <h2><?php _e( 'Need Help?', 'neuralab' ); ?></h2>
                <p><?php _e( 'If you encounter any issues or have questions:', 'neuralab' ); ?></p>
                <ul style="list-style: disc; margin-left: 20px;">
                    <li><?php _e( 'Check that your WordPress email is configured correctly', 'neuralab' ); ?></li>
                    <li><?php _e( 'Consider using an SMTP plugin for better email delivery', 'neuralab' ); ?></li>
                    <li><?php _e( 'Check spam/junk folders for submitted emails', 'neuralab' ); ?></li>
                    <li><?php _e( 'Contact the developer for support', 'neuralab' ); ?></li>
                </ul>
            </div>

        </div>
    </div>
    <?php
}

/**
 * Add admin notice for new installation
 * 
 * @return void
 */
function neuralab_cf_admin_notice(): void {
    $screen = get_current_screen();
    
    if ( ! $screen ) {
        return;
    }
    
    // Only show on dashboard and plugins page
    if ( $screen->id !== 'dashboard' && $screen->id !== 'plugins' ) {
        return;
    }

    // Check if user has dismissed this notice
    $dismissed = get_user_meta( get_current_user_id(), 'neuralab_cf_notice_dismissed', true );
    if ( $dismissed ) {
        return;
    }
    ?>
    <div class="notice notice-success is-dismissible neuralab-cf-notice">
        <p>
            <strong><?php esc_html_e( 'Neuralab Contact Form', 'neuralab' ); ?></strong> - 
            <?php esc_html_e( 'Plugin activated! Use the shortcode', 'neuralab' ); ?> 
            <code>[nc_form]</code> 
            <?php esc_html_e( 'to display the contact form.', 'neuralab' ); ?>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=neuralab-contact-form' ) ); ?>" style="margin-left: 10px;">
                <?php esc_html_e( 'View Settings', 'neuralab' ); ?>
            </a>
        </p>
    </div>
    <script>
    jQuery(document).ready(function($) {
        $('.neuralab-cf-notice').on('click', '.notice-dismiss', function() {
            $.post(ajaxurl, {
                action: 'neuralab_cf_dismiss_notice',
                nonce: '<?php echo esc_js( wp_create_nonce( 'neuralab_cf_dismiss_notice' ) ); ?>'
            });
        });
    });
    </script>
    <?php
}
add_action( 'admin_notices', 'neuralab_cf_admin_notice' );

/**
 * Handle notice dismissal
 * 
 * @return void
 */
function neuralab_cf_dismiss_notice(): void {
    check_ajax_referer( 'neuralab_cf_dismiss_notice', 'nonce' );
    
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html__( 'Unauthorized access.', 'neuralab' ) );
    }
    
    update_user_meta( get_current_user_id(), 'neuralab_cf_notice_dismissed', true );
    wp_die();
}
add_action( 'wp_ajax_neuralab_cf_dismiss_notice', 'neuralab_cf_dismiss_notice' );