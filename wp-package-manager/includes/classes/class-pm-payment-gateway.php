<?php
// includes/classes/class-pm-payment-gateway.php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class PM_Payment_Gateway
 *
 * Integrates with VISA & VIVA payment gateways via webhooks/callbacks.
 * Updates payment records and fires confirmation, cancellation, and refund hooks.
 */
class PM_Payment_Gateway {

    public function __construct() {
        // Register REST route for gateway callbacks
        add_action( 'rest_api_init', [ $this, 'register_routes' ] );
    }

    /**
     * Register REST API routes for payment gateway callbacks.
     */
    public function register_routes() {
        register_rest_route(
            'wc-pm/v1',
            '/gateway/callback',
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'handle_gateway_callback' ],
                'permission_callback' => '__return_true',
            ]
        );
    }

    /**
     * Handle incoming webhook/callback from VISA or VIVA.
     *
     * Expects JSON payload with at least:
     *  - gateway: 'visa' or 'viva'
     *  - submission_id
     *  - transaction_id
     *  - status ('success','failed','refunded')
     *  - amount
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function handle_gateway_callback( $request ) {
        $params        = $request->get_json_params();
        $gateway       = sanitize_text_field( $params['gateway'] ?? '' );
        $submission_id = intval( $params['submission_id'] ?? 0 );
        $txn_id        = sanitize_text_field( $params['transaction_id'] ?? '' );
        $status        = sanitize_text_field( $params['status'] ?? '' );
        $amount        = floatval( $params['amount'] ?? 0 );

        // TODO: verify callback signature using gateway secret

        global $wpdb;
        $table = $wpdb->prefix . 'pm_payments';

        // Try find existing payment record
        $payment = $wpdb->get_row(
            $wpdb->prepare( "SELECT * FROM {$table} WHERE submission_id = %d", $submission_id ),
            ARRAY_A
        );

        $now = current_time( 'mysql', true );

        if ( $payment ) {
            // Update existing
            $wpdb->update(
                $table,
                [
                    'gateway'    => $gateway,
                    'txn_id'     => $txn_id,
                    'status'     => $status,
                    'amount'     => $amount,
                    'created_at' => $now,
                ],
                [ 'id' => intval( $payment['id'] ) ],
                [ '%s', '%s', '%s', '%f', '%s' ],
                [ '%d' ]
            );
            $payment_id = intval( $payment['id'] );
            $user_id    = intval( $payment['user_id'] ?? 0 );
        } else {
            // Insert new payment
            $wpdb->insert(
                $table,
                [
                    'submission_id' => $submission_id,
                    'gateway'       => $gateway,
                    'txn_id'        => $txn_id,
                    'status'        => $status,
                    'amount'        => $amount,
                    'created_at'    => $now,
                ],
                [ '%d', '%s', '%s', '%s', '%f', '%s' ]
            );
            $payment_id = intval( $wpdb->insert_id );
            $user_id    = 0;
        }

        // Fire appropriate hooks
        switch ( $status ) {
            case 'success':
                do_action( 'pm_payment_confirmed', $payment_id, $user_id );
                break;
            case 'failed':
                do_action( 'pm_payment_cancelled', $payment_id, $user_id );
                break;
            case 'refunded':
                do_action( 'pm_payment_refunded', $payment_id, $user_id );
                break;
        }

        return rest_ensure_response( [ 'result' => 'ok', 'payment_id' => $payment_id ] );
    }

    /**
     * Generate a payment link for a given submission.
     * You can extend this to call the gateway API to create a payment session.
     *
     * @param int $submission_id
     * @return string URL to redirect the user to for payment
     */
    public static function generate_payment_link( $submission_id ) {
        // Placeholder implementation: use a page with [pm_payment] shortcode
        $url = add_query_arg( [ 'sub_id' => $submission_id ], site_url( '/payment/' ) );
        return esc_url( $url );
    }
}

