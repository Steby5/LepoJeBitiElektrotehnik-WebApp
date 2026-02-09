<?php
header('Content-Type: application/json; charset=utf-8');
require 'server_data.php';

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(['error' => 'Database connection failed']);
    die();
}
$conn->set_charset("utf8");

// Get contestants (izbran = 0)
$contestants = [];
$sql = "SELECT ID, name FROM contestants WHERE izbran = 0 ORDER BY time DESC";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $contestants[] = $row;
}

// Get latest question stats
$question = null;
$sqlQ = "SELECT * FROM question ORDER BY ID DESC LIMIT 1";
$resultQ = $conn->query($sqlQ);
if ($resultQ->num_rows > 0) {
    $question = $resultQ->fetch_assoc();
}

$conn->close();

// Get view status
$view = "";
if (file_exists("pogled.txt")) {
    $view = trim(file_get_contents("pogled.txt"));
}

// Get current selected contestant
$trenutniTekmovalec = "";
if (file_exists("izbran_tekmovalec.txt")) {
    $trenutniTekmovalec = file_get_contents("izbran_tekmovalec.txt");
}
if ($trenutniTekmovalec == "") {
    $trenutniTekmovalec = "Ni tekmovalca";
}

echo json_encode([
    'contestants' => $contestants,
    'contestantCount' => count($contestants),
    'question' => $question,
    'view' => $view,
    'trenutniTekmovalec' => $trenutniTekmovalec,
    'timestamp' => time()
]);
?>