<?php
require 'auth_config.php';
require_login();
header('Content-Type: text/html; charset=utf-8');
require 'server_data.php';

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);
    if (!$conn->connect_error) {
        $conn->set_charset("utf8");

        // Get the ID of currently selected contestant from database
        $selectedId = "0";
        $res = $conn->query("SELECT setting_value FROM system_settings WHERE setting_key = 'selected_contestant_id'");
        if ($row = $res->fetch_assoc()) {
            $selectedId = $row['setting_value'];
        }

        if ($selectedId && $selectedId !== "0") {
            // Restore contestant to list (set izbran = 0)
            $stmt = $conn->prepare("UPDATE contestants SET izbran = 0 WHERE ID = ?");
            $stmt->bind_param("i", $selectedId);
            $stmt->execute();
            $stmt->close();
        }

        // Clear the selected contestant in database
        $conn->query("UPDATE system_settings SET setting_value = '' WHERE setting_key = 'selected_contestant_name'");
        $conn->query("UPDATE system_settings SET setting_value = '0' WHERE setting_key = 'selected_contestant_id'");

        $conn->close();
    }
}

header("Location: nadzor.php");
die();
?>