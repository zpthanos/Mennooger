// assets/js/frontend-forms.js
jQuery(document).ready(function($) {
    var formConfigs = {
        'pm-partner-form': window.PM_Partner_Ajax,
        'pm-interest-form': window.PM_Interest_Ajax,
        'pm-payment-form': window.PM_Payment_Ajax,
        'pm-afm-lookup-form': window.PM_AFM_Ajax
    };

    $.each(formConfigs, function(formId, cfg) {
        var $form = $('#' + formId);
        if (!$form.length || !cfg) {
            return;
        }

        // Create a container for messages
        var $msgContainer = $('<div class="pm-form-message"></div>').prependTo($form);

        $form.on('submit', function(event) {
            event.preventDefault();
            $msgContainer.empty();
            var $submit = $form.find('button[type="submit"], input[type="submit"]');
            $submit.prop('disabled', true).addClass('disabled');

            // Gather data
            var data = {};
            $form.find(':input[name]').each(function() {
                var $input = $(this);
                var name = $input.attr('name');
                data[name] = $input.val();
            });
            data.action = cfg.action;
            data.nonce  = cfg.nonce;

            // AJAX POST
            $.post(cfg.ajax_url, data, function(response) {
                if (response.success) {
                    $msgContainer
                        .removeClass('pm-form-error')
                        .addClass('pm-form-success')
                        .text(response.data.message || response.data.submission_id || 'Επιτυχία!');
                    // Optionally reset form
                    $form[0].reset();
                } else {
                    var err = (response.data && response.data.message) || 'Σφάλμα κατά την υποβολή.';
                    $msgContainer
                        .removeClass('pm-form-success')
                        .addClass('pm-form-error')
                        .text(err);
                }
            }).fail(function() {
                $msgContainer
                    .removeClass('pm-form-success')
                    .addClass('pm-form-error')
                    .text('Δε μπόρεσε να ολοκληρωθεί το αίτημα.');
            }).always(function() {
                $submit.prop('disabled', false).removeClass('disabled');
            });
        });
    });
});
