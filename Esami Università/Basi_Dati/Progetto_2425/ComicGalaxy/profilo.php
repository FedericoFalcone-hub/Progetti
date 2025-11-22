<?php
session_start();

// Accesso solo se loggato
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Profilo Utente - ComicGalaxy</title>
    <link href="https://fonts.googleapis.com/css2?family=Comic+Neue:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>


<?php include 'navbar.php'; ?>

<!-- Main content -->
<div class="main">
    <h1>Profilo Utente</h1>

    <div class="profile-box">
        <p><strong>Nome:</strong> <?= htmlspecialchars($_SESSION['nome']) ?></p>
        <p><strong>Cognome:</strong> <?= htmlspecialchars($_SESSION['cognome']) ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($_SESSION['user']) ?></p>
        <p><strong>Telefono:</strong> <?= htmlspecialchars($_SESSION['telefono']) ?></p>
        <p><strong>Ruolo:</strong> <?= htmlspecialchars($_SESSION['ruolo']) ?></p>

        <hr style="border:2px solid #ff6f61; margin:20px 0;">
        <div class="back-link">
        <a href="<?=
            $_SESSION['ruolo'] === 'manager'
                ? 'area_manager.php'
                : 'area_clienti.php'
        ?>" class="button">Torna alla tua area riservata</a>
        </div>
    </div>
</div>

</body>
</html>
