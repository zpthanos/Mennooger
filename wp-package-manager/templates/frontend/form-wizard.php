<?php
// templates/frontend/form-wizard.php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Steps configuration is available via $this->steps
$step_keys = array_keys( $this->steps );
?>
<div id="pm-subscription-wizard" class="pm-wizard">
    <ul class="pm-wizard-progress">
        <?php foreach ( $step_keys as $index => $step_key ) : ?>
            <li data-step="<?php echo esc_attr( $step_key ); ?>" class="<?php echo 0 === $index ? 'active' : ''; ?>">
                <?php echo esc_html( $this->steps[ $step_key ]['title'] ); ?>
            </li>
        <?php endforeach; ?>
    </ul>

    <form id="pm-wizard-form" class="pm-form" method="post" enctype="multipart/form-data">
        <?php wp_nonce_field( 'pm_wizard_nonce', 'nonce' ); ?>

        <?php foreach ( $this->steps as $step_key => $step ) : 
            $is_first = $step_key === $step_keys[0];
        ?>
            <div class="pm-wizard-step" data-step="<?php echo esc_attr( $step_key ); ?>" style="display: <?php echo $is_first ? 'block' : 'none'; ?>;">
                <h2><?php echo esc_html( $step['title'] ); ?></h2>
                <?php if ( ! empty( $step['description'] ) ) : ?>
                    <p><?php echo esc_html( $step['description'] ); ?></p>
                <?php endif; ?>

                <?php foreach ( $step['fields'] as $field_key ) : ?>
                    <div class="pm-form-field pm-form-field-<?php echo esc_attr( $field_key ); ?>">
                        <?php
                        // You can customize by checking $field_key and rendering the appropriate input.
                        // Example for text inputs:
                        ?>
                        <label for="<?php echo esc_attr( $field_key ); ?>">
                            <?php echo esc_html( ucfirst( str_replace( '_', ' ', $field_key ) ) ); ?>
                        </label>
                        <input 
                            type="text" 
                            name="<?php echo esc_attr( $field_key ); ?>" 
                            id="<?php echo esc_attr( $field_key ); ?>" 
                        />
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>

        <div class="pm-wizard-navigation">
            <button type="button" class="button pm-wizard-prev" disabled>
                <?php esc_html_e( 'Πίσω', 'wc-pm' ); ?>
            </button>
            <button type="button" class="button pm-wizard-next">
                <?php esc_html_e( 'Επόμενο', 'wc-pm' ); ?>
            </button>
            <button type="submit" class="button pm-wizard-submit" style="display: none;">
                <?php esc_html_e( 'Ολοκλήρωση', 'wc-pm' ); ?>
            </button>
        </div>
    </form>
</div>
