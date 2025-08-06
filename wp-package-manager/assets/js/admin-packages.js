// assets/js/admin-packages.js
jQuery(function($) {
    var cfg = window.PM_Packages_Ajax;
    if (!cfg) {
        return;
    }

    // Handler: Create Package
    $('#pm-add-package-form').on('submit', function(e) {
        e.preventDefault();
        var data = $(this).serializeArray();
        data.push({ name: 'action', value: cfg.create_action });
        data.push({ name: 'nonce', value: cfg.nonce });
        $.post(cfg.ajax_url, data, function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert(response.data.message || 'Error creating package.');
            }
        });
    });

    // Handler: Edit Package (open modal and populate)
    $('.pm-edit-package').on('click', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        // Fetch package data via AJAX
        $.get(cfg.ajax_url, { action: cfg.fetch_action, package_id: id, nonce: cfg.nonce }, function(res) {
            if (res.success) {
                var pkg = res.data;
                var $form = $('#pm-edit-package-form');
                $.each(pkg, function(key, val) {
                    $form.find('[name="' + key + '"]').val(val);
                });
                $form.find('[name="package_id"]').val(id);
                $('#pm-edit-package-modal').show();
            } else {
                alert(res.data.message || 'Error fetching package.');
            }
        });
    });

    // Handler: Submit Edit Package
    $('#pm-edit-package-form').on('submit', function(e) {
        e.preventDefault();
        var data = $(this).serializeArray();
        data.push({ name: 'action', value: cfg.update_action });
        data.push({ name: 'nonce', value: cfg.nonce });
        $.post(cfg.ajax_url, data, function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert(response.data.message || 'Error updating package.');
            }
        });
    });

    // Handler: Delete Package
    $('.pm-delete-package').on('click', function(e) {
        e.preventDefault();
        if (!confirm('Are you sure you want to delete this package?')) {
            return;
        }
        var id = $(this).data('id');
        $.post(cfg.ajax_url, { action: cfg.delete_action, package_id: id, nonce: cfg.nonce }, function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert(response.data.message || 'Error deleting package.');
            }
        });
    });
});
