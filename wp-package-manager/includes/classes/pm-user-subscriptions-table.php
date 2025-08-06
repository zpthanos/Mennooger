<?php
// includes/classes/class-pm-user-subscriptions-table.php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'PM_User_Subscriptions_Table' ) ) :

/**
 * List-table for “Συνδρομές Χρηστών” (User-level subscriptions).
 */
class PM_User_Subscriptions_Table extends WP_List_Table {

    public function __construct() {
        parent::__construct( [
            'singular' => 'subscription',
            'plural'   => 'subscriptions',
            'ajax'     => false,
        ] );
    }

    /*--------------------------------------------------------------*/
    /*  Columns                                                     */
    /*--------------------------------------------------------------*/
    public function get_columns() {
        return [
            'cb'            => '<input type="checkbox" />',
            'id'            => 'ID',
            'user'          => __( 'Χρήστης',          'wc-pm' ),
            'package'       => __( 'Πακέτο',           'wc-pm' ),
            'period'        => __( 'Περίοδος',         'wc-pm' ),
            'amount'        => __( 'Ποσό',             'wc-pm' ),
            'status'        => __( 'Κατάσταση',        'wc-pm' ),
            'start_date'    => __( 'Έναρξη',           'wc-pm' ),
            'end_date'      => __( 'Λήξη',             'wc-pm' ),
        ];
    }

    public function column_cb( $item ) {
        return sprintf( '<input type="checkbox" name="subscription[]" value="%d" />', $item['id'] );
    }

    public function column_user( $item ) {
        $user = get_userdata( $item['user_id'] );
        return $user ? esc_html( $user->user_email ) : '—';
    }

    public function column_package( $item ) {
        return esc_html( $item['package_title'] );
    }

    public function column_period( $item ) {
        return esc_html( ucfirst( $item['period'] ) );
    }

    public function column_amount( $item ) {
        $symbol = get_option( 'pm_currency_symbol', '€' );
        $before = get_option( 'pm_currency_position', 'before' ) === 'before';
        $amt    = number_format_i18n( $item['amount'], get_option( 'pm_decimals', 2 ) );
        return $before ? $symbol . $amt : $amt . $symbol;
    }

    public function column_status( $item ) {
        $s = esc_html( $item['status'] );
        return $s === 'active'
            ? '<span style="color:#46b450">' . $s . '</span>'
            : ( $s === 'expired'
                ? '<span style="color:#d63638">' . $s . '</span>'
                : $s );
    }

    /*--------------------------------------------------------------*/
    /*  Data                                                        */
    /*--------------------------------------------------------------*/
    public function prepare_items() {
        global $wpdb;

        $columns  = $this->get_columns();
        $hidden   = [];
        $sortable = [ 'id' => [ 'id', true ], 'end_date' => [ 'end_date', false ] ];
        $this->_column_headers = [ $columns, $hidden, $sortable ];

        $per_page = 20;
        $paged    = $this->get_pagenum();
        $offset   = ( $paged - 1 ) * $per_page;

        $table = $wpdb->prefix . 'pm_user_subscriptions';
        $this->items = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT us.*, p.title AS package_title
                 FROM {$table} us
                 LEFT JOIN {$wpdb->prefix}pm_packages p
                 ON p.id = us.package_id
                 ORDER BY us.id DESC
                 LIMIT %d, %d",
                $offset, $per_page
            ),
            ARRAY_A
        );

        $total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" );
        $this->set_pagination_args( [
            'total_items' => $total,
            'per_page'    => $per_page,
            'total_pages' => ceil( $total / $per_page ),
        ] );
    }
}

endif;
