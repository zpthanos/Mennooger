<?php
// includes/classes/class-pm-payments-table.php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'PM_Payments_List_Table' ) ) :

/**
 * List-table for Payments (admin → Πληρωμές).
 */
class PM_Payments_List_Table extends WP_List_Table {

    public function __construct() {
        parent::__construct( [
            'singular' => 'payment',
            'plural'   => 'payments',
            'ajax'     => false,
        ] );
    }

    /*--------------------------------------------------------------*/
    /*  Columns                                                     */
    /*--------------------------------------------------------------*/
    public function get_columns() {
        return [
            'cb'         => '<input type="checkbox" />',
            'id'         => 'ID',
            'user'       => __( 'Χρήστης', 'wc-pm' ),
            'amount'     => __( 'Ποσό', 'wc-pm' ),
            'gateway'    => __( 'Πύλη', 'wc-pm' ),
            'status'     => __( 'Κατάσταση', 'wc-pm' ),
            'created_at' => __( 'Ημερομηνία', 'wc-pm' ),
        ];
    }

    public function column_cb( $item ) {
        return sprintf(
            '<input type="checkbox" name="payment[]" value="%d" />',
            $item['id']
        );
    }

    public function column_user( $item ) {
        $user = get_userdata( $item['user_id'] );
        return $user ? esc_html( $user->user_email ) : '—';
    }

    public function column_amount( $item ) {
        $symbol = get_option( 'pm_currency_symbol', '€' );
        $before = get_option( 'pm_currency_position', 'before' ) === 'before';
        $amount = number_format_i18n( $item['amount'], get_option( 'pm_decimals', 2 ) );
        return $before ? $symbol . $amount : $amount . $symbol;
    }

    /*--------------------------------------------------------------*/
    /*  Data                                                        */
    /*--------------------------------------------------------------*/
    public function prepare_items() {
        global $wpdb;

        $columns  = $this->get_columns();
        $hidden   = [];
        $sortable = [ 'id' => [ 'id', true ], 'amount' => [ 'amount', false ] ];
        $this->_column_headers = [ $columns, $hidden, $sortable ];

        $per_page = 20;
        $paged    = $this->get_pagenum();
        $offset   = ( $paged - 1 ) * $per_page;

        $table = $wpdb->prefix . 'pm_payments';
        $this->items = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$table} ORDER BY id DESC LIMIT %d, %d",
                $offset, $per_page
            ),
            ARRAY_A
        );

        $total_items = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" );
        $this->set_pagination_args( [
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => ceil( $total_items / $per_page ),
        ] );
    }
}

endif;
