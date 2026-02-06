<?php
header('Content-Type: text/html; charset=utf-8');
require 'server_data.php';

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['id'])) {
    $dozivetje_id = intval($_GET['id']);

    if ($dozivetje_id > 0) {
        // Create database connection
        $conn = new mysqli($servername, $username, $password, $dbname);
        if ($conn->connect_error) {
            die("Povezava s strežnikom ni uspela: " . $conn->connect_error);
        }
        $conn->set_charset("utf8");

        // Delete all prijave for this specific dozivetje
        $stmt = $conn->prepare("DELETE FROM dozivetja_prijave WHERE dozivetje_id = ?");
        $stmt->bind_param("i", $dozivetje_id);
        $stmt->execute();
        $stmt->close();

        $conn->close();
    }
}

header("Location: /nadzor_dozivetja.php");
die();
?>