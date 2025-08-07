<?php
// Helper functions for custrest plugin

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Get plugin settings with caching (transient).
 */
function custrest_get_settings() {
    $settings = get_transient( 'custrest_settings_cache' );
    if ( false === $settings ) {
        $settings = get_option( CUSTREST_OPTION_KEY, array() );
        set_transient( 'custrest_settings_cache', $settings, 5 * MINUTE_IN_SECONDS );
    }
    return $settings;
}

/**
 * Clear settings cache on update.
 */
add_action( 'update_option_' . CUSTREST_OPTION_KEY, function() {
    delete_transient( 'custrest_settings_cache' );
}, 10, 0 );

/**
 * Filters and actions for extensibility:
 * - 'custrest_is_restricted' (bool $restricted, int $post_id, string $post_type)
 * - 'custrest_redirect_url' (string $url, int $post_id, string $post_type)
 */

/**
 * Check if a post is restricted for the current user.
 *
 * @param int $post_id
 * @return bool
 */
function custrest_is_post_restricted( $post_id ) {
    if ( ! $post_id || ! get_post( $post_id ) ) return false;
    if ( is_admin() || defined( 'DOING_AJAX' ) ) return false;
    if ( current_user_can( 'manage_options' ) ) return false;

    $options = custrest_get_settings();
    $restricted_types = isset( $options['post_types'] ) ? (array) $options['post_types'] : array();
    $ignore_pages = isset( $options['ignore_pages'] ) ? (array) $options['ignore_pages'] : array();
    $post_type = get_post_type( $post_id );
    $override = get_post_meta( $post_id, '_custrest_override', true );

    if ( in_array( $post_id, $ignore_pages, true ) ) return false;
    if ( $override === 'disable' ) return false;
    if ( $override === 'force' ) return apply_filters( 'custrest_is_restricted', true, $post_id, $post_type );
    if ( in_array( $post_type, $restricted_types, true ) ) {
        return apply_filters( 'custrest_is_restricted', true, $post_id, $post_type );
    }
    return apply_filters( 'custrest_is_restricted', false, $post_id, $post_type );
}