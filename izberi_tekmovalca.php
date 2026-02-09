<?php
require 'auth_config.php';
require_login();
header('Content-Type: text/html; charset=utf-8');

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $name = $_GET['name'];
    $id = intval($_GET['id']);

    // Save selected contestant name to file (for display on quiz screen)
    $result = file_put_contents("izbran_tekmovalec.txt", $name);
    // Also save the ID for reference
    file_put_contents("izbran_tekmovalec_id.txt", $id);

    if ($result === FALSE) {
        die("Napaka pri nastavljanju tekmovalca. Vnesi ročno v programu kviza");
    }

    // Don't modify the database - contestant stays in the list
}

header("Location: /nadzor.php");
die();
?>