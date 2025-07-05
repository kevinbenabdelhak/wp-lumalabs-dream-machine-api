<?php


if (!defined('ABSPATH')) {
    exit;
}


require_once plugin_dir_path(__FILE__) . 'api-functions.php';

function generate_video_action() {

    check_ajax_referer('generate_video_nonce', 'security');

    $prompt = sanitize_text_field($_POST['prompt']);
    $image_url = sanitize_text_field($_POST['image_url']);
    $aspect_ratio = sanitize_text_field($_POST['aspect_ratio']);

    $api_key = get_option('wp_lumalabs_api_key');
    if (!$api_key) {
        wp_send_json_error(['data' => 'Clé API non configurée.']);
    }

    $response = wp_lumalabs_generate_video($api_key, $prompt, $image_url, $aspect_ratio);

    if (isset($response['id'])) {
        wp_send_json_success(['generation_id' => $response['id']]);
    } else {
        wp_send_json_error(['data' => $response['error']]);
    }
}

function check_video_status_action() {
    check_ajax_referer('check_video_status_nonce', 'security');

    $generation_id = sanitize_text_field($_POST['generation_id']);
    $api_key = get_option('wp_lumalabs_api_key');
    if (!$api_key) {
        wp_send_json_error(['data' => 'Clé API non configurée.']);
    }

    $response = wp_lumalabs_check_video_status($api_key, $generation_id);

    if ($response['state'] === 'completed') {
        wp_send_json_success(['state' => 'completed', 'video_url' => esc_url_raw($response['video_url'])]);
    } else if ($response['state'] === 'dreaming') {
        wp_send_json_success(['state' => 'dreaming']);
    } else {
        wp_send_json_error(['data' => $response['failure_reason']]);
    }
}

function download_video_action() {
    check_ajax_referer('download_video_nonce', 'security');

    $video_url = esc_url_raw($_POST['video_url']);
    $replace_image = filter_var($_POST['replace_image'], FILTER_VALIDATE_BOOLEAN);
    $post_id = intval($_POST['post_id']);
    $prompt = sanitize_text_field($_POST['prompt']);

    // Téléchargement de la vidéo
    $tmp = download_url($video_url);

    if (is_wp_error($tmp)) {
        wp_send_json_error(['data' => $tmp->get_error_message()]);
    }

    // Préparation du tableau pour l'insertion en tant que post média
    $file_array = [
        'name' => basename($video_url),
        'tmp_name' => $tmp
    ];

    // Insertion de l'élément dans la bibliothèque médiatique
    $attachment_id = media_handle_sideload($file_array, $post_id);

    // Gestion des erreurs d'insertion
    if (is_wp_error($attachment_id)) {
        @unlink($file_array['tmp_name']); // Supprime le fichier temporaire en cas d'erreur
        wp_send_json_error(['data' => $attachment_id->get_error_message()]);
    }

    // Mise à jour des métadonnées de la vidéo
    $attachment_data = [
        'ID'           => $attachment_id,
        'post_title'   => $prompt ? $prompt : get_the_title($post_id),
        'post_content' => $prompt ? $prompt : get_the_title($post_id),
        'post_excerpt' => $prompt ? $prompt : get_the_title($post_id),
    ];
    wp_update_post($attachment_data);


    // Récupération de l'URL de la vidéo
    $video_url = wp_get_attachment_url($attachment_id);

    wp_send_json_success([
        'video_url' => $video_url,
        'replace_image' => $replace_image
    ]);
}