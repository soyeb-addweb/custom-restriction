<?php
// Restriction logic for custrest plugin

if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'template_redirect', 'custrest_maybe_restrict_access' );

/**
 * Main restriction logic for frontend access.
 *
 * Hooks:
 * - do_action( 'custrest_before_restriction_check', $post )
 * - do_action( 'custrest_after_restriction_check', $post, $restricted )
 * - do_action( 'custrest_before_redirect', $redirect_url, $post )
 */
function custrest_maybe_restrict_access() {
    if ( is_admin() || defined( 'DOING_AJAX' ) ) return;
    if ( current_user_can( 'manage_options' ) ) return;

    global $post;
    if ( ! isset( $post ) || ! is_singular() ) return;

    do_action( 'custrest_before_restriction_check', $post );

    $options = get_option( CUSTREST_OPTION_KEY );
    $restricted_types = isset( $options['post_types'] ) ? (array) $options['post_types'] : array();
    $redirect_url = isset( $options['redirect_url'] ) && $options['redirect_url'] ? esc_url( $options['redirect_url'] ) : wp_login_url( get_permalink() );
    $ignore_pages = isset( $options['ignore_pages'] ) ? (array) $options['ignore_pages'] : array();
    $allowed_roles = isset( $options['allowed_roles'] ) ? (array) $options['allowed_roles'] : array();

    if ( in_array( $post->ID, $ignore_pages, true ) ) return;

    $post_type = get_post_type( $post );
    if ( ! in_array( $post_type, $restricted_types, true ) ) return;

    $override = get_post_meta( $post->ID, '_custrest_override', true );
    $roles_override = get_post_meta( $post->ID, '_custrest_roles', true );
    if ( $override === 'disable' ) return;
    if ( $override === 'force' ) {
        $restricted = true;
    } else {
        $restricted = true;
    }

    $restricted = apply_filters( 'custrest_is_restricted', $restricted, $post->ID, $post_type );

    do_action( 'custrest_after_restriction_check', $post, $restricted );

    if ( $restricted ) {
        $user = wp_get_current_user();
        $has_role = false;
        $roles_to_check = ( is_array( $roles_override ) && ! empty( $roles_override ) ) ? $roles_override : $allowed_roles;
        if ( ! empty( $roles_to_check ) ) {
            foreach ( $user->roles as $role ) {
                if ( in_array( $role, $roles_to_check, true ) ) {
                    $has_role = true;
                    break;
                }
            }
        } else {
            $has_role = is_user_logged_in();
        }
        $has_role = apply_filters( 'custrest_user_has_allowed_role', $has_role, $user, $post->ID, $post_type );
        if ( ! $has_role ) {
            $redirect_url = apply_filters( 'custrest_redirect_url', $redirect_url, $post->ID, $post_type );
            do_action( 'custrest_before_redirect', $redirect_url, $post );
            wp_safe_redirect( $redirect_url );
            exit;
        }
    }
}

add_action( 'custrest_before_redirect', 'custrest_set_restriction_notice', 10, 2 );
function custrest_set_restriction_notice( $redirect_url, $post ) {
    if ( is_user_logged_in() ) return;
    set_transient( 'custrest_last_restriction_notice_' . md5( $_SERVER['REMOTE_ADDR'] ), $post->ID, 60 );
}

add_action( 'admin_notices', 'custrest_show_restriction_notice' );
function custrest_show_restriction_notice() {
    if ( ! current_user_can( 'manage_options' ) ) return;
    $key = 'custrest_last_restriction_notice_' . md5( $_SERVER['REMOTE_ADDR'] );
    $post_id = get_transient( $key );
    if ( $post_id ) {
        $title = get_the_title( $post_id );
        echo '<div class="notice notice-warning is-dismissible"><p>' . sprintf( esc_html__( 'A restriction redirect was triggered for "%s" (Post ID: %d).', 'custrest' ), esc_html( $title ), intval( $post_id ) ) . '</p></div>';
        delete_transient( $key );
    }
}