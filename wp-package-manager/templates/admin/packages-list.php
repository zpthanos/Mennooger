<?php
// templates/admin/packages-list.php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Class PM_Packages_List_Table
 *
 * Renders the List Table for Packages.
 */
class PM_Packages_List_Table extends WP_List_Table {

    /** DB table name */
    protected $table;

    public function __construct() {
        parent::__construct([
            'singular' => __( 'Πακέτο', 'wc-pm' ),
            'plural'   => __( 'Πακέτα', 'wc-pm' ),
            'ajax'     => false,
        ]);
        global $wpdb;
        $this->table = $wpdb->prefix . 'pm_packages';
    }

    /** Define columns */
    public function get_columns() {
        return [
            'cb'       => '<input type="checkbox" />',
            'id'       => __( 'ID', 'wc-pm' ),
            'title'    => __( 'Τίτλος', 'wc-pm' ),
            'price'    => __( 'Τιμή', 'wc-pm' ),
            'status'   => __( 'Κατάσταση', 'wc-pm' ),
            'created'  => __( 'Δημιουργία', 'wc-pm' ),
            'updated'  => __( 'Ενημέρωση', 'wc-pm' ),
        ];
    }

    /** Bulk actions */
    protected function get_bulk_actions() {
        return [
            'publish' => __( 'Δημοσίευση', 'wc-pm' ),
            'draft'   => __( 'Σε πρόχειρο', 'wc-pm' ),
            'trash'   => __( 'Στα σκουπίδια', 'wc-pm' ),
        ];
    }

    /** Prepare items */
    public function prepare_items() {
        global $wpdb;

        $per_page = $this->get_items_per_page( 'pm_packages_per_page', 20 );
        $current_page = $this->get_pagenum();
        $offset = ( $current_page - 1 ) * $per_page;

        // Columns & headers
        $columns  = $this->get_columns();
        $hidden   = [];
        $sortable = [ 'id' => [ 'id', true ], 'title' => [ 'title', false ], 'created' => [ 'created_at', false ] ];
        $this->_column_headers = [ $columns, $hidden, $sortable ];

        // Bulk action handling
        $action = $this->current_action();
        if ( $action && isset( $_REQUEST['package_id'] ) ) {
            $ids = array_map( 'intval', (array) $_REQUEST['package_id'] );
            foreach ( $ids as $id ) {
                switch ( $action ) {
                    case 'publish':
                    case 'draft':
                        $wpdb->update( $this->table, [ 'status' => $action ], [ 'id' => $id ], [ '%s' ], [ '%d' ] );
                        break;
                    case 'trash':
                        $wpdb->delete( $this->table, [ 'id' => $id ], [ '%d' ] );
                        break;
                }
            }
        }

        // Fetch items
        $total_items = $wpdb->get_var( "SELECT COUNT(*) FROM {$this->table}" );
        $items = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$this->table} ORDER BY created_at DESC LIMIT %d OFFSET %d",
                $per_page, $offset
            ),
            ARRAY_A
        );
        $this->items = $items;

        // Pagination
        $this->set_pagination_args([
            'total_items' => $total_items,
            'per_page'    => $per_page,
        ]);
    }

    /** Render column default */
    public function column_default( $item, $column_name ) {
        switch ( $column_name ) {
            case 'id':
            case 'price':
            case 'status':
                return esc_html( $item[ $column_name ] );
            case 'title':
                $edit_url = add_query_arg( [ 'page' => 'pm-packages', 'action' => 'edit', 'package_id' => $item['id'] ], admin_url( 'admin.php' ) );
                return sprintf( '<strong><a href="%s">%s</a></strong>', esc_url( $edit_url ), esc_html( $item['title'] ) );
            case 'created':
                return esc_html( $item['created_at'] );
            case 'updated':
                return esc_html( $item['updated_at'] );
        }
        return '';
    }

    /** Checkbox column */
    protected function column_cb( $item ) {
        return sprintf(
            '<input type="checkbox" name="package_id[]" value="%d" />', intval( $item['id'] )
        );
    }

    /** No items message */
    public function no_items() {
        esc_html_e( 'Δεν βρέθηκαν πακέτα.', 'wc-pm' );
    }
}

// Instantiate and prepare the table
$list_table = new PM_Packages_List_Table();
$list_table->prepare_items();
?>
<div class="wrap">
    <h1><?php esc_html_e( 'Πακέτα', 'wc-pm' ); ?></h1>
    <form method="post">
        <?php $list_table->search_box( __( 'Αναζήτηση Πακέτου', 'wc-pm' ), 'pm_package_search' ); ?>
        <?php $list_table->display(); ?>
    </form>
</div>
