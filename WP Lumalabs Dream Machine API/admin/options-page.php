<?php

if (!defined('ABSPATH')) {
    exit;
}


function wp_lumalabs_settings_page() {
    ?>
    <div class="wrap">
        <h1>WP Lumalabs Dream Machine API</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('wp_lumalabs_settings_group');
            do_settings_sections('wp-lumalabs-settings');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}