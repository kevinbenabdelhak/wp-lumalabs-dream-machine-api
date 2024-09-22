<?php
/*
Plugin Name: WP Lumalabs Dream Machine API
Plugin URI: https://kevin-benabdelhak.fr/plugins/wp-lumalabs-dream-machine-api/
Description: Générez des vidéos IA directement dans l'éditeur de WordPress en sélectionnant une image ou un texte
Version: 1.0
Author: Kevin Benabdelhak
Author URI: https://kevin-benabdelhak.fr/
Contributors: kevinbenabdelhak
*/

if (!defined('ABSPATH')) {
    exit;
}

require_once plugin_dir_path(__FILE__) . 'admin/settings.php';
require_once plugin_dir_path(__FILE__) . 'admin/menu.php';
require_once plugin_dir_path(__FILE__) . 'admin/options-page.php';
require_once plugin_dir_path(__FILE__) . 'public/enqueue-styles.php';
require_once plugin_dir_path(__FILE__) . 'includes/ajax-functions.php';
require_once plugin_dir_path(__FILE__) . 'includes/api-functions.php';
require_once plugin_dir_path(__FILE__) . 'includes/shortcode-functions.php';



add_action('admin_menu', 'wp_lumalabs_settings_menu');
add_action('admin_init', 'wp_lumalabs_settings_init');
add_action('wp_ajax_generate_video', 'generate_video_action');
add_action('wp_ajax_check_video_status', 'check_video_status_action');
add_action('wp_ajax_download_video', 'download_video_action');
add_action('admin_footer', 'wp_lumalabs_add_custom_script');
add_action('admin_enqueue_scripts', 'wp_lumalabs_enqueue_scripts');
add_action('admin_print_styles', 'wp_lumalabs_enqueue_custom_styles');