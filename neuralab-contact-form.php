<?php
/**
 * Plugin Name: Neuralab Contact Form
 * Description: A simple contact form for Neuralab. It sends an email to the admin and optionally to the user. It also has a honeypot to prevent spam.
 * Version: 1.0.0
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

/**
 * Render the contact form shortcode
 * 
 * @return string Form HTML
 */
function neuralab_cf_render_form() {
    ob_start();
    
    // Preserve form data on error
    $form_data = array(
        'first_name' => isset( $_GET['cf_error'] ) && isset( $_POST['first_name'] ) ? sanitize_text_field( $_POST['first_name'] ) : '',
        'last_name' => isset( $_GET['cf_error'] ) && isset( $_POST['last_name'] ) ? sanitize_text_field( $_POST['last_name'] ) : '',
        'email' => isset( $_GET['cf_error'] ) && isset( $_POST['email'] ) ? sanitize_email( $_POST['email'] ) : '',
        'inquiry_type' => isset( $_GET['cf_error'] ) && isset( $_POST['inquiry_type'] ) ? sanitize_text_field( $_POST['inquiry_type'] ) : '',
        'message' => isset( $_GET['cf_error'] ) && isset( $_POST['message'] ) ? sanitize_textarea_field( $_POST['message'] ) : '',
        'send_copy' => isset( $_GET['cf_error'] ) && isset( $_POST['send_copy'] ) ? true : false,
    );
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
    // Display success message
    if ( isset( $_GET['cf_sent'] ) && $_GET['cf_sent'] === '1' ) {
        echo '<div class="neuralab-cf-message neuralab-cf-success">' . esc_html__( 'Thank you! Your message has been sent successfully.', 'neuralab' ) . '</div>';
    }
    
    // Display error message
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
 * Process the contact form submission
 */
function neuralab_cf_process_form() {
    // Check if form was submitted
    if ( ! isset( $_POST['nc_form_submit'] ) ) {
        return;
    }

    // Verify nonce for security
    if ( ! isset( $_POST['nc_form_nonce'] ) || ! wp_verify_nonce( $_POST['nc_form_nonce'], 'nc_form' ) ) {
        wp_safe_redirect( add_query_arg( 'cf_error', 'security', wp_get_referer() ) );
        exit;
    }

    // Check honeypot field (spam prevention)
    if ( ! empty( $_POST['website'] ) ) {
        // Bot detected, silently fail
        wp_safe_redirect( wp_get_referer() );
        exit;
    }

    // Sanitize and validate input
    $first_name = isset( $_POST['first_name'] ) ? sanitize_text_field( $_POST['first_name'] ) : '';
    $last_name = isset( $_POST['last_name'] ) ? sanitize_text_field( $_POST['last_name'] ) : '';
    $email = isset( $_POST['email'] ) ? sanitize_email( $_POST['email'] ) : '';
    $inquiry_type = isset( $_POST['inquiry_type'] ) ? sanitize_text_field( $_POST['inquiry_type'] ) : '';
    $message = isset( $_POST['message'] ) ? sanitize_textarea_field( $_POST['message'] ) : '';
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
    $headers = array(
        'Reply-To: ' . $email,
        'Content-Type: text/plain; charset=UTF-8'
    );

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

    // Fire action after successful submission
    do_action( 'neuralab_cf_after_submission', $first_name, $last_name, $email, $inquiry_type, $message );

    // Redirect with success message
    wp_safe_redirect( add_query_arg( 'cf_sent', '1', wp_get_referer() ) );
    exit;
}
add_action( 'wp_loaded', 'neuralab_cf_process_form' );

/**
 * Enqueue styles for the contact form
 */
function neuralab_cf_enqueue_styles() {
    if ( ! is_admin() ) {
        wp_enqueue_style(
            'neuralab-contact-form',
            plugin_dir_url( __FILE__ ) . 'assets/css/style.css',
            array(),
            '1.0.0',
            'all'
        );
    }
}
add_action( 'wp_enqueue_scripts', 'neuralab_cf_enqueue_styles' );