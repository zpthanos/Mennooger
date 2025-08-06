<?php
// includes/class-pm-shortcodes.php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class PM_Shortcodes
 *
 * Registers and handles plugin shortcodes, reading names from settings.
 */
class PM_Shortcodes {

    /** Mapping of shortcode keys to handler methods */
    protected $map = [
        'listing'    => 'shortcode_listing',
        'single'     => 'shortcode_single',
        'partner'    => 'shortcode_partner',
        'interest'   => 'shortcode_interest',
        'payment'    => 'shortcode_payment',
        'afm_lookup' => 'shortcode_afm_lookup',
        'wizard'     => 'shortcode_subscription_wizard',
    ];

    public function __construct() {
        add_action( 'init', [ $this, 'register_shortcodes' ] );
    }

    /**
     * Register all shortcodes based on settings or defaults.
     */
    public function register_shortcodes() {
        foreach ( $this->map as $key => $method ) {
            $option = get_option( 'pm_shortcode_' . $key, '' );
            $tag    = $option ?: 'pm_' . $key;
            add_shortcode( $tag, [ $this, $method ] );
        }
    }

    /**
     * Shortcode: package listing archive
     */
    public function shortcode_listing( $atts ) {
        global $wpdb;
        $atts = shortcode_atts( [
            'style'    => get_option( 'pm_listing_style', 'grid' ),
            'per_page' => intval( get_option( 'pm_per_page', 12 ) ),
            'page'     => max( 1, get_query_var( 'paged', 1 ) ),
        ], $atts, 'pm_listing' );

        $offset   = ( $atts['page'] - 1 ) * $atts['per_page'];
        $table    = $wpdb->prefix . 'pm_packages';
        $packages = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$table} WHERE status = %s ORDER BY created_at DESC LIMIT %d, %d",
                'published', $offset, $atts['per_page']
            ),
            ARRAY_A
        );

        ob_start();
        include WC_PM_PATH . 'templates/frontend/package-archive.php';
        return ob_get_clean();
    }

    /**
     * Shortcode: single package view
     */
    public function shortcode_single( $atts ) {
        $renderer = new PM_Form_Renderer();
        return $renderer->render_single_package( $atts );
    }

    /** Shortcode: partner form */
    public function shortcode_partner( $atts ) {
        $form = new PM_Partner_Form();
        return $form->render_partner_form( $atts );
    }

    /** Shortcode: interest form */
    public function shortcode_interest( $atts ) {
        $form = new PM_Interest_Form();
        return $form->render_interest_form( $atts );
    }

    /** Shortcode: payment form */
    public function shortcode_payment( $atts ) {
        $form = new PM_Payment_Form();
        return $form->render_payment_form( $atts );
    }

    /** Shortcode: AFM lookup form */
    public function shortcode_afm_lookup( $atts ) {
        $lookup = new PM_AFM_Lookup();
        return $lookup->render_afm_lookup_form( $atts );
    }

    /** Shortcode: subscription wizard */
    public function shortcode_subscription_wizard( $atts ) {
        $wizard = new PM_Subscription_Wizard();
        return $wizard->render_subscription_wizard( $atts );
    }
}
