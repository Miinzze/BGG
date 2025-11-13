<?php
require_once 'config.php';
require_once 'functions.php';
requireLogin();
requireAjaxCSRF();

header('Content-Type: application/json');

if (!isset($_FILES['image'])) {
    echo json_encode(['success' => false, 'message' => 'Keine Datei hochgeladen']);
    exit;
}

// Optimiere Bild
$result = optimizeUploadedImage($_FILES['image'], [
    'max_width' => 1920,
    'max_height' => 1080,
    'quality' => 85,
    'create_webp' => true,
    'create_thumbnail' => true,
    'target_dir' => 'uploads/'
]);

if ($result['success']) {
    echo json_encode([
        'success' => true,
        'path' => $result['path'],
        'webp_path' => $result['webp_path'] ?? null,
        'thumbnail_path' => $result['thumbnail_path'] ?? null,
        'savings' => $result['savings_percent'] . '%'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => $result['message']
    ]);
}
