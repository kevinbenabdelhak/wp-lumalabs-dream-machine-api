<?php


if (!defined('ABSPATH')) {
    exit;
}


function wp_lumalabs_settings_menu() {
    add_options_page(
        'WP Lumalabs Dream Machine API',
        'WP Lumalabs Dream Machine API',
        'manage_options',
        'wp-lumalabs-video-generator',
        'wp_lumalabs_settings_page'
    );
}