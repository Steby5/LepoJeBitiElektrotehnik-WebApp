<?php
require 'auth_config.php';
require_login();
require 'server_data.php';

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    try {
        $question = isset($_GET["Q"]) ? trim($_GET["Q"]) : "";
        $A = isset($_GET["A"]) ? trim($_GET["A"]) : "";
        $B = isset($_GET["B"]) ? trim($_GET["B"]) : "";
        $C = isset($_GET["C"]) ? trim($_GET["C"]) : "";
        $D = isset($_GET["D"]) ? trim($_GET["D"]) : "";

        if ($question == "" || $A == "" || $B == "" || $C == "" || $D == "") {
            echo "ERR: Missing parameters";
            die();
        }

        // Create connection
        $conn = new mysqli($servername, $username, $password, $dbname);
        // Check connection
        if ($conn->connect_error) {
            die("Povezava s strežnikom ni uspela: " . $conn->connect_error);
        }

        $conn->set_charset("utf8");

        // Use prepared statement to prevent SQL injection
        $stmt = $conn->prepare("INSERT INTO question (questionText, AText, BText, CText, DText) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $question, $A, $B, $C, $D);

        if ($stmt->execute()) {
            echo "OK";
            // Automatically switch view to voting (2)
            file_put_contents("pogled.txt", "2");
        } else {
            echo "ERR: " . $stmt->error;
        }

        $stmt->close();
        $conn->close();
    } catch (Exception $e) {
        echo "ERR: " . $e->getMessage();
    }
}
?>