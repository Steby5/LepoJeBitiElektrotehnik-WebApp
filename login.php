<?php
require 'auth_config.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $password = $_POST['password'] ?? '';
    if (verify_admin_password($password)) {
        $_SESSION['admin_logged_in'] = true;
        header("Location: nadzor.php");
        exit();
    } else {
        $error = "Neveljavno geslo.";
    }
}
?>
<!doctype html>
<html lang="sl" data-bs-theme="dark">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Prijava | Lepo je biti elektrotehnik</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <link href="css/premium.css" rel="stylesheet">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            padding-top: 40px;
            padding-bottom: 40px;
        }

        .form-signin {
            width: 100%;
            max-width: 330px;
            padding: 15px;
            margin: auto;
        }
    </style>
</head>

<body class="text-center">
    <main class="form-signin glass-card">
        <form method="post">
            <h1 class="h3 mb-3 fw-normal">Prijava</h1>

            <?php if ($error): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <div class="form-floating mb-3">
                <input type="password" class="form-control" id="floatingPassword" name="password" placeholder="Geslo"
                    required>
                <label for="floatingPassword">Geslo</label>
            </div>

            <button class="w-100 btn btn-lg btn-primary" type="submit">Prijavi se</button>
            <p class="mt-5 mb-3 text-muted">&copy; UL FE 2026</p>
        </form>
    </main>
</body>

</html>