<?php
require 'auth_config.php';
require_login();
header('Content-Type: text/html; charset=utf-8');
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['id'])) {
    $dozivetje_id = $_GET['id'];
    file_put_contents("prikaz_dozivetje.txt", $dozivetje_id);
}

header("Location: /nadzor_dozivetja.php");
die();
?>