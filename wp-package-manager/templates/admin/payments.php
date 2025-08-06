<?php
// templates/admin/payments.php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Class PM_Payments_List_Table
 *
 * Renders the List Table for Payments.
 */
class PM_Payments_List_Table extends WP_List_Table {

    protected $table;

    public function __construct() {
        parent::__construct([
            'singular' => __( 'Πληρωμή', 'wc-pm' ),
            'plural'   => __( 'Πληρωμές', 'wc-pm' ),
            'ajax'     => false,
        ]);
        global $wpdb;
        $this->table = $wpdb->prefix . 'pm_payments';
    }

    public function get_columns() {
        return [
            'cb'            => '<input type="checkbox" />',
            'id'            => __( 'ID', 'wc-pm' ),
            'submission_id' => __( 'ID Υποβολής', 'wc-pm' ),
            'gateway'       => __( 'Πύλη', 'wc-pm' ),
            'amount'        => __( 'Ποσό', 'wc-pm' ),
            'status'        => __( 'Κατάσταση', 'wc-pm' ),
            'txn_id'        => __( 'Transaction ID', 'wc-pm' ),
            'created'       => __( 'Ημερομηνία', 'wc-pm' ),
            'actions'       => __( 'Ενέργειες', 'wc-pm' ),
        ];
    }

    protected function get_bulk_actions() {
        return [
            'refund' => __( 'Επιστροφή', 'wc-pm' ),
            'cancel' => __( 'Ακύρωση', 'wc-pm' ),
            'trash'  => __( 'Διαγραφή', 'wc-pm' ),
        ];
    }

    protected function column_cb( $item ) {
        return sprintf(
            '<input type="checkbox" name="payment_id[]" value="%d" />',
            intval( $item['id'] )
        );
    }

    public function column_default( $item, $column_name ) {
        switch ( $column_name ) {
            case 'id':
            case 'submission_id':
            case 'gateway':
            case 'amount':
            case 'status':
            case 'txn_id':
                return esc_html( $item[ $column_name ] );
            case 'created':
                return esc_html( $item['created_at'] );
            case 'actions':
                $refund_url = wp_nonce_url(
                    add_query_arg([
                        'page'       => 'pm-payments',
                        'action'     => 'refund',
                        'payment_id' => $item['id'],
                    ], admin_url( 'admin.php' )),
                    'pm_refund_payment'
                );
                $cancel_url = wp_nonce_url(
                    add_query_arg([
                        'page'       => 'pm-payments',
                        'action'     => 'cancel',
                        'payment_id' => $item['id'],
                    ], admin_url( 'admin.php' )),
                    'pm_cancel_payment'
                );
                return sprintf(
                    '<a href="%s">%s</a> | <a href="%s">%s</a>',
                    esc_url( $refund_url ),   esc_html__( 'Επιστροφή', 'wc-pm' ),
                    esc_url( $cancel_url ),   esc_html__( 'Ακύρωση', 'wc-pm' )
                );
            default:
                return '';
        }
    }

    public function prepare_items() {
        global $wpdb;
        $per_page     = 20;
        $current_page = $this->get_pagenum();
        $offset       = ( $current_page - 1 ) * $per_page;

        // Handle bulk actions
        $action = $this->current_action();
        if ( $action && isset( $_REQUEST['payment_id'] ) ) {
            $ids = array_map( 'intval', (array) $_REQUEST['payment_id'] );
            foreach ( $ids as $id ) {
                switch ( $action ) {
                    case 'refund':
                        $wpdb->update( $this->table, [ 'status' => 'refunded' ], [ 'id' => $id ], [ '%s' ], [ '%d' ] );
                        do_action( 'pm_payment_refunded', $id, 0 );
                        break;
                    case 'cancel':
                        $wpdb->update( $this->table, [ 'status' => 'failed' ], [ 'id' => $id ], [ '%s' ], [ '%d' ] );
                        do_action( 'pm_payment_cancelled', $id, 0 );
                        break;
                    case 'trash':
                        $wpdb->delete( $this->table, [ 'id' => $id ], [ '%d' ] );
                        break;
                }
            }
        }

        $total_items = $wpdb->get_var( "SELECT COUNT(*) FROM {$this->table}" );
        $items = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$this->table} ORDER BY created_at DESC LIMIT %d OFFSET %d",
                $per_page, $offset
            ),
            ARRAY_A
        );
        $this->items = $items;

        $this->set_pagination_args([
            'total_items' => $total_items,
            'per_page'    => $per_page,
        ]);
    }
}

// Display the payments table
$list_table = new PM_Payments_List_Table();
$list_table->prepare_items();
?>
<div class="wrap">
    <h1><?php esc_html_e( 'Πληρωμές', 'wc-pm' ); ?></h1>
    <form method="post">
        <?php $list_table->search_box( __( 'Αναζήτηση Πληρωμής', 'wc-pm' ), 'pm_payment_search' ); ?>
        <?php $list_table->display(); ?>
    </form>
</div>
