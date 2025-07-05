<?php

if (!defined('ABSPATH')) {
    exit;
}


function wp_lumalabs_enqueue_custom_styles() {
    $screen = get_current_screen();
    if ( $screen->base === 'post' ) {
        wp_enqueue_style('wp-lumalabs-custom-admin', plugin_dir_url(__FILE__) . '../assets/css/custom-admin.css');
    }
}

function enqueue_custom_css() {
    echo '<style type="text/css">
        .video_auto .mejs-controls { display: none !important; }
    </style>';
}
add_action('wp_footer', 'enqueue_custom_css');