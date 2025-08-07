<?php
// Restriction logic for custrest plugin

if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'template_redirect', 'custrest_maybe_restrict_access' );

function custrest_maybe_restrict_access() {
    if ( is_admin() || defined( 'DOING_AJAX' ) ) return;
    if ( current_user_can( 'manage_options' ) ) return;

    global $post;
    if ( ! isset( $post ) || ! is_singular() ) return;

    $options = get_option( CUSTREST_OPTION_KEY );
    $restricted_types = isset( $options['post_types'] ) ? (array) $options['post_types'] : array();
    $redirect_url = isset( $options['redirect_url'] ) && $options['redirect_url'] ? esc_url( $options['redirect_url'] ) : wp_login_url( get_permalink() );
    $ignore_pages = isset( $options['ignore_pages'] ) ? (array) $options['ignore_pages'] : array();

    // Always ignore restriction for selected pages/posts
    if ( in_array( $post->ID, $ignore_pages, true ) ) return;

    $post_type = get_post_type( $post );
    if ( ! in_array( $post_type, $restricted_types, true ) ) return;

    // Check per-post override
    $override = get_post_meta( $post->ID, '_custrest_override', true );
    if ( $override === 'disable' ) return;
    if ( $override === 'force' ) {
        $restricted = true;
    } else {
        $restricted = true; // Inherit global
    }

    $restricted = apply_filters( 'custrest_is_restricted', $restricted, $post->ID, $post_type );

    if ( $restricted && ! is_user_logged_in() ) {
        $redirect_url = apply_filters( 'custrest_redirect_url', $redirect_url, $post->ID, $post_type );
        wp_safe_redirect( $redirect_url );
        exit;
    }
}