// assets/js/frontend-wizard.js
jQuery(document).ready(function($) {
    var cfg = window.PM_Subscription_Ajax;
    var $wizard = $('#pm-subscription-wizard');
    var $steps = $wizard.find('.pm-wizard-step');
    var $progressItems = $wizard.find('.pm-wizard-progress li');
    var $form = $('#pm-wizard-form');
    var currentStep = 0;

    if ( ! cfg ) {
        return;
    }

    // Fallback messages; you can override via PM_Subscription_Ajax
    var successMsg      = cfg.success_message || 'Η συνδρομή καταχωρήθηκε με επιτυχία!';
    var submitErrorMsg  = cfg.error_message || 'Σφάλμα κατά την υποβολή.';
    var requestFailMsg  = cfg.fail_message || 'Δε μπόρεσε να ολοκληρωθεί το αίτημα.';

    // Insert a container for messages
    var $msgContainer = $('<div class="pm-form-message"></div>').prependTo($wizard);

    function showStep(index) {
        $steps.hide().eq(index).show();
        $progressItems.removeClass('active').eq(index).addClass('active');
        $wizard.find('.pm-wizard-prev').prop('disabled', index === 0);
        var isLast = index === $steps.length - 1;
        $wizard.find('.pm-wizard-next').toggle(!isLast);
        $wizard.find('.pm-wizard-submit').toggle(isLast);
    }

    // Next / Previous handlers
    $wizard.on('click', '.pm-wizard-next', function() {
        if ( currentStep < $steps.length - 1 ) {
            currentStep++;
            showStep(currentStep);
        }
    });
    $wizard.on('click', '.pm-wizard-prev', function() {
        if ( currentStep > 0 ) {
            currentStep--;
            showStep(currentStep);
        }
    });

    // AJAX submission on final step
    $form.on('submit', function(e) {
        e.preventDefault();
        $msgContainer.removeClass('pm-form-error pm-form-success').text('');
        var $submit = $form.find('.pm-wizard-submit');
        $submit.prop('disabled', true).addClass('disabled');

        var formData = new FormData(this);
        formData.append('action', cfg.action);
        formData.append('nonce', cfg.nonce);

        $.ajax({
            url: cfg.ajax_url,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false
        }).done(function(response) {
            if ( response.success ) {
                $msgContainer
                    .removeClass('pm-form-error')
                    .addClass('pm-form-success')
                    .text( response.data.message || successMsg );
                $form[0].reset();
            } else {
                var err = ( response.data && response.data.message ) || submitErrorMsg;
                $msgContainer
                    .removeClass('pm-form-success')
                    .addClass('pm-form-error')
                    .text( err );
            }
        }).fail(function() {
            $msgContainer
                .removeClass('pm-form-success')
                .addClass('pm-form-error')
                .text( requestFailMsg );
        }).always(function() {
            $submit.prop('disabled', false).removeClass('disabled');
        });
    });

    // Initialize the first step
    showStep(currentStep);
});
