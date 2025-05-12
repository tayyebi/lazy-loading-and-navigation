<?php
/**
 * Plugin Name: Lazy Loading and Navigation
 * Plugin URI: https://github.com/tayyebi/lazy-loading-and-navigation
 * Description: Loads page content via AJAX with a loading indicator.
 * Text Domain: lazy-loading-and-navigation
 * Version: 1.0.0
 * Author: MohammadReza Tayyebi
 * Author URI: https://gordarg.com
 * License: GPL2
 */

defined( 'ABSPATH' ) or die( 'No direct access please!' );

// Add a settings page to the WordPress admin panel.
function lazyloadnav_register_settings() {
    register_setting( 'lazyloadnav_settings_group', 'lazyloadnav_settings' );
}
add_action( 'admin_init', 'lazyloadnav_register_settings' );

function lazyloadnav_add_settings_page() {
    add_options_page(
        'Lazy Loading and Navigation Settings',
        'Lazy Loading Navigation',
        'manage_options',
        'lazyloadnav-settings',
        'lazyloadnav_render_settings_page'
    );
}
add_action( 'admin_menu', 'lazyloadnav_add_settings_page' );

// Add a debug mode checkbox to the settings page.
function lazyloadnav_render_settings_page() {
    $settings = get_option( 'lazyloadnav_settings', [
        'container' => '.wp-site-blocks',
        'fade_duration' => 300,
        'debug_mode' => false
    ] );
    ?>
    <div class="wrap">
        <h1>Lazy Loading and Navigation Settings</h1>
        <form method="post" action="options.php">
            <?php settings_fields( 'lazyloadnav_settings_group' ); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Container Selector</th>
                    <td><input type="text" name="lazyloadnav_settings[container]" value="<?php echo esc_attr( $settings['container'] ); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Fade Duration (ms)</th>
                    <td><input type="number" name="lazyloadnav_settings[fade_duration]" value="<?php echo esc_attr( $settings['fade_duration'] ); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Enable Debug Mode</th>
                    <td><input type="checkbox" name="lazyloadnav_settings[debug_mode]" value="1" <?php checked( $settings['debug_mode'], true ); ?> /></td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

/**
 * Enqueue scripts and styles for the plugin.
 */
// Pass debug mode to JavaScript.
function lazyloadnav_enqueue_scripts() {
    wp_register_script(
        'lazyloadnav-script',
        plugins_url( 'assets/js/lazyloadnav.js', __FILE__ ),
        array( 'jquery' ),
        '1.0.0',
        true
    );

    $settings = get_option( 'lazyloadnav_settings', [
        'container' => 'main',
        'fade_duration' => 300,
        'debug_mode' => false
    ] );

    $localized_strings = [
        'loading' => __( 'Loading', 'lazy-loading-and-navigation' )
    ];

    $inline_script = "
        var lazyloadnav_settings = " . json_encode( $settings ) . ";
        var lazyloadnav_strings = " . json_encode( $localized_strings ) . ";
    ";
    wp_add_inline_script( 'lazyloadnav-script', $inline_script, 'before' );

    wp_enqueue_script( 'lazyloadnav-script' );
}
add_action( 'wp_enqueue_scripts', 'lazyloadnav_enqueue_scripts' );

/**
 * Enqueue styles for the plugin.
 */
function lazyloadnav_enqueue_styles() {
    wp_register_style(
        'lazyloadnav-style',
        plugins_url( 'assets/css/lazyloadnav.css', __FILE__ ),
        array(),
        '1.0.0'
    );
    wp_enqueue_style( 'lazyloadnav-style' );
}
add_action( 'wp_enqueue_scripts', 'lazyloadnav_enqueue_styles' );
