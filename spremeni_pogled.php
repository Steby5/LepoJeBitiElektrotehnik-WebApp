<?php
require 'auth_config.php';
require_login();
require 'server_data.php';

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $view = $_GET['view'];
    $from = isset($_GET['from']) ? $_GET['from'] : 'nadzor';

    $conn = new mysqli($servername, $username, $password, $dbname);
    if (!$conn->connect_error) {
        $conn->set_charset("utf8");
        $stmt = $conn->prepare("UPDATE system_settings SET setting_value = ? WHERE setting_key = 'current_view'");
        $stmt->bind_param("s", $view);
        $stmt->execute();
        $stmt->close();
        $conn->close();
    }

    // When switching to prijava (view=1), generate new session ID
    if ($view == "1") {
        $newSessionId = time() . '_' . uniqid();
        file_put_contents("prijava_session.txt", $newSessionId);
    }

    // When switching to glasovanje (view=2), generate new session ID
    if ($view == "2") {
        $newSessionId = time() . '_' . uniqid();
        file_put_contents("glasovanje_session.txt", $newSessionId);
    }

    // When switching to dozivetja (view=3), generate new session ID
    if ($view == "3") {
        $newSessionId = time() . '_' . uniqid();
        file_put_contents("dozivetja_session.txt", $newSessionId);
    }

    // Redirect back to the page that called this
    if ($from == 'dozivetja') {
        header("Location: nadzor_dozivetja.php");
    } else {
        header("Location: nadzor.php");
    }
    die();
}

header("Location: nadzor.php");
die();
?>