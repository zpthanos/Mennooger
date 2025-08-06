<?php
// includes/classes/class-pm-logger.php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class PM_Logger
 *
 * Logs critical actions (submissions, payments, subscriptions) to the pm_logs table.
 */
class PM_Logger {

    public function __construct() {
        // Submission creation (partner, interest, payment, subscription)
        add_action( 'pm_after_create_submission_partner',    [ $this, 'log_submission' ], 10, 2 );
        add_action( 'pm_after_create_submission_interest',   [ $this, 'log_submission' ], 10, 2 );
        add_action( 'pm_after_create_submission_payment',    [ $this, 'log_submission' ], 10, 2 );
        add_action( 'pm_after_create_submission_subscription',[ $this, 'log_submission' ], 10, 2 );

        // Payment gateway events
        add_action( 'pm_payment_confirmed',  [ $this, 'log_payment' ], 10, 2 );
        add_action( 'pm_payment_cancelled',  [ $this, 'log_payment' ], 10, 2 );
        add_action( 'pm_payment_refunded',   [ $this, 'log_payment' ], 10, 2 );

        // Subscription lifecycle
        add_action( 'pm_subscription_renew',  [ $this, 'log_subscription_action' ], 10, 1 );
        add_action( 'pm_subscription_cancel', [ $this, 'log_subscription_action' ], 10, 1 );
    }

    /**
     * Generic logger helper.
     *
     * @param string   $action       Action key (e.g. 'submission', 'payment_confirmed')
     * @param string   $object_type  'submission', 'payment', 'subscription'
     * @param int|null $object_id    The related object ID
     */
    protected function log( $action, $object_type, $object_id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'pm_logs';
        $wpdb->insert(
            $table,
            [
                'timestamp'   => current_time( 'mysql' ),
                'user_id'     => get_current_user_id() ?: null,
                'action'      => sanitize_text_field( $action ),
                'object_type' => sanitize_text_field( $object_type ),
                'object_id'   => intval( $object_id ),
                'ip'          => sanitize_text_field( $_SERVER['REMOTE_ADDR'] ?? '' ),
            ],
            [ '%s', '%d', '%s', '%s', '%d', '%s' ]
        );
    }

    /**
     * Log submission creations.
     *
     * @param int   $submission_id
     * @param array $data
     */
    public function log_submission( $submission_id, $data ) {
        $this->log( 'submission_created', 'submission', $submission_id );
    }

    /**
     * Log payment events.
     *
     * @param int $payment_id
     * @param int $user_id
     */
    public function log_payment( $payment_id, $user_id ) {
        // The hook name indicates the status
        $status = current_filter(); // e.g. 'pm_payment_confirmed'
        $this->log( $status, 'payment', $payment_id );
    }

    /**
     * Log subscription renew/cancel.
     *
     * @param int $subscription_id
     */
    public function log_subscription_action( $subscription_id ) {
        $action = current_filter(); // 'pm_subscription_renew' or 'pm_subscription_cancel'
        $this->log( $action, 'subscription', $subscription_id );
    }
}

// Instantiate the logger
new PM_Logger();
