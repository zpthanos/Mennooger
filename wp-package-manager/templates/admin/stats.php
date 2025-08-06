<?php
// templates/admin/stats.php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
|--------------------------------------------------------------------------
| Get dashboard data safely
|--------------------------------------------------------------------------
| If the analytics/helper class isn’t loaded yet, use a zero-filled array.
*/
if ( class_exists( 'PM_Dashboard_Stats' ) && method_exists( 'PM_Dashboard_Stats', 'get_stats' ) ) {
	$stats = PM_Dashboard_Stats::get_stats();
} else {
	$stats = [
		'kpis'   => [
			'total_sales'     => 0,
			'total_revenue'   => 0,
			'total_refunds'   => 0,
			'pending_payments'=> 0,
		],
		'recent' => [],          // no log rows
	];
}
?>
<div class="wrap pm-dashboard">
	<h1><?php esc_html_e( 'Dashboard', 'wc-pm' ); ?></h1>

	<div class="pm-kpis">
		<?php
		$currency_decimals = intval( get_option( 'pm_decimals', 2 ) );
		$kpi_labels = [
			'total_sales'      => __( 'Συνολικές Πωλήσεις', 'wc-pm' ),
			'total_revenue'    => __( 'Συνολικά Έσοδα',     'wc-pm' ),
			'total_refunds'    => __( 'Επιστροφές',         'wc-pm' ),
			'pending_payments' => __( 'Εκκρεμείς Πληρωμές', 'wc-pm' ),
		];

		foreach ( $kpi_labels as $key => $label ) : ?>
			<div class="pm-kpi-card">
				<h2><?php echo esc_html( $label ); ?></h2>
				<p class="pm-kpi-value">
					<?php
					$value = $stats['kpis'][ $key ] ?? 0;
					echo $key === 'total_revenue'
						? esc_html( number_format_i18n( $value, $currency_decimals ) )
						: esc_html( $value );
					?>
				</p>
			</div>
		<?php endforeach; ?>
	</div>

	<h2><?php esc_html_e( 'Πωλήσεις τον τελευταίο μήνα', 'wc-pm' ); ?></h2>
	<canvas id="pm-sales-chart" width="600" height="200"></canvas>

	<h2><?php esc_html_e( 'Δημοφιλία Πακέτων', 'wc-pm' ); ?></h2>
	<canvas id="pm-popularity-chart" width="600" height="200"></canvas>

	<h2><?php esc_html_e( 'Πρόσφατη Δραστηριότητα', 'wc-pm' ); ?></h2>
	<table class="widefat fixed striped">
		<thead>
			<tr>
				<th><?php esc_html_e( 'Ημερομηνία',        'wc-pm' ); ?></th>
				<th><?php esc_html_e( 'Χρήστης',           'wc-pm' ); ?></th>
				<th><?php esc_html_e( 'Δράση',             'wc-pm' ); ?></th>
				<th><?php esc_html_e( 'Τύπος Αντικειμένου', 'wc-pm' ); ?></th>
				<th><?php esc_html_e( 'ID Αντικειμένου',    'wc-pm' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php if ( empty( $stats['recent'] ) ) : ?>
				<tr><td colspan="5"><?php esc_html_e( 'Δεν υπάρχουν εγγραφές.', 'wc-pm' ); ?></td></tr>
			<?php else : ?>
				<?php foreach ( $stats['recent'] as $log ) : ?>
					<tr>
						<td><?php echo esc_html( $log['timestamp'] ); ?></td>
						<td><?php echo esc_html( $log['user_id'] ?: '-' ); ?></td>
						<td><?php echo esc_html( $log['action'] ); ?></td>
						<td><?php echo esc_html( $log['object_type'] ); ?></td>
						<td><?php echo esc_html( $log['object_id'] ); ?></td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
		</tbody>
	</table>
</div>
