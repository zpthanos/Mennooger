<?php
// templates/frontend/package-archive.php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Determine display style: 'grid' or 'list'
$style = isset( $atts['style'] ) && 'list' === $atts['style'] ? 'list' : 'grid';
$decimals = intval( get_option( 'pm_decimals', 2 ) );
$symbol   = esc_html( get_option( 'pm_currency_symbol', '€' ) );
$position = get_option( 'pm_currency_position', 'before' );
?>

<?php if ( 'grid' === $style ) : ?>
    <div class="pm-package-archive pm-grid">
        <?php foreach ( $packages as $pkg ) : 
            $price_num = number_format_i18n( $pkg['price'], $decimals );
            $price = 'before' === $position ? $symbol . $price_num : $price_num . $symbol;
        ?>
            <div class="pm-package-item">
                <h3 class="pm-package-title"><?php echo esc_html( $pkg['title'] ); ?></h3>
                <p class="pm-package-desc"><?php echo esc_html( wp_trim_words( $pkg['description'], 20 ) ); ?></p>
                <p class="pm-package-price"><?php echo esc_html( $price ); ?></p>
                <a href="<?php echo esc_url( add_query_arg( 'id', $pkg['id'], site_url( '/package/' ) ) ); ?>" class="button pm-package-button">
                    <?php esc_html_e( 'Περισσότερα', 'wc-pm' ); ?>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
<?php else : ?>
    <ul class="pm-package-archive pm-list">
        <?php foreach ( $packages as $pkg ) :
            $price_num = number_format_i18n( $pkg['price'], $decimals );
            $price = 'before' === $position ? $symbol . $price_num : $price_num . $symbol;
        ?>
            <li class="pm-package-item">
                <h3 class="pm-package-title"><?php echo esc_html( $pkg['title'] ); ?></h3>
                <p class="pm-package-desc"><?php echo esc_html( wp_trim_words( $pkg['description'], 20 ) ); ?></p>
                <span class="pm-package-price"><?php echo esc_html( $price ); ?></span>
                <a href="<?php echo esc_url( add_query_arg( 'id', $pkg['id'], site_url( '/package/' ) ) ); ?>" class="button pm-package-button">
                    <?php esc_html_e( 'Περισσότερα', 'wc-pm' ); ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<?php
// Pagination if needed
if ( function_exists( 'paginate_links' ) ) {
    $total_items = count( $packages ); // You may pass real total via $atts or a global var
    $per_page    = intval( $atts['per_page'] );
    $current     = intval( $atts['page'] );
    $base        = esc_url( add_query_arg( 'paged', '%#%' ) );
    echo '<div class="pm-pagination">' . paginate_links( [
        'base'      => $base,
        'format'    => '?paged=%#%',
        'current'   => $current,
        'total'     => ceil( $total_items / $per_page ),
        'prev_text' => '«',
        'next_text' => '»',
    ] ) . '</div>';
}
?>
