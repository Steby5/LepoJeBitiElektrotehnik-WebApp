<?php
header('Content-Type: application/json; charset=utf-8');
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
require 'server_data.php';

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(['error' => 'Database connection failed']);
    die();
}
$conn->set_charset("utf8");

// Get system settings
$resSettings = $conn->query("SELECT setting_key, setting_value FROM system_settings");
$settings = [];
if ($resSettings) {
    while ($sRow = $resSettings->fetch_assoc()) {
        $settings[$sRow['setting_key']] = $sRow['setting_value'];
    }
}

// Get hash of current experience selections
$izbraniHash = "";
$prikazId = isset($settings['displayed_experience_id']) ? $settings['displayed_experience_id'] : "0";
if ($prikazId !== "0") {
    $stmt = $conn->prepare("SELECT name FROM dozivetja_prijave WHERE dozivetje_id = ? AND izbran = 1 ORDER BY name");
    $stmt->bind_param("i", $prikazId);
    $stmt->execute();
    $res = $stmt->get_result();
    $names = "";
    while ($row = $res->fetch_assoc()) {
        $names .= $row['name'] . "|";
    }
    $izbraniHash = md5($names);
    $stmt->close();
}

$conn->close();

echo json_encode([
    'view' => isset($settings['current_view']) ? $settings['current_view'] : "0",
    'prikazId' => $prikazId,
    'izbraniHash' => $izbraniHash,
    'tekmovalec' => isset($settings['selected_contestant_name']) ? $settings['selected_contestant_name'] : ""
]);
?>