<?php
// includes/classes/class-pm-packages-table.php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'PM_Packages_List_Table' ) ) :

/**
 * List-table for Packages.
 */
class PM_Packages_List_Table extends WP_List_Table {

    public function __construct() {
        parent::__construct( [
            'singular' => 'package',
            'plural'   => 'packages',
            'ajax'     => false,
        ] );
    }

    /*--------------------------------------------------------------*/
    /*  Column definitions                                          */
    /*--------------------------------------------------------------*/
    public function get_columns() {
        return [
            'cb'          => '<input type="checkbox" />',
            'id'          => __( 'ID',          'wc-pm' ),
            'title'       => __( 'Τίτλος',      'wc-pm' ),
            'price'       => __( 'Τιμή',        'wc-pm' ),
            'status'      => __( 'Κατάσταση',   'wc-pm' ),
            'created_at'  => __( 'Δημιουργήθηκε', 'wc-pm' ),
            'updated_at'  => __( 'Ενημερώθηκε', 'wc-pm' ),
        ];
    }

    public function column_cb( $item ) {
        return sprintf(
            '<input type="checkbox" name="package[]" value="%d" />',
            $item['id']
        );
    }

    public function column_title( $item ) {
        $edit_url = admin_url( 'admin.php?page=pm-packages&action=edit&package=' . intval( $item['id'] ) );
        return sprintf(
            '<strong><a href="%s">%s</a></strong>',
            esc_url( $edit_url ),
            esc_html( $item['title'] )
        );
    }

    public function column_price( $item ) {
        $symbol  = get_option( 'pm_currency_symbol', '€' );
        $before  = get_option( 'pm_currency_position', 'before' ) === 'before';
        $price   = number_format_i18n( $item['price'], get_option( 'pm_decimals', 2 ) );
        return $before ? $symbol . $price : $price . $symbol;
    }

    /*--------------------------------------------------------------*/
    /*  Data preparation                                            */
    /*--------------------------------------------------------------*/
    public function prepare_items() {
        global $wpdb;

        $columns  = $this->get_columns();
        $hidden   = [];
        $sortable = [ 'id' => [ 'id', true ], 'title' => [ 'title', false ] ];
        $this->_column_headers = [ $columns, $hidden, $sortable ];

        $per_page = 20;
        $current  = max( 1, $this->get_pagenum() );
        $offset   = ( $current - 1 ) * $per_page;

        $table = $wpdb->prefix . 'pm_packages';
        $items = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$table} ORDER BY id DESC LIMIT %d, %d",
                $offset, $per_page
            ),
            ARRAY_A
        );

        $total_items = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" );

        $this->items = $items;

        $this->set_pagination_args( [
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => ceil( $total_items / $per_page ),
        ] );
    }
}

endif;
