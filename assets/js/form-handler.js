/**
 * Neuralab Contact Form - AJAX Handler
 * Version: 1.1.0
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        // Handle form submission
        $('.neuralab-contact-form').on('submit', function(e) {
            e.preventDefault();

            var $form = $(this);
            var $submitBtn = $form.find('.neuralab-cf-submit');
            var $messageContainer = $form.find('.neuralab-cf-message');

            // Disable submit button
            $submitBtn.prop('disabled', true).text(neuralabCF.submitting);

            // Remove any existing messages
            $messageContainer.remove();

            // Prepare form data
            var formData = new FormData(this);
            formData.append('action', 'neuralab_cf_submit');

            // Send AJAX request
            $.ajax({
                url: neuralabCF.ajaxUrl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        // Display success message
                        $form.before('<div class="neuralab-cf-message neuralab-cf-success">' + response.data.message + '</div>');
                        
                        // Reset form
                        $form[0].reset();

                        // Scroll to success message
                        $('html, body').animate({
                            scrollTop: $form.offset().top - 100
                        }, 500);

                        // Remove success message after 5 seconds
                        setTimeout(function() {
                            $('.neuralab-cf-success').fadeOut(function() {
                                $(this).remove();
                            });
                        }, 5000);

                    } else {
                        // Display error message
                        $form.before('<div class="neuralab-cf-message neuralab-cf-error">' + response.data.message + '</div>');
                        
                        // Scroll to error message
                        $('html, body').animate({
                            scrollTop: $form.offset().top - 100
                        }, 500);
                    }
                },
                error: function(xhr, status, error) {
                    // Display generic error message
                    $form.before('<div class="neuralab-cf-message neuralab-cf-error">' + neuralabCF.errorMessage + '</div>');
                    
                    console.error('AJAX Error:', error);
                },
                complete: function() {
                    // Re-enable submit button
                    $submitBtn.prop('disabled', false).text(neuralabCF.submitText);
                }
            });
        });

        // Remove error/success messages on input
        $('.neuralab-contact-form input, .neuralab-contact-form textarea, .neuralab-contact-form select').on('input change', function() {
            $('.neuralab-cf-message').fadeOut(function() {
                $(this).remove();
            });
        });
    });

})(jQuery);

