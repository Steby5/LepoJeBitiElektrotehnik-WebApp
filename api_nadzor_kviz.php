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

$conn = new mysqli($servername, $username, $password, $dbname);
if (!$conn->connect_error) {
    $conn->set_charset("utf8");
    $resS = $conn->query("SELECT setting_key, setting_value FROM system_settings");
    $settings = [];
    while ($sRow = $resS->fetch_assoc()) {
        $settings[$sRow['setting_key']] = $sRow['setting_value'];
    }
    $conn->close();
}

$view = isset($settings['current_view']) ? $settings['current_view'] : "0";
$trenutniTekmovalec = isset($settings['selected_contestant_name']) ? $settings['selected_contestant_name'] : "";
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