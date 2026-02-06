<!doctype html>
<html lang="sl" class="h-100" data-bs-theme="auto">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Vodič - Lepo je biti elektrotehnik</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">

    <style>
        body {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            min-height: 100vh;
        }

        .name-card {
            font-size: 1.5rem;
            padding: 1rem 1.5rem;
            margin: 0.5rem 0;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            color: white;
            text-align: center;
        }

        .container-main {
            max-width: 600px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }

        h1 {
            color: white;
            text-align: center;
            margin-bottom: 2rem;
        }

        .no-people {
            color: rgba(255, 255, 255, 0.6);
            text-align: center;
            font-size: 1.2rem;
            padding: 2rem;
        }
    </style>
</head>

<?php
require 'server_data.php';

// Create database connection
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Povezava ni uspela: " . $conn->connect_error);
}
$conn->set_charset("utf8");

// Get all active dozivetja
$dozivetjaResult = $conn->query("SELECT id, name, code FROM dozivetja WHERE active = 1 ORDER BY name");
$dozivetja = [];
while ($row = $dozivetjaResult->fetch_assoc()) {
    $dozivetja[] = $row;
}

$selectedCode = isset($_GET['dozivetje']) ? $_GET['dozivetje'] : '';
$izbrani = [];
$selectedName = '';

if ($selectedCode !== '') {
    // Get izbrani for selected dozivetje
    $stmt = $conn->prepare("
        SELECT dp.name 
        FROM dozivetja_prijave dp
        JOIN dozivetja d ON dp.dozivetje_id = d.id
        WHERE d.code = ? AND dp.izbran = 1
        ORDER BY dp.name
    ");
    $stmt->bind_param("s", $selectedCode);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $izbrani[] = $row['name'];
    }
    $stmt->close();

    // Get dozivetje name
    foreach ($dozivetja as $d) {
        if ($d['code'] === $selectedCode) {
            $selectedName = $d['name'];
            break;
        }
    }
}

$conn->close();
?>

<body>
    <div class="container-main">
        <h1>Vodič</h1>

        <div class="d-flex gap-2 mb-4">
            <select class="form-select form-select-lg" id="dozivetjeSelect"
                onchange="window.location.href='vodic.php?dozivetje=' + this.value">
                <option value="" <?php echo $selectedCode === '' ? 'selected' : ''; ?>>Izberi doživetje</option>
                <?php foreach ($dozivetja as $d): ?>
                    <option value="<?php echo htmlspecialchars($d['code']); ?>" <?php echo ($selectedCode === $d['code']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($d['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button class="btn btn-warning btn-lg" onclick="window.location.reload()">
                ↻
            </button>
        </div>

        <?php if ($selectedCode !== ''): ?>
            <?php if (count($izbrani) > 0): ?>
                <div class="names-list">
                    <?php foreach ($izbrani as $ime): ?>
                        <div class="name-card">
                            <?php echo htmlspecialchars($ime); ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                <p class="text-center text-white-50 mt-3">
                    Skupaj: <?php echo count($izbrani); ?> oseb
                </p>
            <?php else: ?>
                <div class="no-people">
                    Ni izbranih oseb za to doživetje.
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="no-people">
                Izberi doživetje iz menija zgoraj.
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL"
        crossorigin="anonymous"></script>
</body>

</html>