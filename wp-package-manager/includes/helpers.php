<?php
// includes/helpers.php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Create a generic submission record.
 *
 * @param string $type         One of 'partner','interest','payment','subscription'.
 * @param array  $data         Associative array of submitted data.
 * @param int    $related_id   Optional related ID (e.g. subscription_id).
 * @return int|false           Inserted submission ID or false on error.
 */
function pm_create_submission( $type, array $data, $related_id = null ) {
    global $wpdb;
    $table = $wpdb->prefix . 'pm_submissions';
    $now   = current_time( 'mysql' );

    $inserted = $wpdb->insert(
        $table,
        [
            'type'       => sanitize_key( $type ),
            'user_id'    => get_current_user_id() ?: null,
            'data'       => wp_json_encode( $data ),
            'status'     => 'pending',
            'created_at' => $now,
        ],
        [ '%s', '%d', '%s', '%s', '%s' ]
    );

    if ( ! $inserted ) {
        return false;
    }

    $submission_id = intval( $wpdb->insert_id );
    // Fire hook for logging/emailing/etc.
    do_action( "pm_after_create_submission_{$type}", $submission_id, $data, $related_id );
    do_action( 'pm_after_buy_package', $submission_id, get_current_user_id() );

    return $submission_id;
}

/**
 * Retrieve a submission by ID.
 *
 * @param int $submission_id
 * @return array|null         ['id'=>..., 'type'=>..., 'data'=>..., 'status'=>..., 'created_at'=>...] or null.
 */
function pm_get_submission( $submission_id ) {
    global $wpdb;
    $table = $wpdb->prefix . 'pm_submissions';
    $row   = $wpdb->get_row(
        $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", intval( $submission_id ) ),
        ARRAY_A
    );
    if ( ! $row ) {
        return null;
    }
    $row['data'] = json_decode( $row['data'], true );
    return $row;
}

/**
 * Retrieve a payment by ID.
 *
 * @param int $payment_id
 * @return array|null         ['id'=>..., 'submission_id'=>..., 'gateway'=>..., 'amount'=>..., 'status'=>..., 'txn_id'=>..., 'created_at'=>...]
 */
function pm_get_payment( $payment_id ) {
    global $wpdb;
    $table = $wpdb->prefix . 'pm_payments';
    return $wpdb->get_row(
        $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", intval( $payment_id ) ),
        ARRAY_A
    );
}

/* … existing helper functions … */

/**
 * Validate a Greek AFM (Αριθμός Φορολογικού Μητρώου).
 *
 * @param string $afm
 * @return bool
 */
if ( ! function_exists( 'pm_validate_afm' ) ) {
    function pm_validate_afm( $afm ) {
        $afm = preg_replace( '/\D/', '', $afm );
        if ( strlen( $afm ) !== 9 ) {
            return false;
        }
        $sum = 0;
        for ( $i = 0; $i < 8; $i++ ) {
            $sum += ( (int) $afm[$i] ) * ( 1 << ( 8 - $i ) );
        }
        $mod   = $sum % 11;
        $check = $mod % 10;
        return $check === (int) $afm[8];
    }
}

/**
 * Generate or retrieve a payment link for a subscription or submission.
 *
 * @param int $id   Subscription or submission ID.
 * @param string $type 'subscription'|'submission'
 * @return string   URL to payment page with query arg.
 */
function pm_generate_payment_link( $id, $type = 'submission' ) {
    $page_slug = 'payment';
    if ( 'subscription' === $type ) {
        $page_slug = 'subscription';
    }
    $url = add_query_arg( [ 'sub_id' => $id ], site_url( "/{$page_slug}/" ) );
    return esc_url( $url );
}
