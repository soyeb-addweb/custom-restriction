<?php
// Admin settings for custrest plugin

if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'admin_menu', 'custrest_admin_menu' );
add_action( 'admin_init', 'custrest_register_settings' );

function custrest_admin_menu() {
    add_options_page(
        __( 'Restrict Access Settings', 'custrest' ),
        __( 'Restrict Access', 'custrest' ),
        'manage_options',
        'custrest-settings',
        'custrest_settings_page'
    );
}

function custrest_register_settings() {
    register_setting( 'custrest_settings_group', CUSTREST_OPTION_KEY, 'custrest_sanitize_settings' );

    add_settings_section(
        'custrest_main_section',
        __( 'Restriction Settings', 'custrest' ),
        '__return_false',
        'custrest-settings'
    );

    add_settings_field(
        'custrest_post_types',
        __( 'Post Types to Restrict', 'custrest' ),
        'custrest_post_types_field',
        'custrest-settings',
        'custrest_main_section'
    );

    add_settings_field(
        'custrest_redirect_url',
        __( 'Redirect URL if not logged in', 'custrest' ),
        'custrest_redirect_url_field',
        'custrest-settings',
        'custrest_main_section'
    );
}

function custrest_post_types_field() {
    $options = get_option( CUSTREST_OPTION_KEY );
    $selected = isset( $options['post_types'] ) ? (array) $options['post_types'] : array();
    $post_types = get_post_types( array( 'public' => true ), 'objects' );
    foreach ( $post_types as $pt ) {
        printf(
            '<label><input type="checkbox" name="%s[post_types][]" value="%s" %s> %s</label><br>',
            esc_attr( CUSTREST_OPTION_KEY ),
            esc_attr( $pt->name ),
            checked( in_array( $pt->name, $selected ), true, false ),
            esc_html( $pt->labels->singular_name )
        );
    }
}

function custrest_redirect_url_field() {
    $options = get_option( CUSTREST_OPTION_KEY );
    $url = isset( $options['redirect_url'] ) ? esc_url( $options['redirect_url'] ) : '';
    printf(
        '<input type="url" name="%s[redirect_url]" value="%s" class="regular-text" placeholder="%s" />',
        esc_attr( CUSTREST_OPTION_KEY ),
        $url,
        esc_attr( home_url( '/wp-login.php' ) )
    );
}

function custrest_settings_page() {
    ?>
    <div class="wrap">
        <h1><?php _e( 'Restrict Access Settings', 'custrest' ); ?></h1>
        <form method="post" action="options.php">
            <?php
            settings_fields( 'custrest_settings_group' );
            do_settings_sections( 'custrest-settings' );
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

function custrest_sanitize_settings( $input ) {
    $output = array();
    $output['post_types'] = isset( $input['post_types'] ) ? array_map( 'sanitize_text_field', (array) $input['post_types'] ) : array();
    $output['redirect_url'] = isset( $input['redirect_url'] ) ? esc_url_raw( $input['redirect_url'] ) : '';
    return $output;
}