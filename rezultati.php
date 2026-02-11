<?php
require 'server_data.php';

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $id = isset($_GET["id"]) ? $_GET["id"] : "-1"; // get id or take last in the database if no parameter given

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error)
        die("Povezava s strežnikom ni uspela: " . $conn->connect_error);
    $conn->set_charset("utf8");

    if ($id != "-1") {
        $stmt = $conn->prepare("SELECT ID, ACount, BCount, CCount, DCount FROM question WHERE ID = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $resultQ = $stmt->get_result();
        $stmt->close();
    } else {
        $resultQ = $conn->query("SELECT ID, ACount, BCount, CCount, DCount FROM question ORDER BY ID DESC LIMIT 1");
    }
    $conn->close();

    if ($resultQ->num_rows > 0) {
        $row = $resultQ->fetch_assoc();
        $steviloGlasov = (int) $row['ACount'] + (int) $row['BCount'] + (int) $row['CCount'] + (int) $row['DCount'];
        $procentA = 25;
        $procentB = 25;
        $procentC = 25;
        $procentD = 25;
        if ($steviloGlasov > 0) {
            $procentA = round((int) $row['ACount'] / $steviloGlasov * 100);
            $procentB = round((int) $row['BCount'] / $steviloGlasov * 100);
            $procentC = round((int) $row['CCount'] / $steviloGlasov * 100);
            $procentD = round((int) $row['DCount'] / $steviloGlasov * 100);
        }
        echo json_encode(array('ID' => (int) $row['ID'], 'A' => (int) $row['ACount'], 'B' => (int) $row['BCount'], 'C' => (int) $row['CCount'], 'D' => (int) $row['DCount'], 'Ap' => $procentA, 'Bp' => $procentB, 'Cp' => $procentC, 'Dp' => $procentD, 'ABCD' => $steviloGlasov));
    } else {
        echo json_encode(array('ID' => -1, 'A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'Ap' => 25, 'Bp' => 25, 'Cp' => 25, 'Dp' => 25, 'ABCD' => 0));
    }
}
?>