<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'lib/functions.php';

$errore = null;

if (isset($_POST['login'])) {
    $mail = $_POST['mail'];
    $password = $_POST['password'];

    $login_info = login($mail, $password);

    if ($login_info !== null && isset($login_info['mail'])) {

        $_SESSION['user'] = $login_info['mail'];
        $_SESSION['ruolo'] = $login_info['ruolo'];
        $_SESSION['nome'] = $login_info['nome'];
        $_SESSION['cognome'] = $login_info['cognome'];
        $_SESSION['telefono'] = $login_info['telefono'];

        if ($login_info['ruolo'] === 'manager') {
            header("Location: manager/area_manager.php");
            exit();
        } elseif ($login_info['ruolo'] === 'cliente') {
            header("Location: area_clienti.php");
            exit();
        } else {
            $errore = "Ruolo non riconosciuto!";
        }

    } else {
        $errore = "Email o password errati!";
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Login - ComicGalaxy</title>

    <!-- BOOTSTRAP -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

</head>
<body class="bg-light">

<div class="container d-flex justify-content-center align-items-center" style="min-height: 100vh;">

    <div class="card shadow-lg p-4" style="width: 360px;">

        <h2 class="text-center mb-4 text-primary fw-bold">Accedi a ComicGalaxy</h2>

        <?php if (!empty($errore)) : ?>
            <div class="alert alert-danger text-center">
                <?= htmlspecialchars($errore) ?>
            </div>
        <?php endif; ?>

        <form method="post">
            <div class="mb-3">
                <label class="form-label">E-mail</label>
                <input type="email" name="mail" class="form-control" placeholder="Inserisci la tua email" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" placeholder="Inserisci la password" required>
            </div>

            <button type="submit" name="login" class="btn btn-primary w-100">
                Accedi
            </button>
        </form>

        <div class="text-center mt-3">
            <a href="index.php" class="text-decoration-none">Torna alla home</a>
        </div>

    </div>

</div>

<!-- BOOTSTRAP JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
