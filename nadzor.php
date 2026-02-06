<!doctype html>
<html lang="sl" data-bs-theme="auto">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Lepo je biti elektrotehnik - NADZOR</title>

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@docsearch/css@3">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

  <style>
    * {
      font-family: 'Inter', sans-serif;
    }

    .header-gradient {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      padding: 2rem;
      border-radius: 1rem;
      margin-bottom: 2rem;
      box-shadow: 0 10px 40px rgba(102, 126, 234, 0.3);
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

    .contestant-card {
      transition: all 0.2s ease;
      border-radius: 0.75rem !important;
    }

    .contestant-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
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

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
  die("Povezava s strežnikom ni uspela: " . $conn->connect_error);
}

$conn->set_charset("utf8");

$sql = "SELECT ID, name, time FROM contestants WHERE izbran = 0 ORDER BY time DESC";
$result = $conn->query($sql);

$sql = "SELECT * FROM question ORDER BY ID DESC LIMIT 1";
$resultQ = $conn->query($sql);

$conn->close();

$idQ = "-1";
if ($resultQ->num_rows > 0) {
  $rowQ = $resultQ->fetch_assoc();
  $idQ = $rowQ['ID'];
  $steviloGlasov = (int) $rowQ['ACount'] + (int) $rowQ['BCount'] + (int) $rowQ['CCount'] + (int) $rowQ['DCount'];
  $procentA = 25;
  $procentB = 25;
  $procentC = 25;
  $procentD = 25;
  if ($steviloGlasov > 0) {
    $procentA = round((int) $rowQ['ACount'] / $steviloGlasov * 100);
    $procentB = round((int) $rowQ['BCount'] / $steviloGlasov * 100);
    $procentC = round((int) $rowQ['CCount'] / $steviloGlasov * 100);
    $procentD = round((int) $rowQ['DCount'] / $steviloGlasov * 100);
  }
}

$trenutniTekmovalec = file_get_contents("izbran_tekmovalec.txt");
if ($trenutniTekmovalec == "") {
  $trenutniTekmovalec = "*Ni tekmovalca*";
}
$view = file_get_contents("pogled.txt");

?>
<script>
  var view = "<?php print ($view); ?>";

  // Function to update page content via AJAX
  function updateContent() {
    fetch('/api_nadzor.php')
      .then(response => response.json())
      .then(data => {
        // Update current contestant
        const contestantElement = document.getElementById('trenutni-tekmovalec');
        if (contestantElement) {
          contestantElement.textContent = data.trenutniTekmovalec;
        }

        // Update contestant list
        const contestantList = document.getElementById('contestant-list');
        if (contestantList) {
          let html = '';
          data.contestants.forEach(c => {
            html += `<div class="col"><a href="izberi_tekmovalca.php?name=${encodeURIComponent(c.name)}&id=${c.ID}" class="btn btn-outline-primary">${c.name}</a></div>`;
          });
          if (data.contestants.length > 0) {
            html += `<div class="col"><button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#clearList">Počisti seznam</button></div>`;
          } else {
            html += `<div class="col"><button type="button" class="btn btn-outline-secondary" disabled>Ni prijavljenih</button></div>`;
          }
          contestantList.innerHTML = html;
        }

        // Update vote counts if question exists
        if (data.questionData) {
          const voteCountEl = document.getElementById('vote-count');
          if (voteCountEl) voteCountEl.textContent = data.questionData.steviloGlasov;

          const procentA = document.getElementById('procent-a');
          const procentB = document.getElementById('procent-b');
          const procentC = document.getElementById('procent-c');
          const procentD = document.getElementById('procent-d');
          if (procentA) procentA.textContent = data.questionData.procentA + '%';
          if (procentB) procentB.textContent = data.questionData.procentB + '%';
          if (procentC) procentC.textContent = data.questionData.procentC + '%';
          if (procentD) procentD.textContent = data.questionData.procentD + '%';
        }

        // Update view status
        view = data.view;
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
            <h1>🎮 Nadzorna plošča</h1>
            <p>Upravljanje kviza in glasovanja občinstva</p>
          </div>
          <button type="button" onclick="refreshFunction()" class="btn btn-light btn-lg">
            🔄 Osveži
          </button>
        </div>
      </div>

      <!-- Navigation -->
      <div class="nav-tabs-custom d-flex gap-2">
        <a href="nadzor.php" class="btn btn-primary">🎯 Nadzor kviza</a>
        <a href="nadzor_dozivetja.php" class="btn btn-outline-secondary">🎪 Nadzor doživetij</a>
      </div>

      <!-- System Status -->
      <div class="section-card">
        <div class="section-title">📡 Status sistema</div>
        <div class="d-flex flex-wrap gap-3 align-items-center">
          <span class="text-muted">Prikaz na mobilnih:</span>
          <div class="btn-group" role="group">
            <a href="spremeni_pogled.php?view=0"
              class="btn <?php echo ($view == "0") ? 'btn-secondary' : 'btn-outline-secondary'; ?>">⏸️ Ni aktivnosti</a>
            <a href="spremeni_pogled.php?view=1"
              class="btn <?php echo ($view == "1") ? 'btn-success' : 'btn-outline-success'; ?>">📝 Prijava na kviz</a>
            <a href="spremeni_pogled.php?view=2"
              class="btn <?php echo ($view == "2") ? 'btn-info' : 'btn-outline-info'; ?>">🗳️ Glas ljudstva</a>
            <a href="spremeni_pogled.php?view=3"
              class="btn <?php echo ($view == "3") ? 'btn-warning' : 'btn-outline-warning'; ?>">🎪 Doživetja</a>
          </div>
        </div>
      </div>

      <!-- Current Status Cards -->
      <div class="row g-3 mb-4">
        <div class="col-md-6">
          <div class="section-card h-100">
            <div class="section-title">👤 Trenutni tekmovalec</div>
            <div class="d-flex align-items-center gap-3">
              <span id="trenutni-tekmovalec"
                class="status-pill bg-primary text-white"><?php echo $trenutniTekmovalec ?></span>
              <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal"
                data-bs-target="#clearContestant">
                Odstrani
              </button>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="section-card h-100">
            <div class="section-title">🗳️ Glas ljudstva</div>
            <div class="d-flex align-items-center gap-3">
              <span class="status-pill bg-info text-white">ID: <?php echo $idQ ?></span>
              <span class="text-muted">Skupaj glasov: <?php echo isset($steviloGlasov) ? $steviloGlasov : 0; ?></span>
            </div>
          </div>
        </div>
      </div>


      <!-- Modal za odstranitev tekmovalca -->
      <div class="modal fade" id="clearContestant" tabindex="-1" aria-labelledby="clearContestantLabel"
        aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h1 class="modal-title fs-5" id="clearContestantLabel">Potrdi odločitev</h1>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              Res želiš izbrisati tekmovalca?
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Prekliči</button>
              <a href="odstrani_tekmovalca.php" type="button" class="btn btn-danger" role="button">Odstrani</a>
            </div>
          </div>
        </div>
      </div>

      <!-- Prijave v kviz -->
      <div class="section-card">
        <div class="section-title">📝 Prijave na kviz</div>
        <p class="text-muted mb-3">Najnovejše prijave na vrhu. Tekmovalca izberi s klikom na njegovo ime.</p>

        <div id="contestant-list" class="row row-cols-2 row-cols-lg-6 g-2 g-lg-3">
          <?php
          if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
              echo "<div class=\"col\"><a href=\"izberi_tekmovalca.php?name=" . $row["name"] . "&id=" . $row["ID"] . "\" type=\"button\" class=\"btn btn-outline-primary\" role=\"button\">" . $row["name"] . "</a></div>";
            }
            ?>
            <div class="col"><button type="button" class="btn btn-danger" data-bs-toggle="modal"
                data-bs-target="#clearList">Počisti seznam</button></div>
            <!-- Modal -->
            <div class="modal fade" id="clearList" tabindex="-1" aria-labelledby="clearListLabel" aria-hidden="true">
              <div class="modal-dialog">
                <div class="modal-content">
                  <div class="modal-header">
                    <h1 class="modal-title fs-5" id="clearListLabel">Potrdi odločitev</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    Res želiš izbrisati vse vnose prijav na tekmovanje?
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Prekliči</button>
                    <a href="pocisti_prijave.php" type="button" class="btn btn-danger" role="button">Izbriši</a>
                  </div>
                </div>
              </div>
            </div>
            <?php
          } else {
            ?>
            <div class="col"><button type="button" class="btn btn-outline-secondary" disabled>Ni prijavljenih</button>
            </div>
            <?php
          }
          ?>
        </div>

        <hr class="my-4">
        <h2 class="mt-4">Glas ljudstva</h2>
        <p>Nadzor nad delovanjem sistema in statistiko.</p>
        <?php
        if ($resultQ->num_rows > 0) {
          ?>
          <p>
          <div class="btn-group" role="group" aria-label="Vprašanje">
            <button type="button" class="btn btn-primary" disabled>Vprašanje</button>
            <button type="button" class="btn btn-light" disabled><?php echo $rowQ['questionText']; ?></button>
          </div>
          </p>
          <div class="row">
            <div class="col-md-auto">
              <div class="btn-group" role="group" aria-label="Število glasov">
                <button type="button" class="btn btn-success" disabled>Število glasov</button>
                <button type="button" class="btn btn-light" disabled><?php echo $steviloGlasov; ?></button>
              </div>
            </div>
            <div class="col-md-auto">
              <div class="btn-group" role="group" aria-label="Odgovor A">
                <button type="button" class="btn btn-primary" disabled>A</button>
                <button type="button" class="btn btn-light" disabled><?php echo $rowQ['AText']; ?></button>
                <button type="button" class="btn btn-primary" disabled><?php echo $procentA; ?>%</button>
              </div>
            </div>
            <div class="col-md-auto">
              <div class="btn-group" role="group" aria-label="Odgovor B">
                <button type="button" class="btn btn-primary" disabled>B</button>
                <button type="button" class="btn btn-light" disabled><?php echo $rowQ['BText']; ?></button>
                <button type="button" class="btn btn-primary" disabled><?php echo $procentB; ?>%</button>
              </div>
            </div>
            <div class="col-md-auto">
              <div class="btn-group" role="group" aria-label="Odgovor C">
                <button type="button" class="btn btn-primary" disabled>C</button>
                <button type="button" class="btn btn-light" disabled><?php echo $rowQ['CText']; ?></button>
                <button type="button" class="btn btn-primary" disabled><?php echo $procentC; ?>%</button>
              </div>
            </div>
            <div class="col-md-auto">
              <div class="btn-group" role="group" aria-label="Odgovor D">
                <button type="button" class="btn btn-primary" disabled>D</button>
                <button type="button" class="btn btn-light" disabled><?php echo $rowQ['DText']; ?></button>
                <button type="button" class="btn btn-primary" disabled><?php echo $procentD; ?>%</button>
              </div>
            </div>
          </div>
          <?php
        } else {
          ?>
          <button type="button" class="btn btn-outline-secondary" disabled>Ni vprašanj</button>
          <?php
        }
        ?>

        <hr class="my-4">
      </div>
  </main>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL"
    crossorigin="anonymous"></script>
</body>

</html>