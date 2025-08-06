<?php
// templates/admin/packages-list.php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
|--------------------------------------------------------------------------
| Ensure WP_List_Table is available
|--------------------------------------------------------------------------
*/
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/*
|--------------------------------------------------------------------------
| Load the Packages list-table class
|--------------------------------------------------------------------------
*/
require_once WC_PM_PATH . 'includes/classes/class-pm-packages-table.php';

/*
|--------------------------------------------------------------------------
| Instantiate + prepare
|--------------------------------------------------------------------------
*/
$list_table = new PM_Packages_List_Table();
$list_table->prepare_items();
?>
<div class="wrap">
	<h1><?php esc_html_e( 'Πακέτα', 'wc-pm' ); ?></h1>

	<form method="post">
		<?php
		$list_table->search_box( __( 'Αναζήτηση Πακέτου', 'wc-pm' ), 'pm_package_search' );
		$list_table->display();
		?>
	</form>
</div>
