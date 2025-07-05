<?php


if (!defined('ABSPATH')) {
    exit;
}


function wp_lumalabs_generate_video($api_key, $prompt, $image_url, $aspect_ratio) {
    $curl = curl_init();

    $post_fields = [
        'prompt' => $prompt,
        'aspect_ratio' => $aspect_ratio
    ];

    if ($image_url) {
        $post_fields['keyframes'] = [
            'frame0' => [
                'type' => 'image',
                'url' => $image_url
            ]
        ];
    }

    curl_setopt_array($curl, [
        CURLOPT_URL => "https://api.lumalabs.ai/dream-machine/v1/generations",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 300, // 5 minutes
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => json_encode($post_fields),
        CURLOPT_HTTPHEADER => [
            "accept: application/json",
            "content-type: application/json",
            "authorization: Bearer $api_key"
        ],
    ]);

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
        error_log("cURL Error #: " . $err); // Log 
        return ['error' => "cURL Error #: " . $err];
    } else {
        $data = json_decode($response, true);
        if (isset($data['id'])) {
            return $data;
        } else {
            error_log('Erreur API: ' . $response); // Log 
            return ['error' => 'L\'ID de génération n\'a pas été retourné par l\'API.'];
        }
    }
}

function wp_lumalabs_check_video_status($api_key, $generation_id) {
    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => "https://api.lumalabs.ai/dream-machine/v1/generations/$generation_id",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 300, // 5 minutes
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => [
            "accept: application/json",
            "authorization: Bearer $api_key"
        ],
    ]);

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);
    if ($err) {
        error_log("cURL Error #: " . $err); // Log 
        return ['error' => "cURL Error #: " . $err];
    } else {
        $data = json_decode($response, true);
        if ($data['state'] === 'completed' && isset($data['assets']['video'])) {
            $video_url = esc_url_raw($data['assets']['video']);
            return ['state' => 'completed', 'video_url' => $video_url];
        } else if ($data['state'] === 'dreaming') {
            return ['state' => 'dreaming'];
        } else {
            error_log('Erreur API: ' . $response); // Log 
            return ['state' => 'error', 'failure_reason' => $data['failure_reason'] ? $data['failure_reason'] : 'L\'état de génération n\'a pas été retourné par l\'API.'];
        }
    }
}