<?php
/**
 * Template: AFM Lookup Form
 * Path: templates/frontend/form-afm-lookup.php
 *
 * Renders the form where users enter their ΑΦΜ to retrieve a payment link.
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<form id="pm-afm-lookup-form" class="pm-form" method="post" novalidate>
    <?php wp_nonce_field( 'pm_afm_nonce', 'nonce' ); ?>

    <div class="pm-form-field pm-form-field-afm">
        <label for="afm"><?php esc_html_e( 'ΑΦΜ', 'wc-pm' ); ?> <span class="required">*</span></label>
        <input
            type="text"
            name="afm"
            id="afm"
            placeholder="<?php esc_attr_e( 'π.χ 999123456', 'wc-pm' ); ?>"
            required
        />
    </div>

    <p>
        <button type="submit" class="button pm-submit-button">
            <?php esc_html_e( 'Αποστολή Συνδέσμου Πληρωμής', 'wc-pm' ); ?>
        </button>
    </p>
</form>
