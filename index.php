<?php require 'server_data.php'; ?>
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

  <style>
    .bd-placeholder-img {
      font-size: 1.125rem;
      text-anchor: middle;
      -webkit-user-select: none;
      -moz-user-select: none;
      user-select: none;
    }

    @media (min-width: 768px) {
      .bd-placeholder-img-lg {
        font-size: 3.5rem;
      }
    }

    .b-example-divider {
      width: 100%;
      height: 3rem;
      background-color: rgba(0, 0, 0, .1);
      border: solid rgba(0, 0, 0, .15);
      border-width: 1px 0;
      box-shadow: inset 0 .5em 1.5em rgba(0, 0, 0, .1), inset 0 .125em .5em rgba(0, 0, 0, .15);
    }

    .b-example-vr {
      flex-shrink: 0;
      width: 1.5rem;
      height: 100vh;
    }

    .bi {
      vertical-align: -.125em;
      fill: currentColor;
    }

    .nav-scroller {
      position: relative;
      z-index: 2;
      height: 2.75rem;
      overflow-y: hidden;
    }

    .nav-scroller .nav {
      display: flex;
      flex-wrap: nowrap;
      padding-bottom: 1rem;
      margin-top: -1px;
      overflow-x: auto;
      text-align: center;
      white-space: nowrap;
      -webkit-overflow-scrolling: touch;
    }

    .btn-bd-primary {
      --bd-violet-bg: #712cf9;
      --bd-violet-rgb: 112.520718, 44.062154, 249.437846;

      --bs-btn-font-weight: 600;
      --bs-btn-color: var(--bs-white);
      --bs-btn-bg: var(--bd-violet-bg);
      --bs-btn-border-color: var(--bd-violet-bg);
      --bs-btn-hover-color: var(--bs-white);
      --bs-btn-hover-bg: #6528e0;
      --bs-btn-hover-border-color: #6528e0;
      --bs-btn-focus-shadow-rgb: var(--bd-violet-rgb);
      --bs-btn-active-color: var(--bs-btn-hover-color);
      --bs-btn-active-bg: #5a23c8;
      --bs-btn-active-border-color: #5a23c8;
    }

    .bd-mode-toggle {
      z-index: 1500;
    }

    .bd-mode-toggle .dropdown-menu .active .bi {
      display: block !important;
    }

    .btn-outline-success-kviz {
      --bs-btn-color: #fff;
      --bs-btn-border-color: #bbbbbb;
    }
  </style>
  <!-- Custom styles for this template -->
  <link href="css/cover.css" rel="stylesheet">

</head>


<?php
$view = file_get_contents("pogled.txt");
?>

<script>
  const interval = setInterval(function () {
    if (localStorage.getItem("ljbe_index") === null) {
      localStorage.setItem('ljbe_index', new Date());
    } else {
      var b = new Date();
      a = Date.parse(localStorage.getItem('ljbe_index')); // parse to date object
      if ((b - a) > 30000) {
        localStorage.removeItem("ljbe_index");
        window.location.replace("index.php");
      }
    }
  }, 3000);
</script>

<body class="d-flex h-100 text-center text-bg-dark">
  <div class="cover-container d-flex w-100 h-100 p-3 mx-auto flex-column">
    <header class="mb-auto">
      <div>
        <h3 class="float-md-start mb-0">Lepo je biti elektrotehnik</h3>
      </div>
    </header>
    <?php
    if ($view == "0") {
      ?>
      <!-- neaktivno -->
      <main class="px-3">
        <h1>Ni aktivnosti</h1>
        <p class="lead">Trenutno ni možnosti za sodelovanje.</p>
      </main>
      <!-- END Neaktivno -->
      <?php
    } else if ($view == "1") {
      ?>
        <!-- Prijava v kviz -->
        <main class="px-3">
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
        ?>
            <main class="px-3">
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
      } else {
        ?>
            <main class="px-3">
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
            <main class="px-3">
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
            <main class="px-3">
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