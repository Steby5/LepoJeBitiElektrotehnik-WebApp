<?php
session_start();
require 'server_data.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // Get current prijava session ID from server
  $currentSessionId = trim(file_get_contents("prijava_session.txt"));

  // Check if user already registered in THIS session
  if (isset($_COOKIE['ljbe_prijava_session']) && $_COOKIE['ljbe_prijava_session'] === $currentSessionId) {
    header("Location: /hvala_prijava.php?message=already");
    die();
  }

  try {
    $name = mb_convert_encoding($_POST["ime"], 'UTF-8');
    $name = (strlen($name) > 50) ? substr($name, 0, 49) : $name;
    $name = strtolower($name);
    $name = ucwords($name);

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
      die("Povezava s strežnikom ni uspela: " . $conn->connect_error);
    }

    $conn->set_charset("utf8");

    // Use prepared statement
    $stmt = $conn->prepare("INSERT INTO contestants (name) VALUES (?)");
    $stmt->bind_param("s", $name);

    if ($stmt->execute() !== TRUE) {
      echo "Error: " . $conn->error;
    }

    $stmt->close();
    $conn->close();

    // Set cookie with current session ID to prevent duplicate registration in this session
    setcookie('ljbe_prijava_session', $currentSessionId, time() + 86400, '/'); // 24 hours
  } catch (Exception $e) {
    echo 'Prislo je do napake: ' . $e->getMessage();
  }
}

header("Location: /hvala_prijava.php");
die();
?>