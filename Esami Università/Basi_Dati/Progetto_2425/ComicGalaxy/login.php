<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'lib/functions.php';
include 'lib/functions.ini.php';

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
            header("Location: area_manager.php");
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
    <link href="https://fonts.googleapis.com/css2?family=Comic+Neue:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">

</head>
<body>

<div class="login-container">
    <h2>Accedi a ComicGalaxy</h2>

    <?php if (!empty($errore)) : ?>
        <div class="error"><?= htmlspecialchars($errore) ?></div>
    <?php endif; ?>

    <form method="post">
        <input type="text" name="mail" placeholder="E-mail" required>
        <input type="password" name="password" placeholder="Password" required>
        <input type="submit" name="login" value="Accedi">
    </form>

    <div class="back-link">
        <a href="index.php">🏠 Torna alla home</a>
    </div>
</div>

</body>
</html>
