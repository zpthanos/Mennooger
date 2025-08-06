<?php
// includes/class-pm-settings.php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class PM_Settings
 *
 * Registers the General Settings page and fields.
 */
class PM_Settings {

    const MENU_SLUG  = 'pm-settings-general';
    const OPTION_GROUP = 'pm_general_settings';

    public function __construct() {
        add_action( 'admin_menu',   [ $this, 'add_menu' ] );
        add_action( 'admin_init',   [ $this, 'register_settings' ] );
    }

    /**
     * Add “Settings” submenu under our Manager menu.
     */
    public function add_menu() {
        add_submenu_page(
            'pm-dashboard',
            __( 'Ρυθμίσεις', 'wc-pm' ),
            __( 'Ρυθμίσεις', 'wc-pm' ),
            'manage_options',
            self::MENU_SLUG,
            [ $this, 'render_page' ]
        );
    }

    /**
     * Register settings, sections, and fields.
     */
    public function register_settings() {
        // Register the option group
        register_setting( self::OPTION_GROUP, 'pm_currency_symbol', [
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => '€',
        ] );
        register_setting( self::OPTION_GROUP, 'pm_decimals', [
            'type'              => 'integer',
            'sanitize_callback' => 'absint',
            'default'           => 2,
        ] );
        register_setting( self::OPTION_GROUP, 'pm_currency_position', [
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => 'before',
        ] );
        register_setting( self::OPTION_GROUP, 'pm_listing_style', [
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => 'grid',
        ] );
        register_setting( self::OPTION_GROUP, 'pm_per_page', [
            'type'              => 'integer',
            'sanitize_callback' => 'absint',
            'default'           => 12,
        ] );

        // Shortcode names
        $shortcodes = [
            'listing'     => 'pm_listing',
            'single'      => 'pm_single_package',
            'partner'     => 'pm_partner_form',
            'interest'    => 'pm_interest_form',
            'payment'     => 'pm_payment_form',
            'afm_lookup'  => 'pm_afm_lookup',
            'wizard'      => 'pm_subscription_wizard',
        ];
        foreach ( $shortcodes as $key => $default ) {
            register_setting( self::OPTION_GROUP, "pm_shortcode_{$key}", [
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default'           => $default,
            ] );
        }

        // Section
        add_settings_section(
            'pm_section_general',
            __( 'Ρυθμίσεις Πληρωμής & Λίστας', 'wc-pm' ),
            null,
            self::MENU_SLUG
        );

        // Fields
        add_settings_field(
            'pm_currency_symbol',
            __( 'Σύμβολο Νομίσματος', 'wc-pm' ),
            [ $this, 'render_currency_symbol' ],
            self::MENU_SLUG,
            'pm_section_general'
        );
        add_settings_field(
            'pm_decimals',
            __( 'Αριθμός δεκαδικών', 'wc-pm' ),
            [ $this, 'render_decimals' ],
            self::MENU_SLUG,
            'pm_section_general'
        );
        add_settings_field(
            'pm_currency_position',
            __( 'Θέση Νομίσματος', 'wc-pm' ),
            [ $this, 'render_currency_position' ],
            self::MENU_SLUG,
            'pm_section_general'
        );
        add_settings_field(
            'pm_listing_style',
            __( 'Στυλ Καταλόγου', 'wc-pm' ),
            [ $this, 'render_listing_style' ],
            self::MENU_SLUG,
            'pm_section_general'
        );
        add_settings_field(
            'pm_per_page',
            __( 'Αντικείμενα ανά σελίδα', 'wc-pm' ),
            [ $this, 'render_per_page' ],
            self::MENU_SLUG,
            'pm_section_general'
        );

        // Shortcode fields section
        add_settings_section(
            'pm_section_shortcodes',
            __( 'Shortcodes', 'wc-pm' ),
            null,
            self::MENU_SLUG
        );
        foreach ( $shortcodes as $key => $default ) {
            add_settings_field(
                "pm_shortcode_{$key}",
                sprintf( __( 'Shortcode %s', 'wc-pm' ), $key ),
                function() use ( $key ) {
                    $opt = get_option( "pm_shortcode_{$key}" );
                    printf(
                        '<input type="text" name="pm_shortcode_%1$s" value="%2$s" class="regular-text" />',
                        esc_attr( $key ),
                        esc_attr( $opt )
                    );
                },
                self::MENU_SLUG,
                'pm_section_shortcodes'
            );
        }
    }

    /* Render callbacks */

    public function render_currency_symbol() {
        $val = get_option( 'pm_currency_symbol', '€' );
        printf(
            '<input type="text" name="pm_currency_symbol" value="%s" class="small-text" />',
            esc_attr( $val )
        );
    }

    public function render_decimals() {
        $val = get_option( 'pm_decimals', 2 );
        printf(
            '<input type="number" name="pm_decimals" value="%d" min="0" class="small-text" />',
            intval( $val )
        );
    }

    public function render_currency_position() {
        $val = get_option( 'pm_currency_position', 'before' );
        ?>
        <select name="pm_currency_position">
            <option value="before" <?php selected( $val, 'before' ); ?>><?php esc_html_e( 'Πριν από το ποσό', 'wc-pm' ); ?></option>
            <option value="after"  <?php selected( $val, 'after' );  ?>><?php esc_html_e( 'Μετά το ποσό', 'wc-pm' ); ?></option>
        </select>
        <?php
    }

    public function render_listing_style() {
        $val = get_option( 'pm_listing_style', 'grid' );
        ?>
        <select name="pm_listing_style">
            <option value="grid" <?php selected( $val, 'grid' ); ?>><?php esc_html_e( 'Grid', 'wc-pm' ); ?></option>
            <option value="list" <?php selected( $val, 'list' ); ?>><?php esc_html_e( 'List', 'wc-pm' ); ?></option>
        </select>
        <?php
    }

    public function render_per_page() {
        $val = get_option( 'pm_per_page', 12 );
        printf(
            '<input type="number" name="pm_per_page" value="%d" min="1" class="small-text" />',
            intval( $val )
        );
    }

    /**
     * Render the settings page HTML.
     */
    public function render_page() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Ρυθμίσεις', 'wc-pm' ); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields( self::OPTION_GROUP );
                do_settings_sections( self::MENU_SLUG );
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }
}

