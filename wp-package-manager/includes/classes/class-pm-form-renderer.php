<?php
// includes/classes/class-pm-form-renderer.php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class PM_Form_Renderer
 *
 * Renders single‐package views on the front end.
 */
class PM_Form_Renderer {

    /** DB table for packages */
    protected $table;

    public function __construct() {
        global $wpdb;
        $this->table = $wpdb->prefix . 'pm_packages';

        // Enqueue frontend assets when shortcode is present
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );
    }

    /**
     * Enqueue frontend CSS/JS needed for single‐package display.
     */
    public function enqueue_assets() {
        // We enqueue globally; you may refine to only when shortcode is on page
        wp_enqueue_style( 'wc-pm-frontend', WC_PM_URL . 'assets/css/frontend.css', [], WC_PM_VERSION );
        wp_enqueue_script( 'wc-pm-forms', WC_PM_URL . 'assets/js/frontend-forms.js', [ 'jquery' ], WC_PM_VERSION, true );
    }

    /**
     * Shortcode callback: display a single package and a connect form.
     *
     * Usage: [pm_single_package id="123"]
     *
     * @param array $atts {
     *     @type int    $id   Package ID.
     *     @type string $slug Package slug (optional, not yet implemented).
     * }
     * @return string HTML
     */
    public function render_single_package( $atts ) {
        $atts = shortcode_atts( [
            'id'   => 0,
            'slug' => '',
        ], $atts, 'pm_single_package' );

        global $wpdb;
        $package = null;

        if ( ! empty( $atts['id'] ) ) {
            $package = $wpdb->get_row( $wpdb->prepare(
                "SELECT * FROM {$this->table} WHERE id = %d AND status = %s",
                intval( $atts['id'] ), 'published'
            ), ARRAY_A );
        }
        // TODO: fetch by slug if implemented

        if ( ! $package ) {
            return '<p>' . esc_html__( 'Το πακέτο δεν βρέθηκε.', 'wc-pm' ) . '</p>';
        }

        // Make $package available to template
        ob_start();
        include WC_PM_PATH . 'templates/frontend/single-package.php';
        return ob_get_clean();
    }
}

// Instantiate
new PM_Form_Renderer();
