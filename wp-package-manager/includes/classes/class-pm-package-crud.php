<?php
// includes/classes/class-pm-package-crud.php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class PM_Package_CRUD
 *
 * Manages package CRUD in the admin and renders the front-end package archive.
 */
class PM_Package_CRUD {

    /** Table name */
    protected $table;

    public function __construct() {
        global $wpdb;
        $this->table = $wpdb->prefix . 'pm_packages';

        // Admin hooks
        if ( is_admin() ) {
            add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ] );
            add_action( 'wp_ajax_pm_create_package', [ $this, 'ajax_create_package' ] );
            add_action( 'wp_ajax_pm_update_package', [ $this, 'ajax_update_package' ] );
            add_action( 'wp_ajax_pm_delete_package', [ $this, 'ajax_delete_package' ] );
        }

        // Front-end shortcode callback is registered in PM_Shortcodes: render_package_archive()
    }

    /**
     * Enqueue admin assets for the Packages page.
     */
    public function enqueue_admin_assets() {
        $screen = get_current_screen();
        if ( isset( $screen->id ) && strpos( $screen->id, 'pm-packages' ) !== false ) {
            wp_enqueue_style( 'wc-pm-admin', WC_PM_URL . 'assets/css/admin.css', [], WC_PM_VERSION );
            wp_enqueue_script( 'wc-pm-packages', WC_PM_URL . 'assets/js/admin-dashboard.js', [ 'jquery' ], WC_PM_VERSION, true );
            wp_localize_script( 'wc-pm-packages', 'PM_Packages_Ajax', [
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce'    => wp_create_nonce( 'pm_packages_nonce' ),
            ] );
        }
    }

    /**
     * AJAX handler: create a new package.
     */
    public function ajax_create_package() {
        check_ajax_referer( 'pm_packages_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( __( 'Unauthorized', 'wc-pm' ), 403 );
        }

        $data = isset( $_POST['package'] ) ? wp_unslash( $_POST['package'] ) : [];
        $title       = sanitize_text_field( $data['title'] );
        $description = wp_kses_post( $data['description'] );
        $price       = floatval( $data['price'] );
        $status      = in_array( $data['status'], [ 'published', 'draft' ], true ) ? $data['status'] : 'draft';

        global $wpdb;
        $now = current_time( 'mysql' );
        $inserted = $wpdb->insert(
            $this->table,
            [
                'title'       => $title,
                'description' => $description,
                'price'       => $price,
                'status'      => $status,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [ '%s', '%s', '%f', '%s', '%s', '%s' ]
        );

        if ( $inserted ) {
            wp_send_json_success( [ 'id' => $wpdb->insert_id ] );
        }
        wp_send_json_error( $wpdb->last_error );
    }

    /**
     * AJAX handler: update an existing package.
     */
    public function ajax_update_package() {
        check_ajax_referer( 'pm_packages_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( __( 'Unauthorized', 'wc-pm' ), 403 );
        }

        $data = isset( $_POST['package'] ) ? wp_unslash( $_POST['package'] ) : [];
        $id          = intval( $data['id'] );
        $title       = sanitize_text_field( $data['title'] );
        $description = wp_kses_post( $data['description'] );
        $price       = floatval( $data['price'] );
        $status      = in_array( $data['status'], [ 'published', 'draft' ], true ) ? $data['status'] : 'draft';
        $now         = current_time( 'mysql' );

        global $wpdb;
        $updated = $wpdb->update(
            $this->table,
            [
                'title'       => $title,
                'description' => $description,
                'price'       => $price,
                'status'      => $status,
                'updated_at'  => $now,
            ],
            [ 'id' => $id ],
            [ '%s', '%s', '%f', '%s', '%s' ],
            [ '%d' ]
        );

        if ( false !== $updated ) {
            wp_send_json_success();
        }
        wp_send_json_error( $wpdb->last_error );
    }

    /**
     * AJAX handler: delete a package.
     */
    public function ajax_delete_package() {
        check_ajax_referer( 'pm_packages_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( __( 'Unauthorized', 'wc-pm' ), 403 );
        }

        $id = intval( $_POST['id'] );
        global $wpdb;
        $deleted = $wpdb->delete( $this->table, [ 'id' => $id ], [ '%d' ] );

        if ( $deleted ) {
            wp_send_json_success();
        }
        wp_send_json_error( $wpdb->last_error );
    }

    /**
     * Front-end: render the package archive grid or list.
     *
     * @param array $atts Shortcode attributes: 'style' => 'grid'|'list', 'per_page' => int
     * @return string HTML output
     */
    public function render_package_archive( $atts ) {
        $atts = shortcode_atts( [
            'style'    => get_option( 'pm_listing_style', 'grid' ),
            'per_page' => intval( get_option( 'pm_per_page', 12 ) ),
            'page'     => max( 1, get_query_var( 'paged' ) ),
        ], $atts, 'pm_listing' );

        global $wpdb;
        $offset = ( $atts['page'] - 1 ) * $atts['per_page'];
        $packages = $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM {$this->table} WHERE status = %s ORDER BY created_at DESC LIMIT %d OFFSET %d",
            'published', $atts['per_page'], $offset
        ), ARRAY_A );

        ob_start();
        include WC_PM_PATH . 'templates/frontend/package-archive.php';
        return ob_get_clean();
    }
}

// Instantiate
new PM_Package_CRUD();
