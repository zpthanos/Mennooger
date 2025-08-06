<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<form id="pm-interest-form" class="pm-form" method="post">
    <?php wp_nonce_field( 'pm_interest_nonce', 'nonce' ); ?>

    <?php foreach ( $this->fields as $key => $cfg ) : 
        $id        = esc_attr( $key );
        $label     = esc_html( $cfg['label'] );
        $required  = ! empty( $cfg['required'] ) ? 'required' : '';
        $placeholder = isset( $cfg['placeholder'] ) ? esc_attr( $cfg['placeholder'] ) : '';
    ?>
        <div class="pm-form-field pm-form-field-<?php echo $id; ?>">
            <?php if ( in_array( $cfg['type'], ['text','email','tel'], true ) ) : ?>
                <label for="<?php echo $id; ?>"><?php echo $label; ?><?php echo $required ? ' <span class="required">*</span>' : ''; ?></label>
                <input 
                    type="<?php echo esc_attr( $cfg['type'] ); ?>" 
                    name="<?php echo $id; ?>" 
                    id="<?php echo $id; ?>" 
                    placeholder="<?php echo $placeholder; ?>" 
                    <?php echo $required; ?> 
                />
            <?php elseif ( 'select' === $cfg['type'] ) : ?>
                <label for="<?php echo $id; ?>"><?php echo $label; ?><?php echo $required ? ' <span class="required">*</span>' : ''; ?></label>
                <select name="<?php echo $id; ?>" id="<?php echo $id; ?>" <?php echo $required; ?>>
                    <?php foreach ( $cfg['choices'] as $value => $choice_label ) : ?>
                        <option value="<?php echo esc_attr( $value ); ?>">
                            <?php echo esc_html( $choice_label ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            <?php elseif ( 'textarea' === $cfg['type'] ) : ?>
                <label for="<?php echo $id; ?>"><?php echo $label; ?><?php echo $required ? ' <span class="required">*</span>' : ''; ?></label>
                <textarea 
                    name="<?php echo $id; ?>" 
                    id="<?php echo $id; ?>" 
                    placeholder="<?php echo $placeholder; ?>" 
                    <?php echo $required; ?>
                ></textarea>
            <?php elseif ( 'checkbox' === $cfg['type'] ) : ?>
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
            <?php elseif ( 'hidden' === $cfg['type'] ) : ?>
                <input type="hidden" name="<?php echo $id; ?>" id="<?php echo $id; ?>" value="" />
            <?php endif; ?>
        </div>
    <?php endforeach; ?>

    <p>
        <button type="submit" class="button pm-submit-button">
            <?php esc_html_e( 'Υποβολή', 'wc-pm' ); ?>
        </button>
    </p>
</form>
