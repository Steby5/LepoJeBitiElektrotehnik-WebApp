<?php
require 'auth_config.php';
require_login();
header('Content-Type: application/json; charset=utf-8');
require 'server_data.php';

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(['error' => 'Database connection failed']);
    die();
}
$conn->set_charset("utf8");

// Get all active dozivetja with their registrations
$dozivetja = [];
$sql = "SELECT id, code, name, max_spots, barva FROM dozivetja WHERE active = 1 ORDER BY id ASC";
$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
    $dozivetje = [
        'id' => $row['id'],
        'code' => $row['code'],
        'name' => $row['name'],
        'max_spots' => $row['max_spots'],
        'barva' => $row['barva'],
        'prijavljeni' => [],
        'izbrani' => []
    ];

    // Get prijavljeni (not selected)
    $stmtPrijavljeni = $conn->prepare("SELECT id, name FROM dozivetja_prijave WHERE dozivetje_id = ? AND izbran = 0 ORDER BY id DESC");
    $stmtPrijavljeni->bind_param("i", $row['id']);
    $stmtPrijavljeni->execute();
    $prijavljeniResult = $stmtPrijavljeni->get_result();
    while ($p = $prijavljeniResult->fetch_assoc()) {
        $dozivetje['prijavljeni'][] = $p;
    }
    $stmtPrijavljeni->close();

    // Get izbrani (selected)
    $stmtIzbrani = $conn->prepare("SELECT id, name FROM dozivetja_prijave WHERE dozivetje_id = ? AND izbran = 1 ORDER BY id");
    $stmtIzbrani->bind_param("i", $row['id']);
    $stmtIzbrani->execute();
    $izbraniResult = $stmtIzbrani->get_result();
    while ($i = $izbraniResult->fetch_assoc()) {
        $dozivetje['izbrani'][] = $i;
    }
    $stmtIzbrani->close();

    $dozivetja[] = $dozivetje;
}

// Get system settings
$resSettings = $conn->query("SELECT setting_key, setting_value FROM system_settings");
$settings = [];
while ($sRow = $resSettings->fetch_assoc()) {
    $settings[$sRow['setting_key']] = $sRow['setting_value'];
}

// Get all names that are already selected anywhere to avoid duplicates across experiences
$selectedNames = [];
$resGlobal = $conn->query("SELECT DISTINCT name FROM dozivetja_prijave WHERE izbran = 1");
while ($sRow = $resGlobal->fetch_assoc()) {
    $selectedNames[] = $sRow['name'];
}

$conn->close();

echo json_encode([
    'dozivetja' => $dozivetja,
    'selected_names' => $selectedNames,
    'view' => isset($settings['current_view']) ? $settings['current_view'] : "0",
    'prikazId' => isset($settings['displayed_experience_id']) ? $settings['displayed_experience_id'] : "0"
]);
?>