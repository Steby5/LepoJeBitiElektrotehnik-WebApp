<?php
header('Content-Type: text/html; charset=utf-8');
require 'server_data.php';

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    // Get the ID of currently selected contestant
    $selectedId = trim(file_get_contents("izbran_tekmovalec_id.txt"));

    if ($selectedId && $selectedId !== "0") {
        // Create connection
        $conn = new mysqli($servername, $username, $password, $dbname);
        if ($conn->connect_error) {
            die("Povezava s strežnikom ni uspela: " . $conn->connect_error);
        }

        // Restore contestant to list (set izbran = 0)
        $stmt = $conn->prepare("UPDATE contestants SET izbran = 0 WHERE ID = ?");
        $stmt->bind_param("i", $selectedId);
        $stmt->execute();
        $stmt->close();
        $conn->close();
    }

    // Clear the selected contestant files
    file_put_contents("izbran_tekmovalec.txt", "");
    file_put_contents("izbran_tekmovalec_id.txt", "0");
}

header("Location: /nadzor.php");
die();
?>