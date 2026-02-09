<?php require 'auth_config.php';
require_login(); ?>
<!doctype html>
<html lang="sl" data-bs-theme="dark">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Nadzorna plošča | Lepo je biti elektrotehnik</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

  <link href="css/premium.css" rel="stylesheet">
</head>

<?php
require 'server_data.php';

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
  die("Povezava s strežnikom ni uspela.");
}
$conn->set_charset("utf8");

$sql = "SELECT ID, name, time FROM contestants WHERE izbran = 0 ORDER BY time DESC";
$result = $conn->query($sql);
$contestantCount = $result->num_rows;

$sql = "SELECT * FROM question ORDER BY ID DESC LIMIT 1";
$resultQ = $conn->query($sql);

$conn->close();

$idQ = "-1";
$steviloGlasov = 0;
if ($resultQ->num_rows > 0) {
  $rowQ = $resultQ->fetch_assoc();
  $idQ = $rowQ['ID'];
  $steviloGlasov = (int) $rowQ['ACount'] + (int) $rowQ['BCount'] + (int) $rowQ['CCount'] + (int) $rowQ['DCount'];
  $procentA = $steviloGlasov > 0 ? round((int) $rowQ['ACount'] / $steviloGlasov * 100) : 25;
  $procentB = $steviloGlasov > 0 ? round((int) $rowQ['BCount'] / $steviloGlasov * 100) : 25;
  $procentC = $steviloGlasov > 0 ? round((int) $rowQ['CCount'] / $steviloGlasov * 100) : 25;
  $procentD = $steviloGlasov > 0 ? round((int) $rowQ['DCount'] / $steviloGlasov * 100) : 25;
}

$trenutniTekmovalec = file_get_contents("izbran_tekmovalec.txt");
if ($trenutniTekmovalec == "") {
  $trenutniTekmovalec = "Ni tekmovalca";
}
$view = file_get_contents("pogled.txt");
?>

<script>
  function refreshFunction() {
    window.location.reload();
  }

  // Auto-refresh for vote counts
  // Auto-refresh for vote counts & contestants
  setInterval(function () {
    fetch('api_nadzor_kviz.php')
      .then(response => response.json())
      .then(data => {
        // Update contestant count
        document.querySelector('.badge-count').innerText = data.contestantCount + ' prijavljenih';

        // Update contestant list
        const listContainer = document.getElementById('contestant-list');
        if (data.contestants.length > 0) {
          let html = '';
          data.contestants.forEach(c => {
            // Simple escaping
            const safeName = c.name.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
            html += `<a href="izberi_tekmovalca.php?name=${encodeURIComponent(c.name)}&id=${c.ID}" class="contestant-btn">${safeName}</a>`;
          });
          listContainer.innerHTML = html;
        } else {
          listContainer.innerHTML = '<div class="text-secondary">Ni prijavljenih tekmovalcev</div>';
        }

        // Update current contestant
        const currentContestantEl = document.getElementById('trenutni-tekmovalec');
        if (currentContestantEl) currentContestantEl.innerText = data.trenutniTekmovalec;

        // Update votes
        if (data.question) {
          const q = data.question;
          const total = parseInt(q.ACount) + parseInt(q.BCount) + parseInt(q.CCount) + parseInt(q.DCount);

          // Helper for percentage
          const calcP = (val) => total > 0 ? Math.round(parseInt(val) / total * 100) : 25;

          if (document.getElementById('procent-a')) document.getElementById('procent-a').innerText = calcP(q.ACount) + '%';
          if (document.getElementById('procent-b')) document.getElementById('procent-b').innerText = calcP(q.BCount) + '%';
          if (document.getElementById('procent-c')) document.getElementById('procent-c').innerText = calcP(q.CCount) + '%';
          if (document.getElementById('procent-d')) document.getElementById('procent-d').innerText = calcP(q.DCount) + '%';

          if (document.getElementById('total-votes-info')) document.getElementById('total-votes-info').innerText = total;
          if (document.getElementById('total-votes-box')) document.getElementById('total-votes-box').innerText = total;
        }

        // Update active view buttons
        document.querySelectorAll('.status-btn').forEach(btn => btn.classList.remove('active'));
        const activeBtn = document.querySelector(`.status-btn[href="spremeni_pogled.php?view=${data.view}"]`);
        if (activeBtn) activeBtn.classList.add('active');
      })
      .catch(err => console.error('Fetch error:', err));
  }, 3000);
</script>

<body>
  <div class="container-fluid py-4 d-flex flex-column" style="min-height: 100vh;">
    <!-- Header -->


    <!-- Combined Navigation & Status -->
    <div class="glass-card d-flex flex-wrap align-items-center gap-4 mb-4">
      <!-- Navigation Section -->
      <div>
        <h6 class="text-secondary mb-2 text-uppercase fw-bold" style="font-size: 0.75rem; letter-spacing: 0.05em;">
          Navigacija</h6>
        <div class="nav-pills-custom mb-0">
          <a href="nadzor.php" class="nav-link active">
            <i class="bi bi-controller"></i> Nadzor kviza
          </a>
          <a href="nadzor_dozivetja.php" class="nav-link">
            <i class="bi bi-stars"></i> Nadzor doživetij
          </a>
        </div>
      </div>

      <!-- Divider (Desktop only) -->
      <div class="d-none d-md-block" style="width: 1px; height: 40px; background: var(--border-color);"></div>

      <!-- Status Section -->
      <div class="flex-grow-1">
        <h6 class="text-secondary mb-2 text-uppercase fw-bold" style="font-size: 0.75rem; letter-spacing: 0.05em;">
          Status sistema</h6>
        <div class="status-btn-group">
          <a href="spremeni_pogled.php?view=0"
            class="status-btn inactive <?php echo ($view == "0") ? 'active' : ''; ?>">
            <i class="bi bi-pause-circle"></i> Ni aktivnosti
          </a>
          <a href="spremeni_pogled.php?view=1" class="status-btn quiz <?php echo ($view == "1") ? 'active' : ''; ?>">
            <i class="bi bi-pencil-square"></i> Prijava na kviz
          </a>
          <a href="spremeni_pogled.php?view=2" class="status-btn vote <?php echo ($view == "2") ? 'active' : ''; ?>">
            <i class="bi bi-hand-thumbs-up"></i> Glas ljudstva
          </a>
          <a href="spremeni_pogled.php?view=3"
            class="status-btn experience <?php echo ($view == "3") ? 'active' : ''; ?>">
            <i class="bi bi-stars"></i> Doživetja
          </a>
        </div>
      </div>

      <!-- Divider (Desktop only) -->
      <div class="d-none d-md-block" style="width: 1px; height: 40px; background: var(--border-color);"></div>

      <!-- Actions Section -->
      <div>
        <h6 class="text-secondary mb-2 text-uppercase fw-bold" style="font-size: 0.75rem; letter-spacing: 0.05em;">
          Sistem</h6>
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

    <!-- Main Content Dashboard Grid -->
    <div class="row g-4 flex-grow-1">
      <!-- Left Column: Contestants (Larger) -->
      <div class="col-lg-8">
        <div class="glass-card h-100">
          <div class="section-header">
            <div class="icon" style="background: linear-gradient(135deg, var(--accent-success), #059669);">
              <i class="bi bi-people-fill text-white"></i>
            </div>
            <h2>Prijave na kviz</h2>
            <span class="badge-count"><?php echo $contestantCount; ?> prijavljenih</span>
          </div>
          <p class="text-secondary mb-3">Klikni na ime za izbiro tekmovalca</p>

          <div id="contestant-list" class="contestant-grid">
            <?php
            if ($result->num_rows > 0) {
              while ($row = $result->fetch_assoc()) {
                echo '<a href="izberi_tekmovalca.php?name=' . urlencode($row["name"]) . '&id=' . $row["ID"] . '" class="contestant-btn">' . htmlspecialchars($row["name"]) . '</a>';
              }
            } else {
              echo '<div class="text-secondary">Ni prijavljenih tekmovalcev</div>';
            }
            ?>
          </div>

          <?php if ($contestantCount > 0): ?>
            <div class="mt-4 pt-3 border-top" style="border-color: var(--border-color) !important;">
              <button type="button" class="btn-action" data-bs-toggle="modal" data-bs-target="#clearList">
                <i class="bi bi-trash3 me-1"></i> Počisti seznam
              </button>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Right Column: Info & Stats (Smaller) -->
      <div class="col-lg-4">
        <div class="row g-3">
          <!-- Current Contestant Info -->
          <div class="col-12">
            <div class="info-card">
              <div class="info-icon">
                <i class="bi bi-person-fill text-white"></i>
              </div>
              <div class="info-content flex-grow-1">
                <h4>Trenutni tekmovalec</h4>
                <div class="value" id="trenutni-tekmovalec"><?php echo htmlspecialchars($trenutniTekmovalec); ?></div>
              </div>
              <button type="button" class="btn-action ms-auto p-2" data-bs-toggle="modal"
                data-bs-target="#clearContestant">
                <i class="bi bi-x-lg"></i>
              </button>
            </div>
          </div>

          <!-- Question ID Info -->
          <div class="col-12">
            <div class="info-card">
              <div class="info-icon" style="background: linear-gradient(135deg, var(--accent-info), #0891b2);">
                <i class="bi bi-hash text-white"></i>
              </div>
              <div class="info-content flex-grow-1">
                <h4>ID vprašanja</h4>
                <div class="value"><?php echo $idQ; ?></div>
              </div>
              <div class="text-end">
                <small class="text-secondary d-block">Skupaj glasov</small>
                <div class="value" style="color: var(--accent-info);" id="total-votes-info">
                  <?php echo $steviloGlasov; ?>
                </div>
              </div>
            </div>
          </div>

          <!-- Voting Section -->
          <div class="col-12">
            <div class="glass-card mb-0">
              <div class="section-header">
                <div class="icon" style="background: linear-gradient(135deg, var(--accent-info), #0891b2);">
                  <i class="bi bi-bar-chart-fill text-white"></i>
                </div>
                <h2>Glas ljudstva</h2>
              </div>

              <?php if ($resultQ->num_rows > 0): ?>
                <div class="question-box">
                  <div class="question-text"><?php echo htmlspecialchars($rowQ['questionText']); ?></div>
                  <div class="vote-count">Skupaj glasov: <strong
                      id="total-votes-box"><?php echo $steviloGlasov; ?></strong></div>
                </div>

                <div class="vote-stats">
                  <div class="vote-option">
                    <div class="letter">A</div>
                    <div class="percent" id="procent-a"><?php echo $procentA; ?>%</div>
                  </div>
                  <div class="vote-option">
                    <div class="letter">B</div>
                    <div class="percent" id="procent-b"><?php echo $procentB; ?>%</div>
                  </div>
                  <div class="vote-option">
                    <div class="letter">C</div>
                    <div class="percent" id="procent-c"><?php echo $procentC; ?>%</div>
                  </div>
                  <div class="vote-option">
                    <div class="letter">D</div>
                    <div class="percent" id="procent-d"><?php echo $procentD; ?>%</div>
                  </div>
                </div>
              <?php else: ?>
                <div class="text-secondary">
                  <i class="bi bi-inbox me-2"></i> Ni aktivnih vprašanj
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal: Odstrani tekmovalca -->
  <div class="modal fade" id="clearContestant" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"><i class="bi bi-exclamation-triangle text-warning me-2"></i>Potrdi odločitev</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          Res želiš odstraniti trenutnega tekmovalca?
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Prekliči</button>
          <a href="odstrani_tekmovalca.php" class="btn btn-danger">
            <i class="bi bi-trash3 me-1"></i> Odstrani
          </a>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal: Počisti seznam -->
  <div class="modal fade" id="clearList" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"><i class="bi bi-exclamation-triangle text-warning me-2"></i>Potrdi odločitev</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          Res želiš izbrisati vse prijave na kviz?
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Prekliči</button>
          <a href="pocisti_prijave.php" class="btn btn-danger">
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