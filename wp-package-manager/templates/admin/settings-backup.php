<?php
// templates/admin/settings-backup.php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="wrap">
    <h1><?php esc_html_e( 'Backup & Export', 'wc-pm' ); ?></h1>

    <h2><?php esc_html_e( 'Εξαγωγή Δεδομένων', 'wc-pm' ); ?></h2>
    <p><?php esc_html_e( 'Εξάγετε τις βάσεις δεδομένων σε CSV ή JSON:', 'wc-pm' ); ?></p>
    <p>
        <a href="<?php echo esc_url( admin_url( 'admin-post.php?action=pm_export&type=packages' ) ); ?>" class="button">
            <?php esc_html_e( 'Εξαγωγή Πακέτων (CSV)', 'wc-pm' ); ?>
        </a>
        <a href="<?php echo esc_url( admin_url( 'admin-post.php?action=pm_export&type=submissions' ) ); ?>" class="button">
            <?php esc_html_e( 'Εξαγωγή Υποβολών (CSV)', 'wc-pm' ); ?>
        </a>
        <a href="<?php echo esc_url( admin_url( 'admin-post.php?action=pm_export&type=payments' ) ); ?>" class="button">
            <?php esc_html_e( 'Εξαγωγή Πληρωμών (CSV)', 'wc-pm' ); ?>
        </a>
    </p>

    <h2><?php esc_html_e( 'Επαναφορά/Εισαγωγή Δεδομένων', 'wc-pm' ); ?></h2>
    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php?action=pm_import' ) ); ?>" enctype="multipart/form-data">
        <?php wp_nonce_field( 'pm_import_nonce', 'nonce' ); ?>
        <table class="form-table">
            <tr>
                <th scope="row"><label for="import_type"><?php esc_html_e( 'Τύπος Δεδομένων', 'wc-pm' ); ?></label></th>
                <td>
                    <select name="import_type" id="import_type">
                        <option value="packages"><?php esc_html_e( 'Πακέτα', 'wc-pm' ); ?></option>
                        <option value="submissions"><?php esc_html_e( 'Υποβολές', 'wc-pm' ); ?></option>
                        <option value="payments"><?php esc_html_e( 'Πληρωμές', 'wc-pm' ); ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="import_file"><?php esc_html_e( 'Αρχείο CSV/JSON', 'wc-pm' ); ?></label></th>
                <td><input type="file" name="import_file" id="import_file" accept=".csv,.json" required /></td>
            </tr>
        </table>
        <?php submit_button( __( 'Εισαγωγή', 'wc-pm' ) ); ?>
    </form>
</div>
