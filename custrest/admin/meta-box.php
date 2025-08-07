<?php
// Admin meta box for custrest plugin

if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'add_meta_boxes', 'custrest_add_restriction_meta_box' );
add_action( 'save_post', 'custrest_save_restriction_meta_box' );

function custrest_add_restriction_meta_box() {
    $options = get_option( CUSTREST_OPTION_KEY );
    $post_types = isset( $options['post_types'] ) ? (array) $options['post_types'] : array();
    if ( empty( $post_types ) ) return;
    foreach ( $post_types as $pt ) {
        add_meta_box(
            'custrest_restriction_meta',
            __( 'Restriction Override', 'custrest' ),
            'custrest_restriction_meta_box_callback',
            $pt,
            'side',
            'default'
        );
    }
}

function custrest_restriction_meta_box_callback( $post ) {
    $value = get_post_meta( $post->ID, '_custrest_override', true );
    wp_nonce_field( 'custrest_save_meta', 'custrest_meta_nonce' );
    ?>
    <p>
        <label><input type="radio" name="custrest_override" value="inherit" <?php checked( $value, '' ); checked( $value, 'inherit' ); ?> /> <?php _e( 'Inherit Global Setting', 'custrest' ); ?></label><br>
        <label><input type="radio" name="custrest_override" value="force" <?php checked( $value, 'force' ); ?> /> <?php _e( 'Force Restriction (Login Required)', 'custrest' ); ?></label><br>
        <label><input type="radio" name="custrest_override" value="disable" <?php checked( $value, 'disable' ); ?> /> <?php _e( 'Disable Restriction (Always Public)', 'custrest' ); ?></label>
    </p>
    <?php
}

function custrest_save_restriction_meta_box( $post_id ) {
    if ( ! isset( $_POST['custrest_meta_nonce'] ) ) return;
    if ( ! wp_verify_nonce( $_POST['custrest_meta_nonce'], 'custrest_save_meta' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    if ( isset( $_POST['custrest_override'] ) ) {
        $val = sanitize_text_field( $_POST['custrest_override'] );
        if ( $val === 'inherit' ) {
            delete_post_meta( $post_id, '_custrest_override' );
        } else {
            update_post_meta( $post_id, '_custrest_override', $val );
        }
    } else {
        delete_post_meta( $post_id, '_custrest_override' );
    }
}