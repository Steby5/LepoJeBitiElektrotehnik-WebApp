<?php
require 'auth_config.php';
require_login();
header('Content-Type: text/html; charset=utf-8');
require 'server_data.php';

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $dozivetje_code = $_GET['id'];
    $ime = $_GET['ime'];
    $prijava_id = isset($_GET['pid']) ? intval($_GET['pid']) : 0;

    // Create database connection
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Povezava s strežnikom ni uspela: " . $conn->connect_error);
    }
    $conn->set_charset("utf8");

    // Check if person is already selected in ANY experience
    $stmtAlready = $conn->prepare("SELECT COUNT(*) as cnt FROM dozivetja_prijave WHERE name = ? AND izbran = 1");
    $stmtAlready->bind_param("s", $ime);
    $stmtAlready->execute();
    $alreadyResult = $stmtAlready->get_result()->fetch_assoc();
    $stmtAlready->close();

    if ($alreadyResult['cnt'] > 0) {
        $conn->close();
        header("Location: /nadzor_dozivetja.php?error=already_selected");
        die();
    }

    // Get dozivetje info to check available spots
    $stmtCheck = $conn->prepare("
        SELECT d.id, d.max_spots, 
               (SELECT COUNT(*) FROM dozivetja_prijave WHERE dozivetje_id = d.id AND izbran = 1) as izbrani_count
        FROM dozivetja d 
        WHERE d.code = ?
    ");
    $stmtCheck->bind_param("s", $dozivetje_code);
    $stmtCheck->execute();
    $result = $stmtCheck->get_result();

    if ($row = $result->fetch_assoc()) {
        $prostaMesta = $row['max_spots'] - $row['izbrani_count'];

        if ($prostaMesta <= 0) {
            $stmtCheck->close();
            $conn->close();
            die("Ni več prostih mest za to doživetje.");
        }

        // Update the record to mark as izbran
        if ($prijava_id > 0) {
            $stmtUpdate = $conn->prepare("UPDATE dozivetja_prijave SET izbran = 1 WHERE id = ?");
            $stmtUpdate->bind_param("i", $prijava_id);
            $stmtUpdate->execute();
            $stmtUpdate->close();
        }
    }

    $stmtCheck->close();
    $conn->close();
}

header("Location: /nadzor_dozivetja.php");
die();
?>