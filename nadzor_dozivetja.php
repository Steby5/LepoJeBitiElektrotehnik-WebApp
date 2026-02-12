<?php require 'auth_config.php';
require_login(); ?>
<!doctype html>
<html lang="sl" data-bs-theme="dark">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Doživetja | Lepo je biti elektrotehnik</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <link href="css/premium.css" rel="stylesheet">
</head>

<?php
require 'server_data.php';

$conn = new mysqli($servername, $username, $password, $dbname);
$view = "0";
$prikazId = "0";
$prikazName = "Prazen prikaz";

if (!$conn->connect_error) {
    $conn->set_charset("utf8");

    // Get view and display settings
    $resS = $conn->query("SELECT setting_key, setting_value FROM system_settings WHERE setting_key IN ('current_view', 'displayed_experience_id')");
    $settings = [];
    while ($sRow = $resS->fetch_assoc()) {
        $settings[$sRow['setting_key']] = $sRow['setting_value'];
    }

    $view = isset($settings['current_view']) ? $settings['current_view'] : "0";
    $prikazId = isset($settings['displayed_experience_id']) ? $settings['displayed_experience_id'] : "0";

    // Get display name
    if ($prikazId && $prikazId !== '0') {
        $stmtP = $conn->prepare("SELECT name FROM dozivetja WHERE id = ?");
        $stmtP->bind_param("i", $prikazId);
        $stmtP->execute();
        $resP = $stmtP->get_result();
        if ($rowP = $resP->fetch_assoc()) {
            $prikazName = $rowP['name'];
        }
        $stmtP->close();
    }
}
?>

<script>
    var pendingSelections = {};

    function toggleSelection(dozId, personId, personName, element) {
        if (!pendingSelections[dozId]) {
            pendingSelections[dozId] = [];
        }

        const index = pendingSelections[dozId].findIndex(p => p.id === personId);
        if (index > -1) {
            pendingSelections[dozId].splice(index, 1);
            element.classList.remove('pending');
        } else {
            pendingSelections[dozId].push({ id: personId, name: personName });
            element.classList.add('pending');
        }

        updatePendingDisplay(dozId);
    }

    function updatePendingDisplay(dozId) {
        const countEl = document.getElementById('pending-count-' + dozId);
        const listEl = document.getElementById('pending-list-' + dozId);
        const confirmBtn = document.getElementById('confirm-btn-' + dozId);
        const available = countEl.getAttribute('data-total');

        if (!pendingSelections[dozId] || pendingSelections[dozId].length === 0) {
            countEl.textContent = '0/' + available;
            listEl.innerHTML = '<span class="text-secondary" style="font-size: 0.85rem;">Klikni na imena za izbiro</span>';
            confirmBtn.disabled = true;
        } else {
            countEl.textContent = pendingSelections[dozId].length + '/' + available;
            listEl.innerHTML = pendingSelections[dozId].map(p =>
                '<span class="pending-tag">' + p.name + '</span>'
            ).join('');
            confirmBtn.disabled = false;
        }
    }

    function confirmSelections(dozId, dozCode) {
        if (!pendingSelections[dozId] || pendingSelections[dozId].length === 0) {
            return;
        }

        const personIds = pendingSelections[dozId].map(p => p.id);
        const formData = new FormData();
        formData.append('dozivetje_id', dozId);
        formData.append('dozivetje_code', dozCode);
        formData.append('person_ids', JSON.stringify(personIds));

        fetch('potrdi_izbiro_dozivetja.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    alert('Napaka: ' + (data.error || 'Neznana napaka'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Napaka pri potrjevanju izbire.');
            });
    }

    function drawRandomForDozivetje(dozId, prostaMesta) {
        fetch('api_nadzor_dozivetja.php')
            .then(response => response.json())
            .then(data => {
                const doz = data.dozivetja.find(d => d.id == dozId);
                if (!doz) return;

                if (!pendingSelections[dozId]) pendingSelections[dozId] = [];

                const needed = prostaMesta - pendingSelections[dozId].length;
                if (needed <= 0) {
                    alert("Vsa mesta so že zapolnjena.");
                    return;
                }

                // Get all names currently pending in OTHER experiences
                const otherPendingNames = [];
                for (const dId in pendingSelections) {
                    if (dId != dozId) {
                        pendingSelections[dId].forEach(p => otherPendingNames.push(p.name));
                    }
                }

                // Filter available candidates
                const available = doz.prijavljeni.filter(p => {
                    // Not globally selected
                    if (data.selected_names.includes(p.name)) return false;
                    // Not already pending in this experience
                    if (pendingSelections[dozId].some(ps => ps.id === p.id)) return false;
                    // Not already pending in OTHER experiences
                    if (otherPendingNames.includes(p.name)) return false;
                    return true;
                });

                if (available.length === 0) {
                    alert("Ni več razpoložljivih prijavljenih kandidatov.");
                    return;
                }

                // Shuffle and pick
                const shuffled = available.sort(() => 0.5 - Math.random());
                const picked = shuffled.slice(0, needed);

                picked.forEach(p => {
                    pendingSelections[dozId].push({ id: p.id, name: p.name });
                    const btn = document.getElementById(`person-btn-${dozId}-${p.id}`);
                    if (btn) btn.classList.add('pending');
                });

                updatePendingDisplay(dozId);
            })
            .catch(err => {
                console.error('Error:', err);
                alert('Napaka pri pridobivanju podatkov.');
            });
    }

    function refreshFunction() {
        window.location.reload();
    }

    function renderGrids(dozId, prijavljeni, izbrani, prostaMesta, maxSpots, barva) {
        const prijavljeniGrid = document.getElementById('prijavljeni-grid-' + dozId);
        const izbraniGrid = document.getElementById('izbrani-grid-' + dozId);
        const spotsBadge = document.getElementById('spots-badge-' + dozId);
        const pendingCount = document.getElementById('pending-count-' + dozId);

        if (spotsBadge) {
            spotsBadge.className = 'spots-badge ' + (prostaMesta > 0 ? 'available' : 'full');
            spotsBadge.innerHTML = `<i class="bi bi-person-fill me-1"></i> ${prostaMesta} / ${maxSpots}`;
        }

        if (pendingCount) {
            pendingCount.setAttribute('data-total', prostaMesta);
            // Also need to adjust the fraction display if pending exists
            const currentPendingCount = (pendingSelections[dozId] || []).length;
            pendingCount.textContent = currentPendingCount + '/' + prostaMesta;
        }

        if (prijavljeniGrid) {
            if (prijavljeni.length > 0) {
                let html = '';
                prijavljeni.forEach(p => {
                    // Check if already selected elsewhere (from API) - currently not passed but we can optimize this
                    // For now, let's focus on basic list update
                    const isPending = pendingSelections[dozId] && pendingSelections[dozId].some(ps => ps.id == p.id);
                    const pendingClass = isPending ? ' pending' : '';
                    const safeName = p.name.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
                    const escapedJsonName = p.name.replace(/\\/g, '\\\\').replace(/'/g, "\\'");

                    if (prostaMesta > 0) {
                        html += `<button class="person-btn${pendingClass}" id="person-btn-${dozId}-${p.id}" onclick="toggleSelection(${dozId}, ${p.id}, '${escapedJsonName}', this)">${safeName}</button>`;
                    } else {
                        html += `<button class="person-btn" id="person-btn-${dozId}-${p.id}" disabled>${safeName}</button>`;
                    }
                });
                prijavljeniGrid.innerHTML = html;
            } else {
                prijavljeniGrid.innerHTML = '<span class="text-secondary small">Ni prijavljenih</span>';
            }
        }

        if (izbraniGrid) {
            if (izbrani.length > 0) {
                let html = '';
                izbrani.forEach(p => {
                    const safeName = p.name.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
                    html += `<a href="odstrani_dozivetje.php?id=${dozId}&ime=${encodeURIComponent(p.name)}&pid=${p.id}" class="person-btn selected small">${safeName} <i class="bi bi-check"></i></a>`;
                });
                izbraniGrid.innerHTML = html;
            } else {
                izbraniGrid.innerHTML = '<span class="text-secondary small">Ni izbranih</span>';
            }
        }
    }

    // AJAX Synchronization
    var currentView = "<?php echo $view; ?>";
    var currentPrikaz = "<?php echo $prikazId; ?>";

    setInterval(function () {
        fetch('api_nadzor_dozivetja.php')
            .then(response => response.json())
            .then(data => {
                // Update grids for each experience
                data.dozivetja.forEach(doz => {
                    const prostaMesta = doz.max_spots - doz.izbrani.length;
                    renderGrids(doz.id, doz.prijavljeni, doz.izbrani, prostaMesta, doz.max_spots, doz.barva);
                });

                // If system view changed, redirect to show proper buttons
                if (data.view !== currentView) {
                    window.location.reload();
                }

                // If displayed experience changed, update active classes
                if (data.prikazId !== currentPrikaz) {
                    currentPrikaz = data.prikazId;
                    window.location.reload();
                }
            })
            .catch(error => console.error('Sync error:', error));
    }, 3000);
</script>

<body>
    <div class="container-fluid py-4">
        <!-- Header -->


        <!-- Combined Navigation & Status -->
        <div class="glass-card d-flex flex-wrap align-items-center gap-4 mb-4">
            <!-- Navigation Section -->
            <div>
                <h6 class="text-secondary mb-2 text-uppercase fw-bold"
                    style="font-size: 0.75rem; letter-spacing: 0.05em;">Navigacija</h6>
                <div class="nav-pills-custom mb-0">
                    <a href="nadzor.php" class="nav-link">
                        <i class="bi bi-controller"></i> Nadzor kviza
                    </a>
                    <a href="nadzor_dozivetja.php" class="nav-link active">
                        <i class="bi bi-stars"></i> Nadzor doživetij
                    </a>
                </div>
            </div>

            <!-- Divider (Desktop only) -->
            <div class="d-none d-md-block" style="width: 1px; height: 40px; background: var(--border-color);"></div>

            <!-- Status Section -->
            <div class="flex-grow-1">
                <h6 class="text-secondary mb-2 text-uppercase fw-bold"
                    style="font-size: 0.75rem; letter-spacing: 0.05em;">Status sistema</h6>
                <div class="status-btn-group">
                    <a href="spremeni_pogled.php?view=0&from=dozivetja"
                        class="status-btn inactive <?php echo ($view == "0") ? 'active' : ''; ?>">
                        <i class="bi bi-pause-circle"></i> Ni aktivnosti
                    </a>
                    <a href="spremeni_pogled.php?view=1&from=dozivetja"
                        class="status-btn quiz <?php echo ($view == "1") ? 'active' : ''; ?>">
                        <i class="bi bi-pencil-square"></i> Prijava na kviz
                    </a>
                    <a href="spremeni_pogled.php?view=2&from=dozivetja"
                        class="status-btn vote <?php echo ($view == "2") ? 'active' : ''; ?>">
                        <i class="bi bi-hand-thumbs-up"></i> Glas ljudstva
                    </a>
                    <a href="spremeni_pogled.php?view=3&from=dozivetja"
                        class="status-btn experience <?php echo ($view == "3") ? 'active' : ''; ?>">
                        <i class="bi bi-stars"></i> Doživetja
                    </a>
                </div>
            </div>

            <!-- Divider (Desktop only) -->
            <div class="d-none d-md-block" style="width: 1px; height: 40px; background: var(--border-color);"></div>

            <!-- Actions Section -->
            <div>
                <h6 class="text-secondary mb-2 text-uppercase fw-bold"
                    style="font-size: 0.75rem; letter-spacing: 0.05em;">Sistem</h6>
                <div class="d-flex gap-2">
                    <button onclick="refreshFunction()" class="status-btn" title="Osveži"
                        style="width: auto; padding: 0.5rem 1rem;">
                        <i class="bi bi-arrow-clockwise"></i>
                    </button>
                    <a href="logout.php" class="status-btn" title="Odjava"
                        style="width: auto; padding: 0.5rem 1rem; color: var(--accent-danger); border-color: rgba(239, 68, 68, 0.3);">
                        <i class="bi bi-box-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Settings: Projection Analysis & Upload (Moved Up) -->
        <h2 class="mb-3" style="font-size: 1.25rem;">Nastavitve</h2>
        <div class="row g-3 mb-4">
            <!-- Projection -->
            <div class="col-md-6">
                <div class="glass-card compact-card projection-card h-100">
                    <div class="section-header">
                        <div class="icon" style="background: linear-gradient(135deg, var(--accent-info), #0891b2);">
                            <i class="bi bi-display text-white"></i>
                        </div>
                        <h2>Prikaz</h2>
                        <a href="prikaz_dozivetja.php" target="_blank" class="btn-refresh ms-auto"
                            style="padding: 0.25rem 0.5rem; font-size: 0.75rem;">
                            <i class="bi bi-box-arrow-up-right"></i>
                        </a>
                    </div>
                    <p class="text-secondary mb-1">Trenutno: <strong
                            class="text-white"><?php echo htmlspecialchars($prikazName); ?></strong></p>
                    <div class="projection-buttons" style="padding: 0.5rem;">
                        <a href="nastavi_prikaz_dozivetja.php?id=0"
                            class="projection-btn <?php echo ($prikazId == '0') ? 'active' : ''; ?>">
                            <i class="bi bi-square me-1"></i> Prazen
                        </a>
                        <?php
                        $connButtons = new mysqli($servername, $username, $password, $dbname);
                        $connButtons->set_charset("utf8");
                        $btnResult = $connButtons->query("SELECT id, name FROM dozivetja WHERE active = 1 ORDER BY id");
                        while ($btn = $btnResult->fetch_assoc()) {
                            $isActive = ($prikazId == $btn['id']) ? 'active' : '';
                            echo '<a href="nastavi_prikaz_dozivetja.php?id=' . $btn['id'] . '" class="projection-btn ' . $isActive . '">' . htmlspecialchars($btn['name']) . '</a>';
                        }
                        $connButtons->close();
                        ?>
                    </div>
                </div>
            </div>

            <!-- Upload -->
            <div class="col-md-4">
                <div class="glass-card compact-card h-100 d-flex flex-column">
                    <div class="section-header">
                        <div class="icon"
                            style="background: linear-gradient(135deg, var(--accent-primary), var(--accent-secondary));">
                            <i class="bi bi-cloud-upload text-white"></i>
                        </div>
                        <h2>Naloži JSON</h2>
                    </div>
                    <form action="nalozi_dozivetja.php" method="post" enctype="multipart/form-data"
                        class="flex-grow-1 d-flex flex-column">
                        <div class="upload-box flex-grow-1 d-flex">
                            <label for="json_file"
                                class="d-flex flex-row align-items-center justify-content-center gap-3 flex-grow-1 w-100"
                                style="padding: 0.5rem;">
                                <i class="bi bi-file-earmark-code upload-icon mb-0"></i>
                                <span class="text-secondary">Izberi JSON datoteko</span>
                                <input type="file" name="json_file" id="json_file" accept=".json,application/json"
                                    required onchange="this.form.submit()">
                            </label>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Clear -->
            <div class="col-md-2">
                <div class="glass-card compact-card h-100 d-flex flex-column">
                    <div class="section-header">
                        <div class="icon"
                            style="background: linear-gradient(135deg, var(--accent-danger), var(--accent-secondary));">
                            <i class="bi bi-trash3 text-white"></i>
                        </div>
                        <h2 style="font-size: 0.8rem; white-space: nowrap;">Počisti vse</h2>
                    </div>
                    <button type="button"
                        class="btn-action d-flex flex-column align-items-center justify-content-center flex-grow-1 w-100"
                        data-bs-toggle="modal" data-bs-target="#clearDozivetja" title="Počisti vse">
                        <i class="bi bi-trash3" style="font-size: 1.5rem;"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Alerts -->
        <?php if (isset($_GET['confirmed'])): ?>
            <div class="custom-alert">
                <i class="bi bi-check-circle-fill"></i>
                Izbira potrjena! Izbrano je bilo <?php echo intval($_GET['confirmed']); ?> oseb.
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error']) && $_GET['error'] === 'already_selected'): ?>
            <div class="custom-alert warning">
                <i class="bi bi-exclamation-triangle-fill"></i>
                Ta oseba je že izbrana za drugo doživetje!
            </div>
        <?php endif; ?>


        <!-- Experience Cards (Grid Layout) -->
        <div class="row g-4">
            <?php
            $dozQuery = "SELECT d.id, d.name, d.code, d.max_spots, d.barva,
                      (SELECT COUNT(*) FROM dozivetja_prijave WHERE dozivetje_id = d.id AND izbran = 1) as izbrani_count
               FROM dozivetja d WHERE d.active = 1 ORDER BY d.id";
            $dozResult = $conn->query($dozQuery);

            if ($dozResult && $dozResult->num_rows > 0) {
                while ($option = $dozResult->fetch_assoc()) {
                    $letter = $option['code'];
                    $prostaMesta = $option['max_spots'] - $option['izbrani_count'];
                    $barva = $option['barva'] ?: '#8b5cf6';

                    $stmtPrij = $conn->prepare("SELECT id, name FROM dozivetja_prijave WHERE dozivetje_id = ? AND izbran = 0 ORDER BY time");
                    $stmtPrij->bind_param("i", $option['id']);
                    $stmtPrij->execute();
                    $prijavljeni = $stmtPrij->get_result()->fetch_all(MYSQLI_ASSOC);
                    $stmtPrij->close();

                    $stmtIzb = $conn->prepare("SELECT id, name FROM dozivetja_prijave WHERE dozivetje_id = ? AND izbran = 1 ORDER BY time");
                    $stmtIzb->bind_param("i", $option['id']);
                    $stmtIzb->execute();
                    $izbrani = $stmtIzb->get_result()->fetch_all(MYSQLI_ASSOC);
                    $stmtIzb->close();
                    ?>

                    <div class="col-lg-6 col-xl-4 d-flex">
                        <div class="experience-card flex-fill d-flex flex-column">
                            <div class="experience-header">
                                <div class="letter-badge"
                                    style="border-color: <?php echo htmlspecialchars($barva); ?>; color: <?php echo htmlspecialchars($barva); ?>; box-shadow: 0 0 15px <?php echo htmlspecialchars($barva); ?>40;">
                                    <?php echo htmlspecialchars($letter); ?>
                                </div>
                                <div class="experience-info flex-grow-1">
                                    <h3 class="text-truncate" title="<?php echo htmlspecialchars($option['name']); ?>">
                                        <?php echo htmlspecialchars($option['name']); ?>
                                    </h3>
                                    <div class="d-flex align-items-center justify-content-between">
                                        <span id="spots-badge-<?php echo $option['id']; ?>"
                                            class="spots-badge <?php echo $prostaMesta > 0 ? 'available' : 'full'; ?>">
                                            <i class="bi bi-person-fill me-1"></i>
                                            <?php echo $prostaMesta; ?> / <?php echo $option['max_spots']; ?>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="experience-body flex-grow-1">
                                <div class="row">
                                    <div class="col-12 mb-3">
                                        <div class="people-section">
                                            <h4><i class="bi bi-people"></i> Prijavljeni</h4>
                                            <div id="prijavljeni-grid-<?php echo $option['id']; ?>" class="people-grid"
                                                style="max-height: 150px; overflow-y: auto;">
                                                <?php
                                                if (count($prijavljeni) > 0) {
                                                    foreach ($prijavljeni as $oseba) {
                                                        $stmtCheck = $conn->prepare("SELECT COUNT(*) as cnt FROM dozivetja_prijave WHERE name = ? AND izbran = 1");
                                                        $stmtCheck->bind_param("s", $oseba['name']);
                                                        $stmtCheck->execute();
                                                        $checkResult = $stmtCheck->get_result()->fetch_assoc();
                                                        $isAlreadySelected = $checkResult['cnt'] > 0;
                                                        $stmtCheck->close();

                                                        if ($isAlreadySelected) {
                                                            echo '<button class="person-btn warning" disabled title="Že izbran/-a drugje"><i class="bi bi-exclamation-triangle me-1"></i>' . htmlspecialchars($oseba['name']) . '</button>';
                                                        } elseif ($prostaMesta > 0) {
                                                            echo '<button class="person-btn" id="person-btn-' . $option['id'] . '-' . $oseba['id'] . '" onclick="toggleSelection(' . $option['id'] . ', ' . $oseba['id'] . ', \'' . addslashes(htmlspecialchars($oseba['name'])) . '\', this)">' . htmlspecialchars($oseba['name']) . '</button>';
                                                        } else {
                                                            echo '<button class="person-btn" id="person-btn-' . $option['id'] . '-' . $oseba['id'] . '" disabled>' . htmlspecialchars($oseba['name']) . '</button>';
                                                        }
                                                    }
                                                } else {
                                                    echo '<span class="text-secondary small">Ni prijavljenih</span>';
                                                }
                                                ?>
                                            </div>

                                            <div class="pending-box mt-3">
                                                <div class="pending-header mb-2">
                                                    <h5 class="small"><i class="bi bi-clock-history me-1"></i> Označeni: <span
                                                            id="pending-count-<?php echo $option['id']; ?>"
                                                            data-total="<?php echo $prostaMesta; ?>">0/<?php echo $prostaMesta; ?></span>
                                                    </h5>
                                                    <div class="d-flex gap-2">
                                                        <?php if ($prostaMesta > 0): ?>
                                                            <button type="button" class="btn-action p-1"
                                                                onclick="drawRandomForDozivetje(<?php echo $option['id']; ?>, <?php echo $prostaMesta; ?>)"
                                                                title="Žrebaj preostale"
                                                                style="background: var(--accent-info); color: white; border: none; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; border-radius: 4px;">
                                                                <i class="bi bi-dice-5"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                        <button type="button" id="confirm-btn-<?php echo $option['id']; ?>"
                                                            class="btn-confirm btn-sm py-1 px-2" disabled
                                                            onclick="confirmSelections(<?php echo $option['id']; ?>, '<?php echo addslashes($option['code']); ?>')">
                                                            <i class="bi bi-check2-circle"></i> Potrdi
                                                        </button>
                                                    </div>
                                                </div>
                                                <div id="pending-list-<?php echo $option['id']; ?>" class="pending-list"
                                                    style="min-height: 1rem;">
                                                    <span class="text-secondary" style="font-size: 0.75rem;">Klikni na
                                                        imena</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="people-section">
                                            <h4><i class="bi bi-check-circle"></i> Izbrani</h4>
                                            <div id="izbrani-grid-<?php echo $option['id']; ?>" class="people-grid"
                                                style="max-height: 100px; overflow-y: auto;">
                                                <?php
                                                if (count($izbrani) > 0) {
                                                    foreach ($izbrani as $oseba) {
                                                        echo '<a href="odstrani_dozivetje.php?id=' . urlencode($option['code']) . '&ime=' . urlencode($oseba['name']) . '&pid=' . $oseba['id'] . '" class="person-btn selected small">' . htmlspecialchars($oseba['name']) . ' <i class="bi bi-check"></i></a>';
                                                    }
                                                } else {
                                                    echo '<span class="text-secondary small">Ni izbranih</span>';
                                                }
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="experience-footer mt-auto">
                                <a href="pocisti_eno_dozivetje.php?id=<?php echo $option['id']; ?>"
                                    class="btn-action w-100 text-center"
                                    onclick="return confirm('Res želiš izbrisati vse prijave za <?php echo htmlspecialchars($option['name']); ?>?');">
                                    <i class="bi bi-trash3 me-1"></i> Izbriši prijave
                                </a>
                            </div>
                        </div>
                    </div>

                    <?php
                }
            } else {
                echo '<div class="col-12"><div class="glass-card text-center py-5">';
                echo '<i class="bi bi-inbox" style="font-size: 3rem; color: var(--text-secondary);"></i>';
                echo '<p class="text-secondary mt-3">Ni podatkov o doživetjih. Naloži JSON datoteko.</p>';
                echo '</div></div>';
            }
            $conn->close();
            ?>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="clearDozivetja" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-exclamation-triangle text-warning me-2"></i>Potrdi odločitev
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    Res želiš izbrisati vse vnose prijav in izbir za doživetja?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Prekliči</button>
                    <a href="pocisti_dozivetja.php" class="btn btn-danger">
                        <i class="bi bi-trash3 me-1"></i> Izbriši vse
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL"
        crossorigin="anonymous"></script>
</body>

</html>