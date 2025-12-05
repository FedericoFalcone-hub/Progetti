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

    <!-- BOOTSTRAP -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

</head>
<body>

<?php include 'navbar.php'; ?>

<div class="container text-center mt-5">

    <h1 class="display-4 fw-bold text-primary">Benvenuti in ComicGalaxy</h1>

    <p class="mt-4 fs-5 text-secondary">
        ComicGalaxy è il tuo punto di riferimento per fumetti, gadget e collezionabili.
        Qui puoi gestire i tuoi ordini, controllare i saldi punti delle tessere fedeltà dei clienti
        e monitorare lo storico degli acquisti.
    </p>

    <p class="fs-5 text-secondary">
        Usa la barra di navigazione in alto per accedere rapidamente a negozi, clienti,
        tessere, ordini e fornitori.
    </p>

</div>

<!-- BOOTSTRAP JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

