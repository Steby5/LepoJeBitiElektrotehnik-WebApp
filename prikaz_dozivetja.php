<!doctype html>
<html lang="sl" class="h-100">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Prikaz doživetja</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <link href="css/premium.css" rel="stylesheet">
</head>

<?php
require 'server_data.php';

$conn = new mysqli($servername, $username, $password, $dbname);
$conn->set_charset("utf8");

// Get currently displayed dozivetje ID from database
$prikazId = '0';
$resS = $conn->query("SELECT setting_value FROM system_settings WHERE setting_key = 'displayed_experience_id'");
if ($rowS = $resS->fetch_assoc()) {
    $prikazId = $rowS['setting_value'];
}
$dozivetjeName = '';
$dozivetjeBarva = '#667eea';
$dozivetjeLetter = '';
$izbrani = [];

if ($prikazId && $prikazId !== '0') {
    $allDoz = $conn->query("SELECT id, code, name, barva FROM dozivetja WHERE active = 1 ORDER BY id DESC");
    $letterIndex = 0;
    while ($row = $allDoz->fetch_assoc()) {
        if ($row['id'] == $prikazId) {
            $dozivetjeName = $row['name'];
            $dozivetjeBarva = $row['barva'] ?: '#667eea';
            $dozivetjeLetter = $row['code'];
            break;
        }
    }

    // Get izbrani for this dozivetje
    $stmtIzb = $conn->prepare("SELECT name FROM dozivetja_prijave WHERE dozivetje_id = ? AND izbran = 1 ORDER BY id DESC");
    $stmtIzb->bind_param("i", $prikazId);
    $stmtIzb->execute();
    $resultIzb = $stmtIzb->get_result();
    while ($row = $resultIzb->fetch_assoc()) {
        $izbrani[] = $row['name'];
    }
    $stmtIzb->close();
}
$conn->close();
?>

<script>
    // Store current prikaz ID to detect changes
    var currentPrikaz = "<?php echo $prikazId; ?>";

    // Check for changes every 3 seconds via API
    setInterval(function () {
        fetch('api_nadzor_dozivetja.php')
            .then(response => response.json())
            .then(data => {
                if (data.prikazId !== currentPrikaz) {
                    window.location.reload();
                }
            })
            .catch(error => console.error('Sync error:', error));
    }, 3000);
</script>

<body class="projection-view">
    <div class="fullscreen-container">
        <div class="aspect-ratio-container">
            <?php if ($prikazId && $prikazId !== '0' && $dozivetjeName): ?>
                <div class="d-flex align-items-center justify-content-center mb-5 gap-4">
                    <div class="letter-box mb-0" style="border-color: <?php echo htmlspecialchars($dozivetjeBarva); ?>; 
                                color: <?php echo htmlspecialchars($dozivetjeBarva); ?>;
                                box-shadow: 0 0 40px <?php echo htmlspecialchars($dozivetjeBarva); ?>40;">
                        <?php echo $dozivetjeLetter; ?>
                    </div>

                    <h1 class="mb-0 text-start"><?php echo htmlspecialchars($dozivetjeName); ?></h1>
                </div>

                <?php if (count($izbrani) > 0): ?>
                    <div class="names-grid">
                        <?php foreach ($izbrani as $ime): ?>
                            <div class="name-card" style="border-left: 4px solid <?php echo htmlspecialchars($dozivetjeBarva); ?>;">
                                <?php echo htmlspecialchars($ime); ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="no-content">
                        Ni še izbranih udeležencev
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="waiting">
                    <i class="bi bi-hourglass-split mb-4 d-block" style="font-size: 8vmin;"></i>
                    Čakam na izbiro doživetja...
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL"
        crossorigin="anonymous"></script>
</body>

</html>