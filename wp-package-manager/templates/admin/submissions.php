<?php
// templates/admin/submissions.php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Class PM_Submissions_List_Table
 *
 * Renders the List Table for Form Submissions.
 */
class PM_Submissions_List_Table extends WP_List_Table {

    protected $table;

    public function __construct() {
        parent::__construct([
            'singular' => __( 'Υποβολή', 'wc-pm' ),
            'plural'   => __( 'Υποβολές', 'wc-pm' ),
            'ajax'     => false,
        ]);
        global $wpdb;
        $this->table = $wpdb->prefix . 'pm_submissions';
    }

    public function get_columns() {
        return [
            'cb'      => '<input type="checkbox" />',
            'id'      => __( 'ID', 'wc-pm' ),
            'type'    => __( 'Τύπος', 'wc-pm' ),
            'user'    => __( 'Χρήστης', 'wc-pm' ),
            'created' => __( 'Ημερομηνία', 'wc-pm' ),
            'status'  => __( 'Κατάσταση', 'wc-pm' ),
            'actions' => __( 'Ενέργειες', 'wc-pm' ),
        ];
    }

    protected function get_bulk_actions() {
        return [
            'complete' => __( 'Ολοκληρώσεις', 'wc-pm' ),
            'cancel'   => __( 'Ακύρωση', 'wc-pm' ),
            'trash'    => __( 'Διαγραφή', 'wc-pm' ),
        ];
    }

    protected function column_cb( $item ) {
        return sprintf(
            '<input type="checkbox" name="submission_id[]" value="%d" />',
            intval( $item['id'] )
        );
    }

    public function column_default( $item, $column_name ) {
        switch ( $column_name ) {
            case 'id':
            case 'type':
                return esc_html( $item[ $column_name ] );
            case 'user':
                return $item['user_id']
                    ? esc_html( get_userdata( $item['user_id'] )->user_login )
                    : '-';
            case 'created':
                return esc_html( $item['created_at'] );
            case 'status':
                return esc_html( $item['status'] );
            case 'actions':
                $view   = add_query_arg([
                    'page'           => 'pm-submissions',
                    'action'         => 'view',
                    'submission_id'  => $item['id'],
                ], admin_url( 'admin.php' ));
                $delete = wp_nonce_url(
                    add_query_arg([
                        'page'           => 'pm-submissions',
                        'action'         => 'trash',
                        'submission_id'  => $item['id'],
                    ], admin_url( 'admin.php' )),
                    'pm_trash_submission'
                );
                return sprintf(
                    '<a href="%s">%s</a> | <a href="%s">%s</a>',
                    esc_url( $view ),   esc_html__( 'Προβολή', 'wc-pm' ),
                    esc_url( $delete ), esc_html__( 'Διαγραφή', 'wc-pm' )
                );
            default:
                return '';
        }
    }

    public function prepare_items() {
        global $wpdb;
        $per_page    = 20;
        $current_page = $this->get_pagenum();
        $offset      = ( $current_page - 1 ) * $per_page;

        // Handle bulk actions
        $action = $this->current_action();
        if ( $action && isset( $_REQUEST['submission_id'] ) ) {
            $ids = array_map( 'intval', (array) $_REQUEST['submission_id'] );
            foreach ( $ids as $id ) {
                switch ( $action ) {
                    case 'complete':
                        $wpdb->update( $this->table, [ 'status' => 'completed' ], [ 'id' => $id ] );
                        break;
                    case 'cancel':
                        $wpdb->update( $this->table, [ 'status' => 'cancelled' ], [ 'id' => $id ] );
                        break;
                    case 'trash':
                        $wpdb->delete( $this->table, [ 'id' => $id ] );
                        break;
                }
            }
        }

        $total_items = $wpdb->get_var( "SELECT COUNT(*) FROM {$this->table}" );
        $items       = $wpdb->get_results(
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

// Display the table
$list_table = new PM_Submissions_List_Table();
$list_table->prepare_items();
?>
<div class="wrap">
    <h1><?php esc_html_e( 'Υποβολές', 'wc-pm' ); ?></h1>
    <form method="post">
        <?php $list_table->search_box( __( 'Αναζήτηση Υποβολής', 'wc-pm' ), 'pm_submission_search' ); ?>
        <?php $list_table->display(); ?>
    </form>
</div>
