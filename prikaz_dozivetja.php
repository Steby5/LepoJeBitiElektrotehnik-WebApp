<!doctype html>
<html lang="sl" class="h-100">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Prikaz doživetja</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
        * {
            font-family: 'Outfit', sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html,
        body {
            height: 100%;
            overflow: hidden;
            background: #000;
        }

        .aspect-ratio-container {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 100vw;
            height: calc(100vw * 9 / 16);
            max-height: 100vh;
            max-width: calc(100vh * 16 / 9);
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 50%, #1e3c72 100%);
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 3%;
        }

        h1 {
            font-size: 5vmin;
            font-weight: 700;
            text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.3);
            margin-bottom: 4vmin;
            text-align: center;
        }

        .names-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2vmin;
            width: 90%;
            max-width: 1100px;
        }

        .name-card {
            font-size: 2.5vmin;
            font-weight: 600;
            padding: 1.5vmin 2vmin;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            border-radius: 1.5vmin;
            border: 1px solid rgba(255, 255, 255, 0.2);
            text-shadow: 1px 1px 4px rgba(0, 0, 0, 0.2);
            text-align: center;
        }

        .no-content {
            font-size: 3vmin;
            opacity: 0.7;
        }

        .waiting {
            font-size: 4vmin;
            opacity: 0.6;
        }
    </style>
</head>

<?php
require 'server_data.php';

// Get currently displayed dozivetje ID
$prikazId = trim(file_get_contents("prikaz_dozivetje.txt"));
$dozivetjeName = '';
$izbrani = [];

if ($prikazId && $prikazId !== '0') {
    // Create database connection
    $conn = new mysqli($servername, $username, $password, $dbname);
    $conn->set_charset("utf8");

    // Get dozivetje name
    $stmtName = $conn->prepare("SELECT name FROM dozivetja WHERE id = ?");
    $stmtName->bind_param("i", $prikazId);
    $stmtName->execute();
    $result = $stmtName->get_result();
    if ($row = $result->fetch_assoc()) {
        $dozivetjeName = $row['name'];
    }
    $stmtName->close();

    // Get izbrani for this dozivetje
    $stmtIzb = $conn->prepare("SELECT name FROM dozivetja_prijave WHERE dozivetje_id = ? AND izbran = 1 ORDER BY name");
    $stmtIzb->bind_param("i", $prikazId);
    $stmtIzb->execute();
    $resultIzb = $stmtIzb->get_result();
    while ($row = $resultIzb->fetch_assoc()) {
        $izbrani[] = $row['name'];
    }
    $stmtIzb->close();

    $conn->close();
}
?>

<script>
    // Store current prikaz ID to detect changes
    var currentPrikaz = "<?php echo $prikazId; ?>";

    // Check for changes every 2 seconds
    setInterval(function () {
        fetch('prikaz_dozivetje.txt?t=' + Date.now())
            .then(response => response.text())
            .then(newId => {
                if (newId.trim() !== currentPrikaz) {
                    window.location.reload();
                }
            });
    }, 2000);
</script>

<body>
    <div class="aspect-ratio-container">
        <?php if ($prikazId && $prikazId !== '0' && $dozivetjeName): ?>
            <h1><?php echo htmlspecialchars($dozivetjeName); ?></h1>

            <?php if (count($izbrani) > 0): ?>
                <div class="names-grid">
                    <?php foreach ($izbrani as $ime): ?>
                        <div class="name-card">
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
                Čakam na izbiro doživetja...
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL"
        crossorigin="anonymous"></script>
</body>

</html>