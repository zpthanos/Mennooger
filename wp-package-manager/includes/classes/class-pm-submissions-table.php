<?php
// includes/classes/class-pm-submissions-table.php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'PM_Submissions_List_Table' ) ) :

/**
 * List-table for “Υποβολές” (partner / interest / payment / subscription forms).
 */
class PM_Submissions_List_Table extends WP_List_Table {

    public function __construct() {
        parent::__construct( [
            'singular' => 'submission',
            'plural'   => 'submissions',
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
            'type'       => __( 'Τύπος',  'wc-pm' ),
            'user'       => __( 'Χρήστης', 'wc-pm' ),
            'status'     => __( 'Κατάσταση', 'wc-pm' ),
            'created_at' => __( 'Ημερομηνία', 'wc-pm' ),
        ];
    }

    public function column_cb( $item ) {
        return sprintf( '<input type="checkbox" name="submission[]" value="%d" />', $item['id'] );
    }

    public function column_type( $item ) {
        $map = [
            'partner'      => __( 'Συνεργάτης',   'wc-pm' ),
            'interest'     => __( 'Ενδιαφέρον',    'wc-pm' ),
            'payment'      => __( 'Πληρωμή',       'wc-pm' ),
            'subscription' => __( 'Συνδρομή',      'wc-pm' ),
        ];
        return $map[ $item['type'] ] ?? esc_html( $item['type'] );
    }

    public function column_user( $item ) {
        if ( ! $item['user_id'] ) {
            return '—';
        }
        $user = get_userdata( $item['user_id'] );
        return $user ? esc_html( $user->user_email ) : '—';
    }

    public function column_status( $item ) {
        $status = esc_html( $item['status'] );
        if ( 'pending' === $status ) {
            return '<span style="color:#dba617">' . $status . '</span>';
        }
        if ( 'completed' === $status ) {
            return '<span style="color:#46b450">' . $status . '</span>';
        }
        return $status;
    }

    /*--------------------------------------------------------------*/
    /*  Data                                                        */
    /*--------------------------------------------------------------*/
    public function prepare_items() {
        global $wpdb;

        $columns  = $this->get_columns();
        $hidden   = [];
        $sortable = [ 'id' => [ 'id', true ], 'created_at' => [ 'created_at', false ] ];
        $this->_column_headers = [ $columns, $hidden, $sortable ];

        $per_page = 20;
        $paged    = $this->get_pagenum();
        $offset   = ( $paged - 1 ) * $per_page;

        $table = $wpdb->prefix . 'pm_submissions';
        $this->items = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT id, type, user_id, status, created_at
                 FROM {$table}
                 ORDER BY id DESC
                 LIMIT %d, %d",
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
