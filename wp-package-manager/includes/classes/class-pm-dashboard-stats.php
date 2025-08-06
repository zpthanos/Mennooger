<?php
// includes/classes/class-pm-dashboard-stats.php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class PM_Dashboard_Stats
 *
 * Computes and caches dashboard metrics: KPIs, sales over time,
 * package popularity, and recent activity logs.
 */
class PM_Dashboard_Stats {

    /** Transient key and expiration (12 hours) */
    const TRANSIENT_KEY = 'pm_dashboard_stats';
    const TRANSIENT_EXP = 12 * HOUR_IN_SECONDS;

    public function __construct() {
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
    }

    /**
     * Enqueue Chart.js and plugin dashboard scripts/styles on our Dashboard page.
     */
    public function enqueue_assets() {
        $screen = get_current_screen();
        if (
            isset( $screen->parent_file ) &&
            $screen->parent_file === 'admin.php' &&
            $screen->id === 'pm-dashboard'
        ) {
            wp_enqueue_script( 'chartjs', 'https://cdn.jsdelivr.net/npm/chart.js', [], '3.9.1', true );
            wp_enqueue_script( 'wc-pm-dashboard', WC_PM_URL . 'assets/js/dashboard-stats.js', [ 'chartjs', 'jquery' ], WC_PM_VERSION, true );
            wp_enqueue_style( 'wc-pm-admin', WC_PM_URL . 'assets/css/admin.css', [], WC_PM_VERSION );
            wp_localize_script( 'wc-pm-dashboard', 'PM_Dashboard_Data', $this->get_all_stats() );
        }
    }

    /**
     * Retrieve all stats, from cache or freshly computed.
     *
     * @return array
     */
    public function get_all_stats() {
        $stats = get_transient( self::TRANSIENT_KEY );
        if ( false === $stats ) {
            $stats = [
                'kpis'       => $this->get_kpis(),
                'sales_time' => $this->get_sales_over_time(),
                'popularity' => $this->get_package_popularity(),
                'recent'     => $this->get_recent_activity(),
            ];
            set_transient( self::TRANSIENT_KEY, $stats, self::TRANSIENT_EXP );
        }
        return $stats;
    }

    /**
     * Clear the cached statistics.
     */
    public function clear_cache() {
        delete_transient( self::TRANSIENT_KEY );
    }

    /**
     * Compute KPI cards: total successful sales, total revenue,
     * total refunds issued, and pending payments count.
     *
     * @return array
     */
    protected function get_kpis() {
        global $wpdb;
        $p = $wpdb->prefix . 'pm_payments';

        $total_sales      = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$p} WHERE status = 'success'" );
        $total_revenue    = (float) $wpdb->get_var( "SELECT SUM(amount) FROM {$p} WHERE status = 'success'" );
        $total_refunds    = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$p} WHERE status = 'refunded'" );
        $pending_payments = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$p} WHERE status = 'pending'" );

        return [
            'total_sales'      => $total_sales,
            'total_revenue'    => $total_revenue,
            'total_refunds'    => $total_refunds,
            'pending_payments' => $pending_payments,
        ];
    }

    /**
     * Get daily sales counts for the last 30 days.
     *
     * @return array [ 'YYYY-MM-DD' => count, ... ]
     */
    protected function get_sales_over_time() {
        global $wpdb;
        $p = $wpdb->prefix . 'pm_payments';

        $rows = $wpdb->get_results(
            "SELECT DATE(created_at) AS day, COUNT(*) AS count
             FROM {$p}
             WHERE status = 'success'
               AND created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
             GROUP BY DATE(created_at)",
            ARRAY_A
        );

        $data = [];
        foreach ( $rows as $row ) {
            $data[ $row['day'] ] = (int) $row['count'];
        }
        return $data;
    }

    /**
     * Determine top 5 subscription types by submission count.
     *
     * @return array [ ['label' => string, 'count' => int], ... ]
     */
    protected function get_package_popularity() {
        global $wpdb;
        $s = $wpdb->prefix . 'pm_submissions';

        $rows = $wpdb->get_results(
            "SELECT data, COUNT(*) AS total
             FROM {$s}
             WHERE type = 'subscription'
             GROUP BY data
             ORDER BY total DESC
             LIMIT 5",
            ARRAY_A
        );

        $popularity = [];
        foreach ( $rows as $row ) {
            $meta = json_decode( $row['data'], true );
            $label = isset( $meta['subscription_type'] ) ? $meta['subscription_type'] : __( 'Άγνωστο', 'wc-pm' );
            $popularity[] = [
                'label' => $label,
                'count' => (int) $row['total'],
            ];
        }
        return $popularity;
    }

    /**
     * Fetch the 10 most recent log entries.
     *
     * @return array of DB rows
     */
    protected function get_recent_activity() {
        global $wpdb;
        $l = $wpdb->prefix . 'pm_logs';

        return $wpdb->get_results(
            "SELECT * FROM {$l} ORDER BY timestamp DESC LIMIT 10",
            ARRAY_A
        );
    }
}

