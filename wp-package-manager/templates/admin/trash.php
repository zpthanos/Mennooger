<?php
// templates/admin/trash.php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="wrap">
    <h1><?php esc_html_e( 'Trash', 'wc-pm' ); ?></h1>
    <p><?php esc_html_e( 'Εδώ μπορείτε να επαναφέρετε ή να διαγράψετε οριστικά αντικείμενα που βρίσκονται στα σκουπίδια.', 'wc-pm' ); ?></p>

    <h2><?php esc_html_e( 'Πακέτα στα Σκουπίδια', 'wc-pm' ); ?></h2>
    <form method="post">
        <?php
        // List trashed packages
        global $wpdb;
        $packages = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}pm_packages WHERE status = 'trash'", ARRAY_A );
        if ( $packages ) :
        ?>
            <table class="widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e( 'ID', 'wc-pm' ); ?></th>
                        <th><?php esc_html_e( 'Τίτλος', 'wc-pm' ); ?></th>
                        <th><?php esc_html_e( 'Ενέργειες', 'wc-pm' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $packages as $pkg ) : ?>
                        <tr>
                            <td><?php echo esc_html( $pkg['id'] ); ?></td>
                            <td><?php echo esc_html( $pkg['title'] ); ?></td>
                            <td>
                                <a href="<?php echo esc_url( add_query_arg( [ 'action' => 'pm_restore', 'type' => 'package', 'id' => $pkg['id'] ], admin_url( 'admin.php?page=pm-trash' ) ) ); ?>">
                                    <?php esc_html_e( 'Επαναφορά', 'wc-pm' ); ?>
                                </a> |
                                <a href="<?php echo esc_url( add_query_arg( [ 'action' => 'pm_delete_permanent', 'type' => 'package', 'id' => $pkg['id'] ], admin_url( 'admin.php?page=pm-trash' ) ) ); ?>">
                                    <?php esc_html_e( 'Διαγραφή Οριστικά', 'wc-pm' ); ?>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else : ?>
            <p><?php esc_html_e( 'Δεν υπάρχουν πακέτα στα σκουπίδια.', 'wc-pm' ); ?></p>
        <?php endif; ?>

    </form>
</div>
