<?php
require 'auth_config.php';
require_login();
require 'server_data.php';
header('Content-Type: text/html; charset=utf-8');
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['id'])) {
    $dozivetje_id = $_GET['id'];

    $conn = new mysqli($servername, $username, $password, $dbname);
    if (!$conn->connect_error) {
        $conn->set_charset("utf8");
        $stmt = $conn->prepare("UPDATE system_settings SET setting_value = ? WHERE setting_key = 'displayed_experience_id'");
        $stmt->bind_param("s", $dozivetje_id);
        $stmt->execute();
        $stmt->close();
        $conn->close();
    }
}

header("Location: nadzor_dozivetja.php");
die();
?>