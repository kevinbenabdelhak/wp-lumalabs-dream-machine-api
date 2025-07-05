<?php


if (!defined('ABSPATH')) {
    exit;
}


// JS pour générer le bouton, la requête, et l'insertion dans l'éditeur wp
function wp_lumalabs_add_custom_script() {
    $screen = get_current_screen();
    if ($screen->base === 'post') {
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                var generateButton;
                var urlInput;
                var promptInput;
                var selectedImageNode;
                var selectedText;
                var totalGenerations = 0;
                var completedGenerations = 0;

                var loader = $('<div>', {
                    text: 'Génération en cours...',
                    class: 'wp-lumalabs-loader'
                }).css({
                    position: 'fixed',
                    top: '40px',
                    right: '270px',
                    padding: '10px',
                    backgroundColor: '#c21826',
                    color: '#fff',
                    border: '1px solid #ccc',
                    zIndex: 10000,
                    display: 'none'
                }).appendTo('body');

                function updateLoaderText() {
                    loader.text('Génération en cours... ' + completedGenerations + '/' + totalGenerations);
                }

                function showLoader() {
                    loader.show();
                }

                function hideLoader() {
                    loader.fadeOut();
                }

                function handleErrorResponse(response) {
                    var errorMessage = response.responseJSON && response.responseJSON.data
                        ? response.responseJSON.data
                        : response.statusText || 'Erreur de connexion avec l\'API.';
                    alert('Erreur: ' + errorMessage);
                }

                function pollGenerationStatus(generationId) {
                    $.ajax({
                        url: "<?php echo admin_url('admin-ajax.php'); ?>",
                        method: 'POST',
                        data: {
                            action: 'check_video_status',
                            security: '<?php echo wp_create_nonce('check_video_status_nonce'); ?>',
                            generation_id: generationId
                        },
                        success: function(response) {
                            if (response.success) {
                                if (response.data.state === 'completed') {
                                    completedGenerations++;
                                    updateLoaderText();
                                    $.ajax({
                                        url: "<?php echo admin_url('admin-ajax.php'); ?>",
                                        method: 'POST',
                                        data: {
                                            action: 'download_video',
                                            security: '<?php echo wp_create_nonce('download_video_nonce'); ?>',
                                            video_url: response.data.video_url,
                                            replace_image: "<?php echo get_option('wp_lumalabs_replace_image'); ?>",
                                            post_id: $('#post_ID').val(),
                                            prompt: selectedText || promptInput.val()
                                        },
                                        success: function(res) {
                                            if (res.success) {
                                                insertVideoShortcode(res.data.video_url, res.data.replace_image);
                                            } else {
                                                handleErrorResponse(res);
                                                hideLoader();
                                            }
                                        },
                                        error: function(response) {
                                            handleErrorResponse(response);
                                            hideLoader();
                                        }
                                    });
                                } else if (response.data.state === 'dreaming') {
                                    setTimeout(function() {
                                        pollGenerationStatus(generationId);
                                    }, 10000);
                                } else {
                                    handleErrorResponse({ statusText: response.data.failure_reason });
                                    hideLoader();
                                }
                            } else {
                                handleErrorResponse(response);
                                hideLoader();
                            }
                        },
                        error: function(response) {
                            handleErrorResponse(response);
                            hideLoader();
                        }
                    });
                }

                function insertVideoShortcode(videoUrl, replaceImage) {
                    var videoWidth = "<?php echo esc_js(get_option('wp_lumalabs_video_width', '1280')); ?>";
                    var videoShortcode = '<div class="video_auto">[video width="' + videoWidth + '" autoplay="true" loop="true" muted="true" mp4="' + videoUrl + '"][/video]</div>';
                    
                    if (replaceImage && selectedImageNode) {
                        $(selectedImageNode).replaceWith(videoShortcode);
                    } else {
                        if (selectedImageNode) {
                            $(selectedImageNode).after(videoShortcode);
                        } else {
                            var editor = tinymce.activeEditor;
                            var bookmark = editor.selection.getBookmark(0);
                            editor.execCommand('mceInsertContent', false, videoShortcode);
                            editor.selection.moveToBookmark(bookmark);
                            editor.selection.collapse(false);
                        }
                    }
                    
                    $('#content-html').click();
                    $('#content-tmce').click();
                }
                
                function removeExistingButtons() {
                    if (generateButton) {
                        generateButton.remove();
                        generateButton = null;
                    }
                    if (urlInput) {
                        urlInput.remove();
                        urlInput = null;
                    }
                    if (promptInput) {
                        promptInput.remove();
                        promptInput = null;
                    }
                }

                function showGenerateButton(e) {
                    var node = e.target;
                    var selection = tinymce.activeEditor.selection;
                    selectedText = selection.getContent({ format: 'text' });

                    removeExistingButtons();

                    if (node.nodeName === 'IMG' || selectedText) {
                        selectedImageNode = node.nodeName === 'IMG' ? node : null;

                        if (selectedImageNode) {
                            var imageUrl = $(node).attr('src');

                            urlInput = $('<input>', {
                                type: 'text',
                                placeholder: 'URL de l\'image',
                                value: imageUrl,
                                class: 'wp-lumalabs-url-input'
                            }).css({
                                marginBottom: '10px',
                            }).insertBefore('#wp-content-media-buttons');

                            promptInput = $('<input>', {
                                type: 'text',
                                placeholder: 'Prompt pour la génération de la vidéo',
                                class: 'wp-lumalabs-prompt-input'
                            }).css({
                                marginBottom: '10px',
                                width: '500px'
                            }).insertBefore('#wp-content-media-buttons');
                        }

                        generateButton = $('<button>', {
                            text: 'Générer une vidéo',
                            class: 'button button-primary wp-lumalabs-generate-button'
                        }).css({
                            display: 'inline',
                            zIndex: '100000000',
                        }).insertBefore('#wp-content-media-buttons');

                        generateButton.on('click', function() {
                            showLoader();
                            totalGenerations++;
                            updateLoaderText();

                            var prompt = selectedImageNode ? promptInput.val() : selectedText;
                            var imageUrl = selectedImageNode ? urlInput.val() : null;
                            var stylePrompt = "<?php echo esc_js(get_option('wp_lumalabs_style_prompt')); ?>";
                            var aspectRatio = "<?php echo esc_js(get_option('wp_lumalabs_image_aspect_ratio')); ?>";
                            var fullPrompt = (stylePrompt ? "Style: " + stylePrompt + " " : "") + prompt;

                            $.ajax({
                                url: "<?php echo admin_url('admin-ajax.php'); ?>",
                                method: 'POST',
                                data: {
                                    action: 'generate_video',
                                    post_id: $('#post_ID').val(),
                                    security: '<?php echo wp_create_nonce('generate_video_nonce'); ?>',
                                    prompt: fullPrompt,
                                    image_url: imageUrl,
                                    aspect_ratio: aspectRatio
                                },
                                success: function(response) {
                                    if (response.success) {
                                        pollGenerationStatus(response.data.generation_id);
                                    } else {
                                        handleErrorResponse(response);
                                        hideLoader();
                                    }
                                },
                                error: function(response) {
                                    handleErrorResponse(response);
                                    hideLoader();
                                }
                            });

                            generateButton.remove();
                            if (urlInput) urlInput.remove();
                            promptInput.remove();
                        });
                    }
                }
                     if (typeof tinymce !== 'undefined' && tinymce.editors['content']) {
                    var editor = tinymce.editors['content'];
                    editor.on('init', function() {
                        setTimeout(function() {
                            var iframe = document.querySelector('#content_ifr').contentWindow;

                            $(iframe.document.body).on('click', function(e) {
                                showGenerateButton(e);
                            });
                        }, 3000); // Attendre 3 secondes (3000 millisecondes)
                    });
                } else {
                    console.error('TinyMCE n\'est pas défini');
                }
            });
        </script>
        <?php
    }
}

function wp_lumalabs_enqueue_scripts() {
    $screen = get_current_screen();
    if ($screen->base === 'post') {
        wp_enqueue_script('jquery');
    }
}