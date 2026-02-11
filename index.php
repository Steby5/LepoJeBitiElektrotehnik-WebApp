<?php
ob_start();
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
header("Pragma: no-cache"); // HTTP 1.0.
header("Expires: 0"); // Proxies.
require 'server_data.php'; ?>
<!doctype html>
<html lang="en" class="h-100" data-bs-theme="auto">

<head>
  <script src="../assets/js/color-modes.js"></script>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Lepo je biti elektrotehnik</title>

  <link rel="canonical" href="https://getbootstrap.com/docs/5.3/examples/cover/">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@docsearch/css@3">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">

  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">


  <!-- Custom styles for this template -->
  <link href="css/cover.css" rel="stylesheet">
  <link href="css/premium.css" rel="stylesheet">

</head>


<?php
$connView = new mysqli($servername, $username, $password, $dbname);
$view = "0";
if (!$connView->connect_error) {
  $connView->set_charset("utf8");
  $resView = $connView->query("SELECT setting_value FROM system_settings WHERE setting_key = 'current_view'");
  if ($rowView = $resView->fetch_assoc()) {
    $view = $rowView['setting_value'];
  }
  $connView->close();
}
?>

<body class="d-flex h-100 text-center text-bg-dark">
  <div class="cover-container d-flex w-100 h-100 p-3 mx-auto flex-column">
    <header class="mb-5">
      <div class="text-center">
        <h3 class="mb-0">Lepo je biti elektrotehnik</h3>
      </div>
    </header>
    <?php
    if ($view == "0") {
      ?>
      <!-- neaktivno -->
      <main class="px-3 glass-card">
        <h1>Ni aktivnosti</h1>
        <p class="lead">Trenutno ni možnosti za sodelovanje.</p>
      </main>
      <!-- END Neaktivno -->
      <?php
    } else if ($view == "1") {
      ?>
        <!-- Prijava v kviz -->
        <main class="px-3 glass-card">
          <form action="prijava.php" accept-charset="utf-8" method="post">
            <h1>Prijavi se v kviz</h1>
            <p> </p>
            <p class="lead"><input type="ime" class="form-control form-control-lg" id="ime" name="ime"
                placeholder="Ime in priimek"></p>
            <p class="lead">-</p>
            <p class="lead">
            <div class="d-grid gap-2 col-6 mx-auto">
              <input type="submit" name="submit" id="submit" value="Pošlji"
                class="btn btn-lg btn-light fw-bold border-white bg-white">
            </div>
            </p>
          </form>
        </main>
        <!-- END Prijava v kviz -->
      <?php
    } else if ($view == "2") {
      // povezava na bazo podatkov
      require 'server_data.php';
      $conn = new mysqli($servername, $username, $password, $dbname);
      if ($conn->connect_error)
        die("Težava na strežniku, poskusi ponovno!" . $conn->connect_error);
      $conn->set_charset("utf8");
      $sql = "SELECT ID, questionText, AText, BText, CText, DText FROM question ORDER BY ID DESC LIMIT 1";
      $resultQ = $conn->query($sql);
      $conn->close();

      if ($resultQ->num_rows > 0) {
        $row = $resultQ->fetch_assoc();

        // Check if user already voted
        $currentSessionId = "";
        if (file_exists("glasovanje_session.txt")) {
          $currentSessionId = trim(file_get_contents("glasovanje_session.txt"));
        }

        if ($currentSessionId !== "" && isset($_COOKIE['ljbe_glasovanje_session']) && $_COOKIE['ljbe_glasovanje_session'] === $currentSessionId) {
          ?>
              <main class="px-3 glass-card">
                <h1>Hvala!</h1>
                <p class="lead">Tvoj glas je bil zabeležen.</p>
                <div class="mt-4">
                  <i class="bi bi-check-circle-fill text-success" style="font-size: 3rem;"></i>
                </div>
              </main>
          <?php
        } else {
          ?>
              <main class="px-3 glass-card">

                <!-- Glas ljudstva -->
                <form action="glasovanje.php" accept-charset="utf-8" method="post">
                  <input type="hidden" id="Qid" name="Qid" value="<?php echo $row["ID"]; ?>">
                  <h1><?php echo $row["questionText"]; ?></h1>
                  <p class="lead">
                  <div class="d-grid gap-2 col-11 mx-auto">
                    <input type="radio" class="btn-check" name="odgovor" value="A" id="A" autocomplete="off" required>
                    <label class="btn btn-outline-success btn-outline-success-kviz btn-lg"
                      for="A"><?php echo $row["AText"]; ?></label>
                    <input type="radio" class="btn-check" name="odgovor" value="B" id="B" autocomplete="off" required>
                    <label class="btn btn-outline-success btn-outline-success-kviz btn-lg"
                      for="B"><?php echo $row["BText"]; ?></label>
                    <input type="radio" class="btn-check" name="odgovor" value="C" id="C" autocomplete="off" required>
                    <label class="btn btn-outline-success btn-outline-success-kviz btn-lg"
                      for="C"><?php echo $row["CText"]; ?></label>
                    <input type="radio" class="btn-check" name="odgovor" value="D" id="D" autocomplete="off" required>
                    <label class="btn btn-outline-success btn-outline-success-kviz btn-lg"
                      for="D"><?php echo $row["DText"]; ?></label>
                  </div>
                  </p>
                  <p class="lead">-</p>
                  <p class="lead">
                  <div class="d-grid gap-2 col-6 mx-auto">
                    <input type="submit" name="submit" id="submit" value="Glasuj"
                      class="btn btn-lg btn-light fw-bold border-white bg-white">
                  </div>
                  </p>
                </form>
                <!-- END Glas ljudstva -->
              </main>
          <?php
        }
      } else {
        ?>
            <main class="px-3 glass-card">
              <!-- Glas ljudstva -->
              <h1>V sistemu ni vprašanj</h1>
              <p class="lead">Poskusi ponovno čez nekaj trenutnkov</p>
              <!-- END Glas ljudstva -->
            </main>
        <?php
      }
    } else if ($view == "3") {
      // Doživetja - experiences signup from database
      $connDoz = new mysqli($servername, $username, $password, $dbname);
      $connDoz->set_charset("utf8");
      $dozResult = $connDoz->query("SELECT code, name FROM dozivetja WHERE active = 1 ORDER BY name");
      $dozivetjaList = [];
      while ($row = $dozResult->fetch_assoc()) {
        $dozivetjaList[] = $row;
      }
      $connDoz->close();
      ?>
            <!-- Prijava na doživetje -->
            <main class="px-3 glass-card">
              <form action="prijava_dozivetje.php" accept-charset="utf-8" method="post">
                <h1>Prijavi se na posebno doživetje</h1>
                <p class="lead mt-3 mb-2">Izberi doživetja:</p>
                <div class="d-grid gap-2 mb-4">
              <?php
              foreach ($dozivetjaList as $option) {
                $id = htmlspecialchars($option['code']);
                $name = htmlspecialchars($option['name']);
                ?>
                    <div class="form-check p-0">
                      <input type="checkbox" class="btn-check" name="dozivetje_id[]" value="<?php echo $id; ?>"
                        id="<?php echo $id; ?>" autocomplete="off">
                      <label class="btn btn-outline-light btn-lg w-100 py-3" for="<?php echo $id; ?>">
                  <?php echo $name; ?>
                      </label>
                    </div>
              <?php
              }
              ?>
                </div>
                <p class="lead mb-2">Vnesi svoje ime in priimek:</p>
                <div class="d-grid gap-2 mb-4">
                  <input type="text" class="form-control form-control-lg" id="ime" name="ime" placeholder="Ime in priimek"
                    required>
                </div>
                <div class="d-grid gap-2">
                  <input type="submit" name="submit" id="submit" value="Pošlji"
                    class="btn btn-lg btn-light fw-bold border-white bg-white">
                </div>
              </form>
            </main>
            <!-- END Prijava na doživetje -->
      <?php
    } else {
      ?>
            <!-- Napaka -->
            <main class="px-3 glass-card">
              <h1>Napaka</h1>
              <p class="lead">Prišlo je do napake. Se opravičujemo!</p>
            </main>
            <!-- END Napaka -->
      <?php
    }
    ?>
    <footer class="mt-auto text-white-50">
      <p>UL FE 2026</p>
    </footer>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL"
    crossorigin="anonymous"></script>

</body>

</html>