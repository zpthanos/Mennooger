<?php
// includes/classes/class-pm-settings.php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class PM_Settings
 *
 * Registers and renders the General Settings (including Shortcodes) via WP Settings API.
 */
class PM_Settings {

    /**
     * Option group and page slug
     */
    const OPTION_GROUP = 'pm_general_settings';
    const OPTION_PAGE  = 'pm-settings-general';

    public function __construct() {
        // Register settings, sections, and fields
        add_action( 'admin_init', [ $this, 'register_settings' ] );
    }

    /**
     * Register all settings sections and fields
     */
    public function register_settings() {
        // Register individual settings
        $options = [
            // Currency & Locale
            'pm_currency_code',
            'pm_currency_symbol',
            'pm_currency_position',
            'pm_thousands_sep',
            'pm_decimal_sep',
            'pm_decimals',
            // Display & Listing
            'pm_listing_style',
            'pm_per_page',
            'pm_ajax_pagination',
            'pm_login_required',
            'pm_archive_slug',
            // Form & UX
            'pm_step_wizard',
            'pm_tooltips',
            'pm_client_validation',
            // Payment Gateways
            'pm_visa_api_key',
            'pm_visa_secret',
            'pm_visa_mode',
            'pm_viva_api_key',
            'pm_viva_secret',
            'pm_viva_mode',
            // CAPTCHA
            'pm_captcha_provider',
            'pm_captcha_site_key',
            'pm_captcha_secret_key',
            'pm_recaptcha_threshold',
            // Email Defaults
            'pm_email_from_name',
            'pm_email_from_address',
            'pm_email_reply_to',
            'pm_email_footer',
            // Logging & Debug
            'pm_enable_logging',
            'pm_log_retention',
            'pm_enable_debug',
            // Maintenance & Updates
            'pm_auto_migrate',
            'pm_show_updates',
            // Shortcodes
            'pm_sc_listing',
            'pm_sc_subscription',
            'pm_sc_single',
            'pm_sc_partner',
            'pm_sc_interest',
            'pm_sc_payment',
            'pm_sc_afm',
        ];
        foreach ( $options as $opt ) {
            register_setting( self::OPTION_GROUP, $opt );
        }

        // Sections
        add_settings_section(
            'pm_section_currency',
            __( 'Νόμισμα & Τοπικές Ρυθμίσεις', 'wc-pm' ),
            '__return_false',
            self::OPTION_PAGE
        );
        add_settings_section(
            'pm_section_display',
            __( 'Προβολή & Λίστα Πακέτων', 'wc-pm' ),
            '__return_false',
            self::OPTION_PAGE
        );
        add_settings_section(
            'pm_section_formux',
            __( 'Φόρμες & Εμπειρία Χρήστη', 'wc-pm' ),
            '__return_false',
            self::OPTION_PAGE
        );
        add_settings_section(
            'pm_section_gateways',
            __( 'Πύλες Πληρωμών', 'wc-pm' ),
            '__return_false',
            self::OPTION_PAGE
        );
        add_settings_section(
            'pm_section_captcha',
            __( 'CAPTCHA', 'wc-pm' ),
            '__return_false',
            self::OPTION_PAGE
        );
        add_settings_section(
            'pm_section_email',
            __( 'Προεπιλογές Email', 'wc-pm' ),
            '__return_false',
            self::OPTION_PAGE
        );
        add_settings_section(
            'pm_section_logging',
            __( 'Καταγραφή & Debug', 'wc-pm' ),
            '__return_false',
            self::OPTION_PAGE
        );
        add_settings_section(
            'pm_section_updates',
            __( 'Συντήρηση & Ενημερώσεις', 'wc-pm' ),
            '__return_false',
            self::OPTION_PAGE
        );
        add_settings_section(
            'pm_section_shortcodes',
            __( 'Shortcodes', 'wc-pm' ),
            '__return_false',
            self::OPTION_PAGE
        );

        // Fields: Currency
        add_settings_field(
            'pm_currency_code',
            __( 'Κωδικός Νομίσματος', 'wc-pm' ),
            [ $this, 'render_text_field' ],
            self::OPTION_PAGE,
            'pm_section_currency',
            [ 'option' => 'pm_currency_code', 'size' => 4 ]
        );
        add_settings_field(
            'pm_currency_symbol',
            __( 'Σύμβολο Νομίσματος', 'wc-pm' ),
            [ $this, 'render_text_field' ],
            self::OPTION_PAGE,
            'pm_section_currency',
            [ 'option' => 'pm_currency_symbol', 'size' => 4 ]
        );
        add_settings_field(
            'pm_currency_position',
            __( 'Θέση Символу', 'wc-pm' ),
            [ $this, 'render_radio_field' ],
            self::OPTION_PAGE,
            'pm_section_currency',
            [ 'option' => 'pm_currency_position', 'choices' => [ 'before' => __( 'Πριν το ποσό', 'wc-pm' ), 'after' => __( 'Μετά το ποσό', 'wc-pm' ) ] ]
        );
        add_settings_field(
            'pm_thousands_sep',
            __( 'Χιλιάδες Διαχωριστής', 'wc-pm' ),
            [ $this, 'render_text_field' ],
            self::OPTION_PAGE,
            'pm_section_currency',
            [ 'option' => 'pm_thousands_sep', 'size' => 2 ]
        );
        add_settings_field(
            'pm_decimal_sep',
            __( 'Δεκαδικός Διαχωριστής', 'wc-pm' ),
            [ $this, 'render_text_field' ],
            self::OPTION_PAGE,
            'pm_section_currency',
            [ 'option' => 'pm_decimal_sep', 'size' => 2 ]
        );
        add_settings_field(
            'pm_decimals',
            __( 'Αριθμός Δεκαδικών', 'wc-pm' ),
            [ $this, 'render_number_field' ],
            self::OPTION_PAGE,
            'pm_section_currency',
            [ 'option' => 'pm_decimals', 'min' => 0, 'max' => 4 ]
        );

        // Fields: Display & Listing
        add_settings_field(
            'pm_listing_style',
            __( 'Στυλ Λίστας', 'wc-pm' ),
            [ $this, 'render_select_field' ],
            self::OPTION_PAGE,
            'pm_section_display',
            [ 'option' => 'pm_listing_style', 'choices' => [ 'grid' => __( 'Πλέγμα', 'wc-pm' ), 'list' => __( 'Λίστα', 'wc-pm' ) ] ]
        );
        add_settings_field(
            'pm_per_page',
            __( 'Πακέτα ανά Σελίδα', 'wc-pm' ),
            [ $this, 'render_number_field' ],
            self::OPTION_PAGE,
            'pm_section_display',
            [ 'option' => 'pm_per_page', 'min' => 1, 'max' => 100 ]
        );
        add_settings_field(
            'pm_ajax_pagination',
            __( 'AJAX Πaginάρισμα', 'wc-pm' ),
            [ $this, 'render_checkbox_field' ],
            self::OPTION_PAGE,
            'pm_section_display',
            [ 'option' => 'pm_ajax_pagination' ]
        );
        add_settings_field(
            'pm_login_required',
            __( 'Απαιτεί Σύνδεση για Αγορά', 'wc-pm' ),
            [ $this, 'render_checkbox_field' ],
            self::OPTION_PAGE,
            'pm_section_display',
            [ 'option' => 'pm_login_required' ]
        );
        add_settings_field(
            'pm_archive_slug',
            __( 'Slug Αρχείου Πακέτων', 'wc-pm' ),
            [ $this, 'render_text_field' ],
            self::OPTION_PAGE,
            'pm_section_display',
            [ 'option' => 'pm_archive_slug', 'size' => 20 ]
        );

        // ... continue adding fields for each section similarly ...

        // Fields: Shortcodes
        add_settings_field(
            'pm_sc_listing',
            __( 'Shortcode Λίστας Πακέτων', 'wc-pm' ),
            [ $this, 'render_text_field' ],
            self::OPTION_PAGE,
            'pm_section_shortcodes',
            [ 'option' => 'pm_sc_listing', 'size' => 20 ]
        );
        add_settings_field(
            'pm_sc_subscription',
            __( 'Shortcode Συνδρομής', 'wc-pm' ),
            [ $this, 'render_text_field' ],
            self::OPTION_PAGE,
            'pm_section_shortcodes',
            [ 'option' => 'pm_sc_subscription', 'size' => 20 ]
        );
        add_settings_field(
            'pm_sc_single',
            __( 'Shortcode Μονής Προβολής Πακέτου', 'wc-pm' ),
            [ $this, 'render_text_field' ],
            self::OPTION_PAGE,
            'pm_section_shortcodes',
            [ 'option' => 'pm_sc_single', 'size' => 20 ]
        );
        add_settings_field(
            'pm_sc_partner',
            __( 'Shortcode Συνεργάτη', 'wc-pm' ),
            [ $this, 'render_text_field' ],
            self::OPTION_PAGE,
            'pm_section_shortcodes',
            [ 'option' => 'pm_sc_partner', 'size' => 20 ]
        );
        add_settings_field(
            'pm_sc_interest',
            __( 'Shortcode Ενδιαφέροντος', 'wc-pm' ),
            [ $this, 'render_text_field' ],
            self::OPTION_PAGE,
            'pm_section_shortcodes',
            [ 'option' => 'pm_sc_interest', 'size' => 20 ]
        );
        add_settings_field(
            'pm_sc_payment',
            __( 'Shortcode Πληρωμής', 'wc-pm' ),
            [ $this, 'render_text_field' ],
            self::OPTION_PAGE,
            'pm_section_shortcodes',
            [ 'option' => 'pm_sc_payment', 'size' => 20 ]
        );
        add_settings_field(
            'pm_sc_afm',
            __( 'Shortcode ΑΦΜ Αναζήτησης', 'wc-pm' ),
            [ $this, 'render_text_field' ],
            self::OPTION_PAGE,
            'pm_section_shortcodes',
            [ 'option' => 'pm_sc_afm', 'size' => 20 ]
        );
    }

    /**
     * Render a text input field
     */
    public function render_text_field( $args ) {
        $option = $args['option'];
        $size   = isset( $args['size'] ) ? $args['size'] : 40;
        $value  = esc_attr( get_option( $option ) );
        printf(
            '<input type="text" name="%1$s" value="%2$s" size="%3$d" />',
            esc_attr( $option ),
            $value,
            $size
        );
    }

    /**
     * Render a number input field
     */
    public function render_number_field( $args ) {
        $option = $args['option'];
        $min    = isset( $args['min'] ) ? intval( $args['min'] ) : '';
        $max    = isset( $args['max'] ) ? intval( $args['max'] ) : '';
        $value  = esc_attr( get_option( $option ) );
        printf(
            '<input type="number" name="%1$s" value="%2$s" min="%3$d" max="%4$d" />',
            esc_attr( $option ),
            $value,
            $min,
            $max
        );
    }

    /**
     * Render a checkbox field
     */
    public function render_checkbox_field( $args ) {
        $option = $args['option'];
        $checked = checked( 1, get_option( $option ), false );
        printf(
            '<input type="checkbox" name="%1$s" value="1" %2$s />',
            esc_attr( $option ),
            $checked
        );
    }

    /**
     * Render a radio group
     */
    public function render_radio_field( $args ) {
        $option  = $args['option'];
        $choices = $args['choices'];
        $current = get_option( $option );
        foreach ( $choices as $value => $label ) {
            $checked = checked( $value, $current, false );
            printf(
                '<label><input type="radio" name="%1$s" value="%2$s" %3$s /> %4$s</label><br>',
                esc_attr( $option ),
                esc_attr( $value ),
                $checked,
                esc_html( $label )
            );
        }
    }

    /**
     * Render a select dropdown
     */
    public function render_select_field( $args ) {
        $option  = $args['option'];
        $choices = $args['choices'];
        $current = get_option( $option );
        printf( '<select name="%1$s">', esc_attr( $option ) );
        foreach ( $choices as $value => $label ) {
            $selected = selected( $value, $current, false );
            printf(
                '<option value="%1$s" %2$s>%3$s</option>',
                esc_attr( $value ),
                $selected,
                esc_html( $label )
            );
        }
        echo '</select>';
    }
}

// Instantiate
new PM_Settings();
