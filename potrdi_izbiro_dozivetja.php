<?php
require 'auth_config.php';
require_login();
/**
 * Handler for confirming experience selections
 * Receives selected person IDs and marks them as izbran = 1
 */
header('Content-Type: application/json; charset=utf-8');
require 'server_data.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

// Get parameters
$dozivetjeId = isset($_POST['dozivetje_id']) ? intval($_POST['dozivetje_id']) : 0;
$dozivetjeCode = isset($_POST['dozivetje_code']) ? $_POST['dozivetje_code'] : '';
$personIdsJson = isset($_POST['person_ids']) ? $_POST['person_ids'] : '[]';

$personIds = json_decode($personIdsJson, true);

if (!$dozivetjeId || !is_array($personIds) || count($personIds) === 0) {
    echo json_encode(['success' => false, 'error' => 'Manjkajoči podatki']);
    exit;
}

// Create database connection
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit;
}
$conn->set_charset("utf8");

// Start transaction
$conn->begin_transaction();

try {
    // Get max spots for this dozivetje
    $stmtMax = $conn->prepare("SELECT max_spots FROM dozivetja WHERE id = ?");
    $stmtMax->bind_param("i", $dozivetjeId);
    $stmtMax->execute();
    $maxResult = $stmtMax->get_result();
    $maxRow = $maxResult->fetch_assoc();
    $maxSpots = $maxRow ? intval($maxRow['max_spots']) : 0;
    $stmtMax->close();

    // Get current selected count
    $stmtCount = $conn->prepare("SELECT COUNT(*) as cnt FROM dozivetja_prijave WHERE dozivetje_id = ? AND izbran = 1");
    $stmtCount->bind_param("i", $dozivetjeId);
    $stmtCount->execute();
    $countResult = $stmtCount->get_result();
    $countRow = $countResult->fetch_assoc();
    $currentSelected = intval($countRow['cnt']);
    $stmtCount->close();

    $availableSpots = $maxSpots - $currentSelected;

    // Process each person
    $confirmedCount = 0;
    $skippedNames = [];

    foreach ($personIds as $personId) {
        $personId = intval($personId);

        // Check if already selected in ANY experience
        $stmtCheck = $conn->prepare("SELECT id, name FROM dozivetja_prijave WHERE id = ?");
        $stmtCheck->bind_param("i", $personId);
        $stmtCheck->execute();
        $personResult = $stmtCheck->get_result();
        $personRow = $personResult->fetch_assoc();
        $stmtCheck->close();

        if (!$personRow) {
            continue; // Person not found
        }

        // Check if this person is already selected somewhere else
        $stmtAlready = $conn->prepare("SELECT COUNT(*) as cnt FROM dozivetja_prijave WHERE name = ? AND izbran = 1 AND id != ?");
        $stmtAlready->bind_param("si", $personRow['name'], $personId);
        $stmtAlready->execute();
        $alreadyResult = $stmtAlready->get_result();
        $alreadyRow = $alreadyResult->fetch_assoc();
        $stmtAlready->close();

        if ($alreadyRow['cnt'] > 0) {
            $skippedNames[] = $personRow['name'] . ' (že izbran drugje)';
            continue;
        }

        // Check if we have available spots
        if ($confirmedCount >= $availableSpots) {
            $skippedNames[] = $personRow['name'] . ' (ni prostih mest)';
            continue;
        }

        // Mark as selected
        $stmtUpdate = $conn->prepare("UPDATE dozivetja_prijave SET izbran = 1 WHERE id = ?");
        $stmtUpdate->bind_param("i", $personId);
        $stmtUpdate->execute();
        $stmtUpdate->close();

        $confirmedCount++;
    }

    $conn->commit();

    $response = [
        'success' => true,
        'confirmed' => $confirmedCount,
        'skipped' => $skippedNames
    ];

    echo json_encode($response);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

$conn->close();
?>