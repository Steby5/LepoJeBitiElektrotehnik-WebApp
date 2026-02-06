<!doctype html>
<html lang="sl" data-bs-theme="auto">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lepo je biti elektrotehnik - NADZOR DOŽIVETIJ</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@docsearch/css@3">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        * {
            font-family: 'Inter', sans-serif;
        }

        .header-gradient {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            padding: 2rem;
            border-radius: 1rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 40px rgba(245, 87, 108, 0.3);
        }

        .header-gradient h1 {
            margin-bottom: 0.5rem;
            font-weight: 700;
        }

        .header-gradient p {
            opacity: 0.9;
            margin-bottom: 0;
        }

        .nav-tabs-custom {
            background: #f8f9fa;
            border-radius: 0.75rem;
            padding: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .nav-tabs-custom .btn {
            border-radius: 0.5rem;
            font-weight: 500;
            padding: 0.75rem 1.5rem;
        }

        .section-card {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border: 1px solid #e9ecef;
        }

        .section-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #495057;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            padding: 0.5rem 1rem;
            border-radius: 2rem;
            font-weight: 500;
            font-size: 0.875rem;
        }

        .experience-card {
            transition: all 0.2s ease;
            border-radius: 0.75rem !important;
            border: 1px solid #e9ecef;
        }

        .experience-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .experience-card .card-header {
            background: linear-gradient(135deg, #667eea20 0%, #764ba220 100%);
            border-bottom: 1px solid #e9ecef;
            font-weight: 600;
        }

        .btn-group-lg .btn {
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .header-gradient {
                padding: 1.5rem;
            }

            .btn-group {
                flex-wrap: wrap;
            }

            .btn-group .btn {
                margin-bottom: 0.25rem;
            }
        }
    </style>
</head>

<?php
require 'server_data.php';
$view = file_get_contents("pogled.txt");
?>

<script>
    // Function to update dozivetja content via AJAX
    function updateContent() {
        fetch('/api_nadzor_dozivetja.php')
            .then(response => response.json())
            .then(data => {
                data.dozivetja.forEach(d => {
                    // Update prijavljeni list
                    const prijavljeniEl = document.getElementById('prijavljeni-' + d.id);
                    if (prijavljeniEl) {
                        let html = '';
                        if (d.prijavljeni.length > 0) {
                            d.prijavljeni.forEach(p => {
                                html += `<a href="izberi_dozivetje.php?id=${d.code}&ime=${encodeURIComponent(p.name)}&pid=${p.id}" class="btn btn-sm btn-outline-primary">${p.name}</a> `;
                            });
                        } else {
                            html = '<span class="text-muted">Ni prijavljenih</span>';
                        }
                        prijavljeniEl.innerHTML = html;
                    }

                    // Update izbrani list
                    const izbraniEl = document.getElementById('izbrani-' + d.id);
                    if (izbraniEl) {
                        let html = '';
                        if (d.izbrani.length > 0) {
                            d.izbrani.forEach(i => {
                                html += `<a href="odstrani_dozivetje.php?id=${d.id}&pid=${i.id}" class="btn btn-sm btn-success">${i.name} ✓</a> `;
                            });
                        } else {
                            html = '<span class="text-muted">Ni izbranih</span>';
                        }
                        izbraniEl.innerHTML = html;
                    }

                    // Update prosta mesta badge
                    const badgeEl = document.getElementById('badge-' + d.id);
                    if (badgeEl) {
                        const prostaMesta = d.mesta - d.izbrani.length;
                        badgeEl.textContent = `Prosta mesta: ${prostaMesta} / ${d.mesta}`;
                        badgeEl.className = prostaMesta > 0 ? 'badge bg-success ms-2' : 'badge bg-danger ms-2';
                    }
                });
            })
            .catch(error => console.error('Error fetching data:', error));
    }

    // Poll every 2 seconds
    setInterval(updateContent, 2000);

    // Manual refresh button
    function refreshFunction() {
        updateContent();
    }
</script>

<body class="bg-light py-4">
    <main>
        <div class="container">
            <!-- Header -->
            <div class="header-gradient">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h1>🎪 Doživetja nadzor</h1>
                        <p>Upravljanje prijav in izbira udeležencev za doživetja</p>
                    </div>
                    <button type="button" onclick="refreshFunction()" class="btn btn-light btn-lg">
                        🔄 Osveži
                    </button>
                </div>
            </div>

            <!-- Navigation -->
            <div class="nav-tabs-custom d-flex gap-2">
                <a href="nadzor.php" class="btn btn-outline-secondary">🎯 Nadzor kviza</a>
                <a href="nadzor_dozivetja.php" class="btn btn-primary">🎪 Nadzor doživetij</a>
            </div>

            <!-- System Status -->
            <div class="section-card">
                <div class="section-title">📡 Status sistema</div>
                <div class="d-flex flex-wrap gap-3 align-items-center">
                    <span class="text-muted">Prikaz na mobilnih:</span>
                    <div class="btn-group" role="group">
                        <a href="spremeni_pogled.php?view=0&from=dozivetja"
                            class="btn <?php echo ($view == "0") ? 'btn-secondary' : 'btn-outline-secondary'; ?>">⏸️ Ni
                            aktivnosti</a>
                        <a href="spremeni_pogled.php?view=1&from=dozivetja"
                            class="btn <?php echo ($view == "1") ? 'btn-success' : 'btn-outline-success'; ?>">📝 Prijava
                            na kviz</a>
                        <a href="spremeni_pogled.php?view=2&from=dozivetja"
                            class="btn <?php echo ($view == "2") ? 'btn-info' : 'btn-outline-info'; ?>">🗳️ Glas
                            ljudstva</a>
                        <a href="spremeni_pogled.php?view=3&from=dozivetja"
                            class="btn <?php echo ($view == "3") ? 'btn-warning' : 'btn-outline-warning'; ?>">🎪
                            Doživetja</a>
                    </div>
                </div>
            </div>

            <hr class="my-4">
            <h2 class="mt-4">Prikaz na projekciji</h2>

            <?php
            // Get current prikaz info
            $prikazId = trim(file_get_contents("prikaz_dozivetje.txt"));
            $prikazName = "Prazen prikaz";
            if ($prikazId && $prikazId !== '0') {
                $connPrikaz = new mysqli($servername, $username, $password, $dbname);
                $connPrikaz->set_charset("utf8");
                $stmtP = $connPrikaz->prepare("SELECT name FROM dozivetja WHERE id = ?");
                $stmtP->bind_param("i", $prikazId);
                $stmtP->execute();
                $resP = $stmtP->get_result();
                if ($rowP = $resP->fetch_assoc()) {
                    $prikazName = $rowP['name'];
                }
                $stmtP->close();
                $connPrikaz->close();
            }
            ?>

            <div class="card mb-3 border-info">
                <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                    <strong>📺 Trenutni prikaz: <?php echo htmlspecialchars($prikazName); ?></strong>
                    <a href="prikaz_dozivetja.php" target="_blank" class="btn btn-sm btn-light">Odpri stran za
                        projekcijo</a>
                </div>
                <div class="card-body">
                    <p class="mb-2">Izberi kaj prikazati:</p>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="nastavi_prikaz_dozivetja.php?id=0"
                            class="btn <?php echo ($prikazId == '0') ? 'btn-secondary' : 'btn-outline-secondary'; ?>">
                            ⬛ Prazen prikaz
                        </a>
                        <?php
                        // Get all active dozivetja for display buttons
                        $connButtons = new mysqli($servername, $username, $password, $dbname);
                        $connButtons->set_charset("utf8");
                        $btnResult = $connButtons->query("SELECT id, name FROM dozivetja WHERE active = 1 ORDER BY name");
                        while ($btn = $btnResult->fetch_assoc()) {
                            $isActive = ($prikazId == $btn['id']) ? 'btn-info' : 'btn-outline-info';
                            echo '<a href="nastavi_prikaz_dozivetja.php?id=' . $btn['id'] . '" class="btn ' . $isActive . '">' . htmlspecialchars($btn['name']) . '</a>';
                        }
                        $connButtons->close();
                        ?>
                    </div>
                </div>
            </div>

            <hr class="my-4">
            <h2 class="mt-4">Doživetja</h2>

            <?php if (isset($_GET['error']) && $_GET['error'] === 'already_selected'): ?>
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <strong>Opozorilo:</strong> Ta oseba je že izbrana za drugo doživetje!
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['upload']) && $_GET['upload'] === 'success'): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    JSON datoteka uspešno naložena!
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="card mb-3">
                <div class="card-header">
                    <strong>Naloži JSON datoteko za doživetja</strong>
                </div>
                <div class="card-body">
                    <form action="nalozi_dozivetja.php" method="post" enctype="multipart/form-data">
                        <div class="input-group">
                            <input type="file" class="form-control" name="json_file" accept=".json,application/json"
                                required>
                            <button type="submit" class="btn btn-primary">Naloži</button>
                        </div>
                        <small class="text-muted">Format: {"options": [{"id": "...", "dozivetje": "...", "mesta": ...},
                            ...]}</small>
                    </form>
                </div>
            </div>

            <p>Upravljanje prijav na doživetja. Klikni na ime za izbiro/odstranitev.</p>
            <?php
            // Read dozivetja from database
            $connDoz = new mysqli($servername, $username, $password, $dbname);
            $connDoz->set_charset("utf8");

            $dozQuery = "SELECT d.id, d.name, d.code, d.max_spots,
                          (SELECT COUNT(*) FROM dozivetja_prijave WHERE dozivetje_id = d.id AND izbran = 1) as izbrani_count
                   FROM dozivetja d WHERE d.active = 1 ORDER BY d.name";
            $dozResult = $connDoz->query($dozQuery);

            if ($dozResult && $dozResult->num_rows > 0) {
                while ($option = $dozResult->fetch_assoc()) {
                    $prostaMesta = $option['max_spots'] - $option['izbrani_count'];

                    // Get prijavljeni (izbran = 0)
                    $stmtPrij = $connDoz->prepare("SELECT id, name FROM dozivetja_prijave WHERE dozivetje_id = ? AND izbran = 0 ORDER BY time");
                    $stmtPrij->bind_param("i", $option['id']);
                    $stmtPrij->execute();
                    $prijavljeni = $stmtPrij->get_result()->fetch_all(MYSQLI_ASSOC);
                    $stmtPrij->close();

                    // Get izbrani (izbran = 1)
                    $stmtIzb = $connDoz->prepare("SELECT id, name FROM dozivetja_prijave WHERE dozivetje_id = ? AND izbran = 1 ORDER BY time");
                    $stmtIzb->bind_param("i", $option['id']);
                    $stmtIzb->execute();
                    $izbrani = $stmtIzb->get_result()->fetch_all(MYSQLI_ASSOC);
                    $stmtIzb->close();
                    ?>
                    <div class="card mb-3">
                        <div class="card-header">
                            <strong>
                                <?php echo htmlspecialchars($option['name']); ?>
                            </strong>
                            <span id="badge-<?php echo $option['id']; ?>"
                                class="badge bg-<?php echo $prostaMesta > 0 ? 'success' : 'danger'; ?> ms-2">
                                Prosta mesta:
                                <?php echo $prostaMesta; ?> /
                                <?php echo $option['max_spots']; ?>
                            </span>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-6">
                                    <h6>Prijavljeni</h6>
                                    <div id="prijavljeni-<?php echo $option['id']; ?>" class="d-flex flex-wrap gap-2">
                                        <?php
                                        if (count($prijavljeni) > 0) {
                                            foreach ($prijavljeni as $oseba) {
                                                // Check if person is already selected in ANY experience
                                                $stmtCheck = $connDoz->prepare("SELECT COUNT(*) as cnt FROM dozivetja_prijave WHERE name = ? AND izbran = 1");
                                                $stmtCheck->bind_param("s", $oseba['name']);
                                                $stmtCheck->execute();
                                                $checkResult = $stmtCheck->get_result()->fetch_assoc();
                                                $isAlreadySelected = $checkResult['cnt'] > 0;
                                                $stmtCheck->close();

                                                if ($isAlreadySelected) {
                                                    echo '<button class="btn btn-sm btn-outline-dark" disabled title="Že izbran/-a za drugo doživetje">⚠️ ' . htmlspecialchars($oseba['name']) . '</button>';
                                                } elseif ($prostaMesta > 0) {
                                                    echo '<a href="izberi_dozivetje.php?id=' . urlencode($option['code']) . '&ime=' . urlencode($oseba['name']) . '&pid=' . $oseba['id'] . '" class="btn btn-sm btn-outline-primary">' . htmlspecialchars($oseba['name']) . '</a>';
                                                } else {
                                                    echo '<button class="btn btn-sm btn-outline-secondary" disabled>' . htmlspecialchars($oseba['name']) . '</button>';
                                                }
                                            }
                                        } else {
                                            echo '<span class="text-muted">Ni prijavljenih</span>';
                                        }
                                        ?>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <h6>Izbrani</h6>
                                    <div id="izbrani-<?php echo $option['id']; ?>" class="d-flex flex-wrap gap-2">
                                        <?php
                                        if (count($izbrani) > 0) {
                                            foreach ($izbrani as $oseba) {
                                                echo '<a href="odstrani_dozivetje.php?id=' . urlencode($option['code']) . '&ime=' . urlencode($oseba['name']) . '&pid=' . $oseba['id'] . '" class="btn btn-sm btn-success">' . htmlspecialchars($oseba['name']) . '</a>';
                                            }
                                        } else {
                                            echo '<span class="text-muted">Ni izbranih</span>';
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer text-end">
                            <a href="pocisti_eno_dozivetje.php?id=<?php echo $option['id']; ?>"
                                class="btn btn-sm btn-outline-danger"
                                onclick="return confirm('Res želiš izbrisati vse prijave za <?php echo htmlspecialchars($option['name']); ?>?');">
                                Izbriši prijave za to doživetje
                            </a>
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo '<p class="text-muted">Ni podatkov o doživetjih. Naloži JSON datoteko zgoraj.</p>';
            }
            $connDoz->close();
            ?>
            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#clearDozivetja">Počisti
                vse
                sezname doživetij</button>

            <!-- Modal za čiščenje doživetij -->
            <div class="modal fade" id="clearDozivetja" tabindex="-1" aria-labelledby="clearDozivetjaLabel"
                aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="clearDozivetjaLabel">Potrdi odločitev</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            Res želiš izbrisati vse vnose prijav in izbir za doživetja?
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Prekliči</button>
                            <a href="pocisti_dozivetja.php" type="button" class="btn btn-danger"
                                role="button">Izbriši</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL"
        crossorigin="anonymous"></script>
</body>

</html>