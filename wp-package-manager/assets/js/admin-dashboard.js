// assets/js/admin-dashboard.js
jQuery(function($) {
    // PM Admin Dashboard JS loaded.
    // Place admin-specific behaviors here, e.g., handling AJAX actions,
    // toggling UI elements, or initializing charts/widgets on admin pages.

    // Example: clear dashboard stats cache via AJAX
    $('#pm-clear-stats').on('click', function(e) {
        e.preventDefault();
        var $btn = $(this).prop('disabled', true).text('Καθαρισμός...');
        $.post(ajaxurl, {
            action: 'pm_clear_dashboard_cache',
            nonce: PM_Dashboard_Ajax.nonce
        }, function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert('Σφάλμα κατά τον καθαρισμό της προσωρινής μνήμης.');
                $btn.prop('disabled', false).text('Καθαρισμός Στατιστικών');
            }
        });
    });
});
