<?php
header('Content-Type: text/html; charset=utf-8');
require 'server_data.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if file was uploaded
    if (!isset($_FILES['json_file']) || $_FILES['json_file']['error'] !== UPLOAD_ERR_OK) {
        die("Napaka pri nalaganju datoteke.");
    }

    $uploadedFile = $_FILES['json_file'];

    // Validate file type
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($uploadedFile['tmp_name']);

    if ($mimeType !== 'application/json' && $mimeType !== 'text/plain') {
        die("Napaka: Datoteka mora biti v JSON formatu.");
    }

    // Read and validate JSON content
    $jsonContent = file_get_contents($uploadedFile['tmp_name']);
    $data = json_decode($jsonContent, true);

    if ($data === null) {
        die("Napaka: Neveljavna JSON struktura. " . json_last_error_msg());
    }

    // Validate required structure
    if (!isset($data['options']) || !is_array($data['options'])) {
        die("Napaka: JSON mora vsebovati 'options' array.");
    }

    // Create database connection
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Povezava s strežnikom ni uspela: " . $conn->connect_error);
    }
    $conn->set_charset("utf8");

    // Deactivate all existing dozivetja
    $conn->query("UPDATE dozivetja SET active = 0");

    // Insert or update each dozivetje
    $stmt = $conn->prepare("INSERT INTO dozivetja (name, code, max_spots, barva, active) VALUES (?, ?, ?, ?, 1) 
                            ON DUPLICATE KEY UPDATE name = VALUES(name), max_spots = VALUES(max_spots), barva = VALUES(barva), active = 1");

    foreach ($data['options'] as $option) {
        if (!isset($option['id']) || !isset($option['dozivetje']) || !isset($option['mesta'])) {
            continue; // Skip invalid entries
        }

        $code = $option['id'];
        $name = $option['dozivetje'];
        $mesta = intval($option['mesta']);
        $barva = isset($option['barva']) ? $option['barva'] : '#667eea';

        $stmt->bind_param("ssis", $name, $code, $mesta, $barva);
        $stmt->execute();
    }

    $stmt->close();
    $conn->close();

    // Redirect back to nadzor with success message
    header("Location: /nadzor_dozivetja.php?upload=success");
    die();
}

// If not POST, redirect to nadzor
header("Location: /nadzor_dozivetja.php");
die();
?>