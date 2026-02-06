<?php
session_start();
require 'server_data.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // Get current glasovanje session ID from server
  $currentSessionId = trim(file_get_contents("glasovanje_session.txt"));

  // Check if user already voted in THIS session
  if (isset($_COOKIE['ljbe_glasovanje_session']) && $_COOKIE['ljbe_glasovanje_session'] === $currentSessionId) {
    header("Location: /hvala_glasovanje.php?message=Tvoj glas smo že zabeležili.");
    die();
  }

  try {
    $ID = $_POST["Qid"];
    $odgovor = $_POST["odgovor"];

    $sql = "";
    switch ($odgovor) {
      case "A":
        $sql = "UPDATE question SET ACount = ACount + 1 WHERE ID = ?";
        break;
      case "B":
        $sql = "UPDATE question SET BCount = BCount + 1 WHERE ID = ?";
        break;
      case "C":
        $sql = "UPDATE question SET CCount = CCount + 1 WHERE ID = ?";
        break;
      case "D":
        $sql = "UPDATE question SET DCount = DCount + 1 WHERE ID = ?";
        break;
      default:
        $sql = "";
    }

    if ($sql != "") {
      // Create connection
      $conn = new mysqli($servername, $username, $password, $dbname);
      if ($conn->connect_error) {
        die("Povezava s strežnikom ni uspela: " . $conn->connect_error);
      }

      // Use prepared statement
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("i", $ID);

      if ($stmt->execute() !== TRUE) {
        echo "Error: " . $conn->error;
      }

      $stmt->close();
      $conn->close();
    }

    // Set cookie with current session ID to prevent duplicate voting in this session
    setcookie('ljbe_glasovanje_session', $currentSessionId, time() + 86400, '/'); // 24 hours
  } catch (Exception $e) {
    echo 'Prislo je do napake: ' . $e->getMessage();
  }
}

header("Location: /hvala_glasovanje.php");
die();
?>