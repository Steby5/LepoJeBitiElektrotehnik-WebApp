<?php
header('Content-Type: text/html; charset=utf-8');
require 'server_data.php';

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    // Create database connection
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Povezava s strežnikom ni uspela: " . $conn->connect_error);
    }
    $conn->set_charset("utf8");

    // Delete all prijave for dozivetja
    $sql = "DELETE FROM dozivetja_prijave";
    $conn->query($sql);

    $conn->close();
}

header("Location: /nadzor_dozivetja.php");
die();
?>