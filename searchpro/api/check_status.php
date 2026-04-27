<?php
if (!defined('ABSPATH')) exit;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_GET['berqwp_webhook']) && $_GET['berqwp_webhook'] == 'check_status') {

    $jsonData = file_get_contents('php://input');
    $data = json_decode($jsonData, true);

    if ($data === null) {
        echo json_encode(['status' => 'error', 'msg' => 'Invalid request body']);
        exit;
    }

    $license_key = berqwp_get_license_key();
    $license_key_hash = sanitize_text_field($data['license_key_hash'] ?? '');

    if (empty($license_key)) {
        echo json_encode(['status' => 'inactive']);
        exit;
    }

    if (empty($license_key_hash) || $license_key_hash !== md5($license_key)) {
        echo json_encode(['status' => 'error', 'msg' => 'Request authentication failed']);
        http_response_code(403);
        exit;
    }

    echo json_encode([
        'status'   => 'active',
        'version'  => BERQWP_VERSION,
        'site_url' => home_url(),
    ]);
    exit;
}
