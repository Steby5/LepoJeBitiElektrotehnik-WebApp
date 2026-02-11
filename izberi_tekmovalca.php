<?php
require 'auth_config.php';
require_login();
require 'server_data.php';
header('Content-Type: text/html; charset=utf-8');

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $name = $_GET['name'];
    $id = intval($_GET['id']);

    $conn = new mysqli($servername, $username, $password, $dbname);
    if (!$conn->connect_error) {
        $conn->set_charset("utf8");

        // Update name
        $stmtName = $conn->prepare("UPDATE system_settings SET setting_value = ? WHERE setting_key = 'selected_contestant_name'");
        $stmtName->bind_param("s", $name);
        $stmtName->execute();
        $stmtName->close();

        // Update ID
        $stmtId = $conn->prepare("UPDATE system_settings SET setting_value = ? WHERE setting_key = 'selected_contestant_id'");
        $stmtId->bind_param("s", $id);
        $stmtId->execute();
        $stmtId->close();

        $conn->close();
    } else {
        die("Napaka pri nastavljanju tekmovalca. Vnesi ročno v programu kviza");
    }
}

header("Location: nadzor.php");
die();
?>