<?php
/**
 * JB Cards (jbcards.nl) - 1-Click Deployment & Sync Agent
 * 
 * Plaats dit bestand eenmalig in je public_html/ of webroot op jbcards.nl
 * Hiermee kun je direct je nieuwste webshop, afbeeldingen en database synchroniseren.
 */

define('DEPLOY_SECRET', 'jbcards_live_2025'); // Verander eventueel naar je eigen geheime sleutel

header('Content-Type: application/json');

$action = $_GET['action'] ?? 'status';

if ($action === 'status') {
    $phpVersion = phpversion();
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443;
    $hasZip = class_exists('ZipArchive');
    $writable = is_writable(__DIR__);
    $hasIndex = file_exists(__DIR__ . '/index.html');
    $hasImages = is_dir(__DIR__ . '/images') || is_dir(__DIR__ . '/assets/images');

    echo json_encode([
        'status' => 'online',
        'domain' => $_SERVER['HTTP_HOST'] ?? 'jbcards.nl',
        'https' => $isHttps,
        'php_version' => $phpVersion,
        'zip_supported' => $hasZip,
        'directory_writable' => $writable,
        'index_installed' => $hasIndex,
        'images_installed' => $hasImages,
        'message' => 'jbcards.nl hosting server is gereed voor synchronisatie.'
    ], JSON_PRETTY_PRINT);
    exit;
}

if ($action === 'deploy') {
    $providedKey = $_POST['key'] ?? ($_GET['key'] ?? '');
    if ($providedKey !== DEPLOY_SECRET && !empty(DEPLOY_SECRET)) {
        http_response_code(403);
        echo json_encode(['error' => 'Ongeldige geheime sleutel. Verifieer je DEPLOY_SECRET.']);
        exit;
    }

    // Controleer of er een zip bestand is geüpload
    if (isset($_FILES['package']) && $_FILES['package']['error'] === UPLOAD_ERR_OK) {
        $zipFile = $_FILES['package']['tmp_name'];
        if (!class_exists('ZipArchive')) {
            echo json_encode(['error' => 'ZipArchive extensie ontbreekt op PHP server.']);
            exit;
        }

        $zip = new ZipArchive();
        if ($zip->open($zipFile) === TRUE) {
            $zip->extractTo(__DIR__);
            $zip->close();
            echo json_encode([
                'success' => true,
                'message' => 'jbcards.nl is succesvol geüpdatet en live gezet!',
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            exit;
        } else {
            echo json_encode(['error' => 'Kon het zip-bestand niet uitpakken.']);
            exit;
        }
    }

    // Controleer of jbcards_nl_deployment.zip al lokaal aanwezig is
    $localZip = __DIR__ . '/jbcards_nl_deployment.zip';
    if (file_exists($localZip) && class_exists('ZipArchive')) {
        $zip = new ZipArchive();
        if ($zip->open($localZip) === TRUE) {
            $zip->extractTo(__DIR__);
            $zip->close();
            echo json_encode([
                'success' => true,
                'message' => 'Lokaal pakket jbcards_nl_deployment.zip succesvol uitgepakt op jbcards.nl!',
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            exit;
        }
    }

    echo json_encode(['error' => 'Geen pakket ontvangen om uit te pakken.']);
    exit;
}
