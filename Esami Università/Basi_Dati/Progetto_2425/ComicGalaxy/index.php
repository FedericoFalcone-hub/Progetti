<?php
session_start();

// Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php"); 
    exit();
}
?>


<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>ComicGalaxy</title>
    <link href="https://fonts.googleapis.com/css2?family=Comic+Neue:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">

</head>
<body>


<?php include 'navbar.php'; ?>

<div class="main">
    <h1>Benvenuti in ComicGalaxy <span class="comic-deco"></span></h1>
    <p>
        ComicGalaxy è il tuo punto di riferimento per fumetti, gadget e collezionabili.
        Qui puoi gestire i tuoi ordini, controllare i saldi punti delle tessere fedeltà dei clienti
        e monitorare lo storico degli acquisti.
    </p>
    <p>
        Usa la barra di navigazione in alto per accedere rapidamente a negozi, clienti,
        tessere, ordini e fornitori.
    </p>
    <p class="comic-deco"></p>
</div>

</body>
</html>
