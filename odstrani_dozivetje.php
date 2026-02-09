<?php
require 'auth_config.php';
require_login();
header('Content-Type: text/html; charset=utf-8');
require 'server_data.php';

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $prijava_id = isset($_GET['pid']) ? intval($_GET['pid']) : 0;

    if ($prijava_id > 0) {
        // Create database connection
        $conn = new mysqli($servername, $username, $password, $dbname);
        if ($conn->connect_error) {
            die("Povezava s strežnikom ni uspela: " . $conn->connect_error);
        }
        $conn->set_charset("utf8");

        // Update the record to mark as not izbran (back to prijavljeni)
        $stmt = $conn->prepare("UPDATE dozivetja_prijave SET izbran = 0 WHERE id = ?");
        $stmt->bind_param("i", $prijava_id);
        $stmt->execute();
        $stmt->close();

        $conn->close();
    }
}

header("Location: /nadzor_dozivetja.php");
die();
?>