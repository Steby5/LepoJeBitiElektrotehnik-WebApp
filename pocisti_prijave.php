<?php
require 'auth_config.php';
require_login();
require 'server_data.php';

if ($_SERVER["REQUEST_METHOD"] == "GET") {

  // Create connection
  $conn = new mysqli($servername, $username, $password, $dbname);
  // Check connection
  if ($conn->connect_error) {
    die("Povezava s strežnikom ni uspela: " . $conn->connect_error);
  }

  $sql = "DELETE FROM contestants";

  if ($conn->query($sql) !== TRUE) {
    echo "Error: " . $sql . " " . $conn->error;
  }

  $conn->close();
}
header("Location: /nadzor.php");
die();
?>