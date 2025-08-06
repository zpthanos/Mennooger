<?php
// templates/admin/settings-emails.php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$templates = PM_Email_Templates::get_available_templates();
?>
<div class="wrap">
    <h1><?php esc_html_e( 'Email Templates', 'wc-pm' ); ?></h1>
    <form method="post" action="options.php">
        <?php settings_fields( 'wc_pm_email_templates' ); ?>

        <?php foreach ( $templates as $key ) : 
            $subject_opt = 'pm_email_subject_' . $key;
            $subject     = get_option( $subject_opt, '' );
        ?>
            <h2><?php echo esc_html( ucwords( str_replace( '-', ' ', $key ) ) ); ?></h2>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row">
                        <label for="<?php echo esc_attr( $subject_opt ); ?>">
                            <?php esc_html_e( 'Subject', 'wc-pm' ); ?>
                        </label>
                    </th>
                    <td>
                        <input
                            name="<?php echo esc_attr( $subject_opt ); ?>"
                            type="text"
                            id="<?php echo esc_attr( $subject_opt ); ?>"
                            value="<?php echo esc_attr( $subject ); ?>"
                            class="regular-text"
                        />
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label><?php esc_html_e( 'Body (HTML)', 'wc-pm' ); ?></label>
                    </th>
                    <td>
                        <?php
                        $body = get_option( 'pm_email_tpl_' . $key, '' );
                        wp_editor(
                            $body,
                            'pm_email_tpl_' . $key,
                            [
                                'textarea_name' => 'pm_email_tpl_' . $key,
                                'textarea_rows' => 10,
                                'media_buttons' => false,
                            ]
                        );
                        ?>
                    </td>
                </tr>
            </table>
        <?php endforeach; ?>

        <?php submit_button(); ?>
    </form>
</div>
