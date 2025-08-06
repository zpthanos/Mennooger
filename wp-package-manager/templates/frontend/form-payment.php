<?php
/**
 * Template: Payment Form
 * Path: templates/frontend/form-payment.php
 *
 * Renders the standalone payment form defined by PM_Payment_Form::$fields.
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<form id="pm-payment-form" class="pm-form" method="post" novalidate>
    <?php wp_nonce_field( 'pm_payment_nonce', 'nonce' ); ?>

    <?php foreach ( $this->fields as $key => $cfg ) :
        $id          = esc_attr( $key );
        $type        = $cfg['type'];
        $label       = esc_html( $cfg['label'] );
        $required    = ! empty( $cfg['required'] ) ? 'required' : '';
        $placeholder = isset( $cfg['placeholder'] ) ? esc_attr( $cfg['placeholder'] ) : '';
        $attrs       = '';
        if ( ! empty( $cfg['attributes'] ) && is_array( $cfg['attributes'] ) ) {
            foreach ( $cfg['attributes'] as $attr => $val ) {
                $attrs .= sprintf( ' %s="%s"', esc_attr( $attr ), esc_attr( $val ) );
            }
        }
    ?>
        <div class="pm-form-field pm-form-field-<?php echo $id; ?>">
            <?php if ( in_array( $type, ['email','number'], true ) ) : ?>
                <label for="<?php echo $id; ?>">
                    <?php echo $label; ?><?php echo $required ? ' <span class="required">*</span>' : ''; ?>
                </label>
                <input
                    type="<?php echo esc_attr( $type ); ?>"
                    name="<?php echo $id; ?>"
                    id="<?php echo $id; ?>"
                    placeholder="<?php echo $placeholder; ?>"
                    <?php echo $required; ?>
                    <?php echo $attrs; ?>
                />
            <?php elseif ( 'select' === $type ) : ?>
                <label for="<?php echo $id; ?>">
                    <?php echo $label; ?><?php echo $required ? ' <span class="required">*</span>' : ''; ?>
                </label>
                <select name="<?php echo $id; ?>" id="<?php echo $id; ?>" <?php echo $required; ?>>
                    <?php foreach ( $cfg['choices'] as $val => $txt ) : ?>
                        <option value="<?php echo esc_attr( $val ); ?>">
                            <?php echo esc_html( $txt ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            <?php elseif ( 'checkbox' === $type ) : ?>
                <label>
                    <input
                        type="checkbox"
                        name="<?php echo $id; ?>"
                        id="<?php echo $id; ?>"
                        value="1"
                        <?php echo $required; ?>
                    />
                    <?php echo $label; ?>
                </label>
            <?php elseif ( 'hidden' === $type ) : ?>
                <input type="hidden" name="<?php echo $id; ?>" id="<?php echo $id; ?>" value="" />
            <?php endif; ?>
        </div>
    <?php endforeach; ?>

    <p>
        <button type="submit" class="button pm-submit-button">
            <?php esc_html_e( 'Προχώρα στην Πληρωμή', 'wc-pm' ); ?>
        </button>
    </p>
</form>
