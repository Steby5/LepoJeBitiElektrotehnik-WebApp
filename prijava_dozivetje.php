<?php
header('Content-Type: text/html; charset=utf-8');
require 'server_data.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get current dozivetja session ID from server
    $currentSessionId = trim(file_get_contents("dozivetja_session.txt"));

    try {
        // Get and sanitize input
        $ime = mb_convert_encoding($_POST["ime"], 'UTF-8');
        $ime = (strlen($ime) > 50) ? substr($ime, 0, 49) : $ime;
        $ime = strtolower($ime);
        $ime = ucwords($ime);

        // Get selected experiences (now an array)
        $dozivetje_ids = isset($_POST["dozivetje_id"]) ? $_POST["dozivetje_id"] : [];

        if (empty($dozivetje_ids)) {
            die("Prosim izberi vsaj eno doživetje.");
        }

        // Create database connection
        $conn = new mysqli($servername, $username, $password, $dbname);
        if ($conn->connect_error) {
            die("Povezava s strežnikom ni uspela: " . $conn->connect_error);
        }
        $conn->set_charset("utf8");

        // Insert user into each selected experience
        $stmt = $conn->prepare("INSERT INTO dozivetja_prijave (dozivetje_id, name, izbran) VALUES (?, ?, 0)");

        foreach ($dozivetje_ids as $dozivetje_code) {
            // Get dozivetje_id from code
            $stmtGet = $conn->prepare("SELECT id FROM dozivetja WHERE code = ?");
            $stmtGet->bind_param("s", $dozivetje_code);
            $stmtGet->execute();
            $result = $stmtGet->get_result();

            if ($row = $result->fetch_assoc()) {
                $dozivetje_id = $row['id'];
                $stmt->bind_param("is", $dozivetje_id, $ime);
                $stmt->execute();
            }
            $stmtGet->close();
        }

        $stmt->close();
        $conn->close();

        // Set cookie with current session ID to prevent duplicate registration in this session
        setcookie('ljbe_dozivetja_session', $currentSessionId, time() + 86400, '/'); // 24 hours

    } catch (Exception $e) {
        echo 'Prišlo je do napake: ' . $e->getMessage();
    }
}

header("Location: /hvala_dozivetje.php");
die();
?>