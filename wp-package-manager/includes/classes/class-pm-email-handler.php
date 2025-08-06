<?php
// includes/classes/class-pm-email-handler.php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class PM_Email_Handler
 *
 * Listens to plugin hooks and dispatches customizable emails for user and admin.
 */
class PM_Email_Handler {
    /** Table for logging emails */
    protected $log_table;
    /** From name and address */
    protected $from_name;
    protected $from_address;

    public function __construct() {
        global $wpdb;
        $this->log_table   = $wpdb->prefix . 'pm_email_logs';
        $this->from_name    = get_option( 'pm_email_from_name', get_bloginfo( 'name' ) );
        $this->from_address = get_option( 'pm_email_from_address', get_option( 'admin_email' ) );

        // Hook into package/purchase events
        add_action( 'pm_after_buy_package',    [ $this, 'handle_purchase_request' ], 10, 2 );
        add_action( 'pm_payment_confirmed',    [ $this, 'handle_payment_confirmed' ], 10, 2 );
        add_action( 'pm_payment_cancelled',    [ $this, 'handle_payment_cancelled' ], 10, 2 );
        add_action( 'pm_payment_refunded',     [ $this, 'handle_payment_refunded' ], 10, 2 );
        add_action( 'pm_payment_error',        [ $this, 'handle_payment_error' ], 10, 2 );
        add_action( 'pm_email_afm_payment_link_user',  [ $this, 'handle_afm_payment_link_user' ],  10, 1 );
        add_action( 'pm_email_afm_payment_link_admin', [ $this, 'handle_afm_payment_link_admin' ], 10, 1 );
    }

    /**
     * Generic email sender
     *
     * @param string $key  Template key (filename without .html)
     * @param string $to   Recipient email address
     * @param array  $data Placeholder data
     */
    protected function send_email( $key, $to, array $data = [] ) {
        // Subject option key
        $subject_opt = 'pm_email_subject_' . $key;
        $subject     = get_option( $subject_opt, '' );
        if ( empty( $subject ) ) {
            $subject = '[' . get_bloginfo( 'name' ) . '] ' . ucfirst( str_replace( '-', ' ', $key ) );
        }

        // Render body
        $body = PM_Email_Templates::render( $key, $data );

        // Headers
        $headers   = [];
        $headers[] = 'Content-Type: text/html; charset=UTF-8';
        $headers[] = 'From: ' . wp_specialchars_decode( $this->from_name ) . ' <' . $this->from_address . '>';
        $reply_to  = get_option( 'pm_email_reply_to', '' );
        if ( $reply_to ) {
            $headers[] = 'Reply-To: ' . $reply_to;
        }

        // Send
        $sent = wp_mail( $to, $subject, $body, $headers );

        // Log
        $status = $sent ? 'sent' : 'failed';
        global $wpdb;
        $wpdb->insert(
            $this->log_table,
            [
                'template_key' => sanitize_text_field( $key ),
                'recipient'    => sanitize_email( $to ),
                'subject'      => wp_strip_all_tags( $subject ),
                'status'       => $status,
                'error_msg'    => $sent ? '' : __( 'wp_mail failed', 'wc-pm' ),
                'timestamp'    => current_time( 'mysql', true ),
            ],
            [ '%s', '%s', '%s', '%s', '%s', '%s' ]
        );
    }

    /**
     * Handle purchase request emails
     */
    public function handle_purchase_request( $submission_id, $user_id ) {
        // Fetch submission data
        $submission = pm_get_submission( $submission_id );
        $email      = $submission['data']['partner_email'] ?? $submission['data']['email'] ?? get_userdata( $user_id )->user_email;
        $data       = [
            'user_name' => $submission['data']['partner_name'] ?? '',
            'package'   => $submission['data']['package_title'] ?? '',
            'link'      => $submission['data']['payment_link'] ?? '',
        ];
        // User
        $this->send_email( 'purchase-request-user', $email, $data );
        // Admin
        $this->send_email( 'purchase-request-admin', get_option( 'admin_email' ), $data );
    }

    /**
     * Handle payment confirmed emails
     */
    public function handle_payment_confirmed( $payment_id, $user_id ) {
        $payment    = pm_get_payment( $payment_id );
        $user_email = $payment['email'] ?? get_userdata( $user_id )->user_email;
        $data = [
            'user_name' => $payment['customer_name'] ?? '',
            'amount'    => $payment['amount'],
            'txn_id'    => $payment['txn_id'],
        ];
        $this->send_email( 'payment-confirmed-user', $user_email, $data );
        $this->send_email( 'payment-confirmed-admin', get_option( 'admin_email' ), $data );
    }

    /**
     * Handle payment cancelled emails
     */
    public function handle_payment_cancelled( $payment_id, $user_id ) {
        $payment    = pm_get_payment( $payment_id );
        $user_email = get_userdata( $user_id )->user_email;
        $data = [
            'user_name' => $payment['customer_name'] ?? '',
            'amount'    => $payment['amount'],
            'txn_id'    => $payment['txn_id'],
        ];
        $this->send_email( 'payment-cancelled-user', $user_email, $data );
        $this->send_email( 'payment-cancelled-admin', get_option( 'admin_email' ), $data );
    }

    /**
     * Handle refund issued emails
     */
    public function handle_payment_refunded( $payment_id, $user_id ) {
        $payment    = pm_get_payment( $payment_id );
        $user_email = get_userdata( $user_id )->user_email;
        $data = [
            'user_name' => $payment['customer_name'] ?? '',
            'amount'    => $payment['amount'],
            'txn_id'    => $payment['txn_id'],
        ];
        $this->send_email( 'refund-issued-user', $user_email, $data );
        $this->send_email( 'refund-issued-admin', get_option( 'admin_email' ), $data );
    }

    /**
     * Handle generic payment error emails
     */
    public function handle_payment_error( $payment_id, $user_id ) {
        $payment    = pm_get_payment( $payment_id );
        $user_email = get_userdata( $user_id )->user_email;
        $data = [
            'user_name' => $payment['customer_name'] ?? '',
            'error'     => $payment['error_message'] ?? '',
        ];
        $this->send_email( 'payment-error-user', $user_email, $data );
        $this->send_email( 'payment-error-admin', get_option( 'admin_email' ), $data );
    }

    /**
     * Handle AFM payment link email to user
     */
    public function handle_afm_payment_link_user( $subscription ) {
        $data = [
            'user_name'        => $subscription['user_name'] ?? '',
            'subscription_type'=> $subscription['subscription_type'],
            'amount'           => $subscription['amount_due'],
            'payment_link'     => $subscription['payment_link'],
            'due_date'         => $subscription['end_date'],
        ];
        $this->send_email( 'afm-payment-link-user', $subscription['email'], $data );
    }

    /**
     * Handle AFM payment link email to admin
     */
    public function handle_afm_payment_link_admin( $subscription ) {
        $data = [
            'user_name'        => $subscription['user_name'] ?? '',
            'afm'              => $subscription['afm'],
            'amount'           => $subscription['amount_due'],
            'payment_link'     => $subscription['payment_link'],
            'due_date'         => $subscription['end_date'],
        ];
        $this->send_email( 'afm-payment-link-admin', get_option( 'admin_email' ), $data );
    }
}

// Instantiate
new PM_Email_Handler();
