<?php
session_start();

// Controllo accesso: solo manager
if (!isset($_SESSION['user']) || $_SESSION['ruolo'] !== 'manager') {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Area Manager - ComicGalaxy</title>
    <link href="https://fonts.googleapis.com/css2?family=Comic+Neue:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>


<?php include 'navbar.php'; ?>

<!-- Main content -->
<div class="main">
    <h1>Area Riservata Manager <span class="comic-deco"></span></h1>
    <p>Da qui puoi gestire tutte le operazioni amministrative del tuo negozio ComicGalaxy.</p>

    <div style="
        display: flex;
        justify-content: center;
        flex-wrap: wrap;
        gap: 25px;
        margin-top: 40px;">

        <a href="gestione_negozio.php" class="manager-card">
            🏪<br>Gestione Negozio
        </a>

        <a href="#" class="manager-card">
            👥<br>Gestione Clienti
        </a>

        <a href="#" class="manager-card">
            💳<br>Gestione Tessere
        </a>

        <a href="#" class="manager-card">
            📦<br>Gestione Ordini
        </a>

        <a href="#" class="manager-card">
            🚚<br>Gestione Fornitori
        </a>

        <a href="#" class="manager-card">
            📊<br>Report & Statistiche
        </a>

    </div>

</div>

</body>
</html>
