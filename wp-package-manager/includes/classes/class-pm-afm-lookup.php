<?php
// includes/classes/class-pm-afm-lookup.php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class PM_AFM_Lookup
 *
 * Renders and processes the ΑΦΜ lookup form:
 * - If payment is due, sends payment-link emails to user & admin
 * - Otherwise, notifies user of no due payments
 */
class PM_AFM_Lookup {

    /** AJAX action for AFM lookup */
    const AJAX_ACTION = 'pm_submit_afm';

    /** Subscriptions table */
    protected $subscriptions_table;

    public function __construct() {
        global $wpdb;
        $this->subscriptions_table = $wpdb->prefix . 'pm_user_subscriptions';

        // Enqueue assets
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );

        // AJAX handlers
        add_action( 'wp_ajax_nopriv_' . self::AJAX_ACTION, [ $this, 'handle_submission' ] );
        add_action( 'wp_ajax_'        . self::AJAX_ACTION, [ $this, 'handle_submission' ] );
    }

    /**
     * Enqueue front-end CSS/JS for AFM lookup form
     */
    public function enqueue_assets() {
        wp_enqueue_style( 'wc-pm-frontend', WC_PM_URL . 'assets/css/frontend.css', [], WC_PM_VERSION );
        wp_enqueue_script( 'wc-pm-forms',   WC_PM_URL . 'assets/js/frontend-forms.js', [], WC_PM_VERSION, true );
        wp_localize_script( 'wc-pm-forms', 'PM_AFM_Ajax', [
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'action'   => self::AJAX_ACTION,
            'nonce'    => wp_create_nonce( 'pm_afm_nonce' ),
        ] );
    }

    /**
     * Shortcode callback: render the AFM lookup form
     *
     * Usage: [pm_afm_lookup]
     *
     * @param array $atts Shortcode attributes (unused)
     * @return string HTML
     */
    public function render_afm_lookup_form( $atts ) {
        ob_start();
        include WC_PM_PATH . 'templates/frontend/form-afm-lookup.php';
        return ob_get_clean();
    }

    /**
     * Handle AJAX AFM lookup submission.
     */
    public function handle_submission() {
        check_ajax_referer( 'pm_afm_nonce', 'nonce' );

        if ( empty( $_POST['afm'] ) ) {
            wp_send_json_error( [ 'message' => __( 'Παρακαλώ εισάγετε ΑΦΜ.', 'wc-pm' ) ], 400 );
        }

        $afm = sanitize_text_field( wp_unslash( $_POST['afm'] ) );
        if ( ! function_exists( 'pm_validate_afm' ) || ! pm_validate_afm( $afm ) ) {
            wp_send_json_error( [ 'message' => __( 'Μη έγκυρο ΑΦΜ.', 'wc-pm' ) ], 400 );
        }

        global $wpdb;
        // Find subscriptions with pending payment or expired without renewal
        $subs = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$this->subscriptions_table}
                 WHERE afm = %s
                   AND status IN ('pending','expired')
                 ORDER BY end_date ASC
                 LIMIT 1",
                $afm
            ),
            ARRAY_A
        );

        if ( empty( $subs ) ) {
            wp_send_json_success( [ 'message' => __( 'Δεν έχετε οφειλή προς πληρωμή.', 'wc-pm' ) ] );
        }

        $subscription = $subs[0];
        // Generate payment link (implementation-dependent)
        if ( function_exists( 'pm_generate_payment_link' ) ) {
            $payment_link = pm_generate_payment_link( $subscription['id'] );
        } else {
            // Fallback: link to [pm_payment] shortcode with subscription ID
            $payment_link = esc_url( add_query_arg( 'sub_id', $subscription['id'], site_url( '/payment/' ) ) );
        }

        // Prepare data for email
        $data = [
            'user_name'         => $this->get_subscriber_name( $subscription ),
            'subscription_type' => $subscription['subscription_type'],
            'amount_due'        => $this->calculate_due_amount( $subscription ),
            'payment_link'      => $payment_link,
            'end_date'          => $subscription['end_date'],
            'afm'               => $subscription['afm'],
            'email'             => $this->get_subscriber_email( $subscription ),
        ];

        // Send user email
        do_action( 'pm_email_afm_payment_link_user', $data );

        // Send admin email
        do_action( 'pm_email_afm_payment_link_admin', $data );

        wp_send_json_success( [ 'message' => __( 'Σας έχει σταλεί σύνδεσμος πληρωμής στο email σας.', 'wc-pm' ) ] );
    }

    /**
     * Retrieve subscriber's name from meta_data JSON or user account.
     */
    protected function get_subscriber_name( $subscription ) {
        $meta = json_decode( $subscription['meta_data'], true );
        if ( ! empty( $meta['contact_fname'] ) && ! empty( $meta['contact_lname'] ) ) {
            return $meta['contact_fname'] . ' ' . $meta['contact_lname'];
        }
        return __( 'Χρήστης', 'wc-pm' );
    }

    /**
     * Retrieve subscriber's email from meta_data or user account.
     */
    protected function get_subscriber_email( $subscription ) {
        $meta = json_decode( $subscription['meta_data'], true );
        if ( ! empty( $meta['company_email'] ) ) {
            return sanitize_email( $meta['company_email'] );
        }
        if ( $subscription['user_id'] ) {
            $user = get_userdata( $subscription['user_id'] );
            if ( $user ) {
                return $user->user_email;
            }
        }
        return get_option( 'admin_email' );
    }

    /**
     * Calculate amount due based on subscription_type and settings.
     */
    protected function calculate_due_amount( $subscription ) {
        // Example: fetch package price or fixed rates
        // For simplicity, assume stored in meta_data
        $meta = json_decode( $subscription['meta_data'], true );
        if ( ! empty( $meta['plan_price'] ) ) {
            return number_format_i18n( $meta['plan_price'], intval( get_option( 'pm_decimals', 2 ) ) );
        }
        return __( 'Εκκρεμεί υπολογισμός', 'wc-pm' );
    }
}

