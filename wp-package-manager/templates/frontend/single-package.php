<?php
// templates/frontend/single-package.php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Format price
$decimals = intval( get_option( 'pm_decimals', 2 ) );
$symbol   = esc_html( get_option( 'pm_currency_symbol', '€' ) );
$position = get_option( 'pm_currency_position', 'before' );
$price_num = number_format_i18n( $package['price'], $decimals );
$formatted_price = 'before' === $position
    ? $symbol . $price_num
    : $price_num . $symbol;
?>
<div class="pm-single-package">
    <h1 class="pm-package-title"><?php echo esc_html( $package['title'] ); ?></h1>

    <div class="pm-package-description">
        <?php echo wp_kses_post( wpautop( $package['description'] ) ); ?>
    </div>

    <div class="pm-package-price">
        <strong><?php echo esc_html( $formatted_price ); ?></strong>
    </div>

    <div class="pm-package-action">
        <a
            href="<?php echo esc_url( add_query_arg( 'id', $package['id'], site_url( '/subscription/' ) ) ); ?>"
            class="button pm-subscribe-button"
        >
            <?php esc_html_e( 'Εγγραφή Τώρα', 'wc-pm' ); ?>
        </a>
    </div>
</div>
