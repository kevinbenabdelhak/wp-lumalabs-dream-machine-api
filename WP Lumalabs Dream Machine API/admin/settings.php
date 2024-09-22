<?php



if (!defined('ABSPATH')) {
    exit;
}


function wp_lumalabs_settings_init() {
    register_setting('wp_lumalabs_settings_group', 'wp_lumalabs_api_key');
    register_setting('wp_lumalabs_settings_group', 'wp_lumalabs_replace_image');
    register_setting('wp_lumalabs_settings_group', 'wp_lumalabs_style_prompt');
    register_setting('wp_lumalabs_settings_group', 'wp_lumalabs_image_aspect_ratio');
    register_setting('wp_lumalabs_settings_group', 'wp_lumalabs_video_width');

    add_settings_section(
        'wp_lumalabs_settings_section',
        'Réglages du plugin',
        '',
        'wp-lumalabs-settings'
    );

    add_settings_field(
        'wp_lumalabs_api_key',
        'Clé API',
        'wp_lumalabs_api_key_field_render',
        'wp-lumalabs-settings',
        'wp_lumalabs_settings_section'
    );

    add_settings_field(
        'wp_lumalabs_replace_image',
        'Remplacer l\'image par la vidéo',
        'wp_lumalabs_replace_image_field_render',
        'wp-lumalabs-settings',
        'wp_lumalabs_settings_section'
    );

    add_settings_field(
        'wp_lumalabs_style_prompt',
        'Style de la vidéo',
        'wp_lumalabs_style_prompt_field_render',
        'wp-lumalabs-settings',
        'wp_lumalabs_settings_section'
    );

    add_settings_field(
        'wp_lumalabs_image_aspect_ratio',
        'Format d\'aspect de l\'image',
        'wp_lumalabs_image_aspect_ratio_field_render',
        'wp-lumalabs-settings',
        'wp_lumalabs_settings_section'
    );

    add_settings_field(
        'wp_lumalabs_video_width',
        'Largeur de la vidéo (px)',
        'wp_lumalabs_video_width_field_render',
        'wp-lumalabs-settings',
        'wp_lumalabs_settings_section'
    );
}

function wp_lumalabs_api_key_field_render() {
    $api_key = get_option('wp_lumalabs_api_key');
    ?>
    <input type="text" name="wp_lumalabs_api_key" value="<?php echo esc_attr($api_key); ?>" size="40">
    <?php
}

function wp_lumalabs_replace_image_field_render() {
    $replace_image = get_option('wp_lumalabs_replace_image');
    ?>
    <input type="checkbox" name="wp_lumalabs_replace_image" value="1" <?php checked(1, $replace_image, true); ?>>
    <?php
}

function wp_lumalabs_style_prompt_field_render() {
    $style_prompt = get_option('wp_lumalabs_style_prompt');
    ?>
    <textarea name="wp_lumalabs_style_prompt" rows="4" cols="50"><?php echo esc_textarea($style_prompt); ?></textarea>
    <?php
}

function wp_lumalabs_image_aspect_ratio_field_render() {
    $aspect_ratio = get_option('wp_lumalabs_image_aspect_ratio');
    $options = [
        '1:1' => '1:1',
        '16:9' => '16:9',
        '9:16' => '9:16',
        '4:3' => '4:3',
        '3:4' => '3:4',
        '21:9' => '21:9',
        '9:21' => '9:21'
    ];
    ?>
    <select name="wp_lumalabs_image_aspect_ratio">
        <?php foreach ($options as $value => $label) : ?>
            <option value="<?php echo esc_attr($value); ?>" <?php selected($aspect_ratio, $value); ?>>
                <?php echo esc_html($label); ?>
            </option>
        <?php endforeach; ?>
    </select>
    <?php
}

function wp_lumalabs_video_width_field_render() {
    $video_width = get_option('wp_lumalabs_video_width', '1280'); // Valeur par défaut : 1280px
    ?>
    <input type="number" name="wp_lumalabs_video_width" value="<?php echo esc_attr($video_width); ?>" size="40" min="100">
    <?php
}