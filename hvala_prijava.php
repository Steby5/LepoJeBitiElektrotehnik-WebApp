<!doctype html>
<html lang="sl" class="h-100" data-bs-theme="auto">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lepo je biti elektrotehnik</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@docsearch/css@3">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link href="css/cover.css" rel="stylesheet">
    <link href="css/premium.css" rel="stylesheet">
</head>

<script>
    // Redirect to index after 5 seconds
    setTimeout(function () {
        window.location.replace("index.php");
    }, 5000);
</script>

<body class="d-flex h-100 text-center text-bg-dark">
    <div class="cover-container d-flex w-100 h-100 p-3 mx-auto flex-column">
        <header class="mb-auto">
            <div>
                <h3 class="float-md-start mb-0">Lepo je biti elektrotehnik</h3>
            </div>
        </header>

        <main class="px-3 glass-card">
            <?php if (isset($_GET['message']) && $_GET['message'] === 'already'): ?>
                <h1>Opozorilo</h1>
                <p class="lead">Že si se prijavil/-a na kviz v tem krogu!</p>
            <?php else: ?>
                <h1>Hvala!</h1>
                <p class="lead">Prijava je bila uspešna. Počakaj na žreb!</p>
            <?php endif; ?>
        </main>

        <footer class="mt-auto text-white-50">
            <p>UL FE 2026</p>
        </footer>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL"
        crossorigin="anonymous"></script>

</body>

</html>