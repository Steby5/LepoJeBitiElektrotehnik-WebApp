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

// Get contestants (not selected)
$sql = "SELECT ID, name, time FROM contestants WHERE izbran = 0 ORDER BY time DESC";
$result = $conn->query($sql);
$contestants = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $contestants[] = $row;
    }
}

// Get latest question data
$sql = "SELECT * FROM question ORDER BY ID DESC LIMIT 1";
$resultQ = $conn->query($sql);
$questionData = null;
if ($resultQ->num_rows > 0) {
    $rowQ = $resultQ->fetch_assoc();
    $steviloGlasov = (int) $rowQ['ACount'] + (int) $rowQ['BCount'] + (int) $rowQ['CCount'] + (int) $rowQ['DCount'];
    $questionData = [
        'ID' => $rowQ['ID'],
        'questionText' => $rowQ['questionText'],
        'AText' => $rowQ['AText'],
        'BText' => $rowQ['BText'],
        'CText' => $rowQ['CText'],
        'DText' => $rowQ['DText'],
        'steviloGlasov' => $steviloGlasov,
        'procentA' => $steviloGlasov > 0 ? round((int) $rowQ['ACount'] / $steviloGlasov * 100) : 25,
        'procentB' => $steviloGlasov > 0 ? round((int) $rowQ['BCount'] / $steviloGlasov * 100) : 25,
        'procentC' => $steviloGlasov > 0 ? round((int) $rowQ['CCount'] / $steviloGlasov * 100) : 25,
        'procentD' => $steviloGlasov > 0 ? round((int) $rowQ['DCount'] / $steviloGlasov * 100) : 25
    ];
}

$conn->close();

// Get current contestant and view
$trenutniTekmovalec = trim(file_get_contents("izbran_tekmovalec.txt"));
if ($trenutniTekmovalec == "") {
    $trenutniTekmovalec = "*Ni tekmovalca*";
}
$view = trim(file_get_contents("pogled.txt"));

echo json_encode([
    'contestants' => $contestants,
    'questionData' => $questionData,
    'trenutniTekmovalec' => $trenutniTekmovalec,
    'view' => $view
]);
?>