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
    $roles_override = get_post_meta( $post->ID, '_custrest_roles', true );
    $options = get_option( CUSTREST_OPTION_KEY );
    $restricted_types = isset( $options['post_types'] ) ? (array) $options['post_types'] : array();
    $ignore_pages = isset( $options['ignore_pages'] ) ? (array) $options['ignore_pages'] : array();
    $global_roles = isset( $options['allowed_roles'] ) ? (array) $options['allowed_roles'] : array();
    $post_type = get_post_type( $post );
    $status = 'inherit';
    if ( in_array( $post->ID, $ignore_pages, true ) ) {
        $status = 'ignored';
    } elseif ( $value === 'force' ) {
        $status = 'restricted';
    } elseif ( $value === 'disable' ) {
        $status = 'public';
    } elseif ( in_array( $post_type, $restricted_types, true ) ) {
        $status = 'restricted';
    } else {
        $status = 'public';
    }
    $status_labels = array(
        'restricted' => __( 'Restricted (Login Required)', 'custrest' ),
        'public'     => __( 'Always Public', 'custrest' ),
        'inherit'    => __( 'Inherit Global Setting', 'custrest' ),
        'ignored'    => __( 'Ignored (Never Restricted)', 'custrest' ),
    );
    $status_colors = array(
        'restricted' => '#d63638',
        'public'     => '#46b450',
        'inherit'    => '#0073aa',
        'ignored'    => '#ffb900',
    );
    wp_nonce_field( 'custrest_save_meta', 'custrest_meta_nonce' );
    ?>
    <div aria-live="polite" aria-atomic="true" style="margin-bottom:10px;">
        <strong><?php _e( 'Current Restriction Status:', 'custrest' ); ?></strong><br>
        <span style="display:inline-block;padding:2px 8px;border-radius:4px;background:<?php echo esc_attr( $status_colors[$status] ); ?>;color:#fff;font-size:13px;">
            <?php echo esc_html( $status_labels[$status] ); ?>
        </span>
    </div>
    <fieldset aria-labelledby="custrest_restriction_legend">
        <legend id="custrest_restriction_legend" class="screen-reader-text"><?php _e( 'Restriction Level', 'custrest' ); ?></legend>
        <label><input type="radio" name="custrest_override" value="inherit" <?php checked( $value, '' ); checked( $value, 'inherit' ); ?> /> <?php _e( 'Inherit Global Setting', 'custrest' ); ?></label><br>
        <label><input type="radio" name="custrest_override" value="force" <?php checked( $value, 'force' ); ?> /> <?php _e( 'Force Restriction (Login Required)', 'custrest' ); ?></label><br>
        <label><input type="radio" name="custrest_override" value="disable" <?php checked( $value, 'disable' ); ?> /> <?php _e( 'Disable Restriction (Always Public)', 'custrest' ); ?></label>
    </fieldset>
    <fieldset aria-labelledby="custrest_roles_legend" style="margin-top:10px;">
        <legend id="custrest_roles_legend"><?php _e( 'Allowed Roles (Override)', 'custrest' ); ?></legend>
        <label for="custrest_roles_select" class="screen-reader-text"><?php _e( 'Allowed Roles', 'custrest' ); ?></label>
        <select id="custrest_roles_select" name="custrest_roles[]" multiple style="width:100%;max-width:220px;min-height:60px;">
            <option value="" <?php selected( empty( $roles_override ) ); ?>><?php _e( 'Inherit Global Roles', 'custrest' ); ?></option>
            <?php
            global $wp_roles;
            if ( ! isset( $wp_roles ) ) $wp_roles = new WP_Roles();
            $roles = $wp_roles->roles;
            foreach ( $roles as $role_key => $role ) {
                printf(
                    '<option value="%s" %s>%s</option>',
                    esc_attr( $role_key ),
                    selected( is_array( $roles_override ) && in_array( $role_key, $roles_override, true ) ),
                    esc_html( $role['name'] )
                );
            }
            ?>
        </select>
        <p class="description"><?php _e( 'If set, only these roles can access this post/page. Leave empty to inherit global roles.', 'custrest' ); ?></p>
    </fieldset>
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

    if ( isset( $_POST['custrest_roles'] ) && is_array( $_POST['custrest_roles'] ) && array_filter( $_POST['custrest_roles'] ) ) {
        $roles = array_map( 'sanitize_text_field', (array) $_POST['custrest_roles'] );
        update_post_meta( $post_id, '_custrest_roles', $roles );
    } else {
        delete_post_meta( $post_id, '_custrest_roles' );
    }
}