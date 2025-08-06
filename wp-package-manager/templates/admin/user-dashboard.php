<?php
// templates/admin/user-dashboard.php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Class PM_User_Subscriptions_Table
 *
 * Renders the List Table for User Subscriptions.
 */
class PM_User_Subscriptions_Table extends WP_List_Table {

    protected $table;

    public function __construct() {
        parent::__construct([
            'singular' => __( 'Συνδρομή', 'wc-pm' ),
            'plural'   => __( 'Συνδρομές', 'wc-pm' ),
            'ajax'     => false,
        ]);
        global $wpdb;
        $this->table = $wpdb->prefix . 'pm_user_subscriptions';
    }

    public function get_columns() {
        return [
            'cb'            => '<input type="checkbox" />',
            'id'            => __( 'ID', 'wc-pm' ),
            'user'          => __( 'Χρήστης', 'wc-pm' ),
            'type'          => __( 'Τύπος', 'wc-pm' ),
            'start'         => __( 'Έναρξη', 'wc-pm' ),
            'end'           => __( 'Λήξη', 'wc-pm' ),
            'status'        => __( 'Κατάσταση', 'wc-pm' ),
            'label'         => __( 'Ετικέτα', 'wc-pm' ),
            'actions'       => __( 'Ενέργειες', 'wc-pm' ),
        ];
    }

    protected function get_bulk_actions() {
        return [
            'renew'  => __( 'Ανανέωση', 'wc-pm' ),
            'cancel' => __( 'Ακύρωση', 'wc-pm' ),
            'trash'  => __( 'Διαγραφή', 'wc-pm' ),
        ];
    }

    protected function column_cb( $item ) {
        return sprintf(
            '<input type="checkbox" name="subscription_id[]" value="%d" />',
            intval( $item['id'] )
        );
    }

    public function column_default( $item, $column_name ) {
        switch ( $column_name ) {
            case 'id':
                return esc_html( $item['id'] );
            case 'user':
                $user = get_userdata( $item['user_id'] );
                return $user ? esc_html( $user->user_login ) : '-';
            case 'type':
                return esc_html( $item['subscription_type'] );
            case 'start':
                return esc_html( $item['start_date'] );
            case 'end':
                return esc_html( $item['end_date'] );
            case 'status':
                return esc_html( $item['status'] );
            case 'label':
                return esc_html( $item['label'] );
            case 'actions':
                $renew_url = wp_nonce_url(
                    add_query_arg([
                        'page'            => 'pm-user-subscriptions',
                        'action'          => 'renew',
                        'subscription_id' => $item['id'],
                    ], admin_url( 'admin.php' )),
                    'pm_renew_subscription'
                );
                $cancel_url = wp_nonce_url(
                    add_query_arg([
                        'page'            => 'pm-user-subscriptions',
                        'action'          => 'cancel',
                        'subscription_id' => $item['id'],
                    ], admin_url( 'admin.php' )),
                    'pm_cancel_subscription'
                );
                return sprintf(
                    '<a href="%s">%s</a> | <a href="%s">%s</a>',
                    esc_url( $renew_url ),   esc_html__( 'Ανανέωση', 'wc-pm' ),
                    esc_url( $cancel_url ),  esc_html__( 'Ακύρωση', 'wc-pm' )
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

        // Bulk actions
        $action = $this->current_action();
        if ( $action && isset( $_REQUEST['subscription_id'] ) ) {
            $ids = array_map( 'intval', (array) $_REQUEST['subscription_id'] );
            foreach ( $ids as $id ) {
                switch ( $action ) {
                    case 'renew':
                        // custom renewal logic or simply update dates
                        // ...
                        do_action( 'pm_subscription_renew', $id );
                        break;
                    case 'cancel':
                        $wpdb->update( $this->table, [ 'status' => 'cancelled' ], [ 'id' => $id ] );
                        do_action( 'pm_subscription_cancel', $id );
                        break;
                    case 'trash':
                        $wpdb->delete( $this->table, [ 'id' => $id ] );
                        break;
                }
            }
        }

        $total_items = $wpdb->get_var( "SELECT COUNT(*) FROM {$this->table}" );
        $items = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$this->table} ORDER BY end_date DESC LIMIT %d OFFSET %d",
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

$list_table = new PM_User_Subscriptions_Table();
$list_table->prepare_items();
?>
<div class="wrap">
    <h1><?php esc_html_e( 'Συνδρομές Χρηστών', 'wc-pm' ); ?></h1>
    <form method="post">
        <?php $list_table->search_box( __( 'Αναζήτηση Συνδρομής', 'wc-pm' ), 'pm_subscription_search' ); ?>
        <?php $list_table->display(); ?>
    </form>
</div>
