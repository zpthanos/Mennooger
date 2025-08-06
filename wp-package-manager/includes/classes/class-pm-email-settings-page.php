<?php
// includes/classes/class-pm-email-settings-page.php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class PM_Email_Settings_Page
 *
 * Registers the Email Templates settings page and fields.
 */
class PM_Email_Settings_Page {
    /** Settings group and page */
    const OPTION_GROUP = 'wc_pm_email_templates';
    const MENU_SLUG    = 'pm-email-templates';

    public function __construct() {
        add_action( 'admin_init', [ $this, 'register_settings' ] );
    }

    /** Register settings for each email template */
    public function register_settings() {
        $templates = PM_Email_Templates::get_available_templates();
        // Register defaults for From/Reply
        register_setting( self::OPTION_GROUP, 'pm_email_from_name' );
        register_setting( self::OPTION_GROUP, 'pm_email_from_address' );
        register_setting( self::OPTION_GROUP, 'pm_email_reply_to' );

        // Register subject and body for each template
        foreach ( $templates as $key ) {
            register_setting( self::OPTION_GROUP, 'pm_email_subject_' . $key );
            register_setting( self::OPTION_GROUP, 'pm_email_tpl_'     . $key );
        }
    }

    /** Render the settings page content */
    public function render_page() {
        $templates = PM_Email_Templates::get_available_templates();
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Email Templates', 'wc-pm' ); ?></h1>
            <form method="post" action="options.php">
                <?php settings_fields( self::OPTION_GROUP ); ?>
                <?php foreach ( $templates as $key ) : 
                    $sub_opt = 'pm_email_subject_' . $key;
                    $subject = get_option( $sub_opt, '' );
                ?>
                    <h2><?php echo esc_html( ucwords( str_replace( '-', ' ', $key ) ) ); ?></h2>
                    <table class="form-table" role="presentation">
                        <tr>
                            <th scope="row">
                                <label for="<?php echo esc_attr( $sub_opt ); ?>">
                                    <?php esc_html_e( 'Subject', 'wc-pm' ); ?>
                                </label>
                            </th>
                            <td>
                                <input
                                    name="<?php echo esc_attr( $sub_opt ); ?>"
                                    type="text"
                                    id="<?php echo esc_attr( $sub_opt ); ?>"
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
        <?php
    }
}

// Instantiate and hook into admin_menu to render page
$email_settings_page = new PM_Email_Settings_Page();
add_action( 'admin_menu', function() use ( $email_settings_page ) {
    // Ensure our submenu uses this render callback
    remove_submenu_page( 'pm-dashboard', 'pm-email-templates' );
    add_submenu_page(
        'pm-dashboard',
        __( 'Email Templates', 'wc-pm' ),
        __( 'Email Templates', 'wc-pm' ),
        'manage_options',
        'pm-email-templates',
        [ $email_settings_page, 'render_page' ]
    );
});
