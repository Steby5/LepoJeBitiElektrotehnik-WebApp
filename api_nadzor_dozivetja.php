<?php
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
$sql = "SELECT id, code, name, mesta FROM dozivetja WHERE active = 1 ORDER BY name";
$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
    $dozivetje = [
        'id' => $row['id'],
        'code' => $row['code'],
        'name' => $row['name'],
        'mesta' => $row['mesta'],
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

$conn->close();

// Get view status
$view = trim(file_get_contents("pogled.txt"));

// Get current prikaz dozivetja
$prikazId = trim(file_get_contents("prikaz_dozivetje.txt"));

echo json_encode([
    'dozivetja' => $dozivetja,
    'view' => $view,
    'prikazId' => $prikazId
]);
?>