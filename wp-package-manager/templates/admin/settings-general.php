<?php
// templates/admin/settings-general.php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="wrap">
    <h1><?php esc_html_e( 'Ρυθμίσεις', 'wc-pm' ); ?></h1>
    <form method="post" action="options.php">
        <?php
        // Output security fields for the registered setting “pm_general_settings”
        settings_fields( 'pm_general_settings' );
        // Output all sections and fields registered to this settings page
        do_settings_sections( 'pm-settings-general' );
        // Submit button
        submit_button();
        ?>
    </form>
</div>
