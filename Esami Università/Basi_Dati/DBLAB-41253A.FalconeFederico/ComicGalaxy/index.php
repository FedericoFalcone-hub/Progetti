<?php
session_start();


if (isset($_GET['logout'])) {
    session_unset();
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
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

    <?php include 'navbar.php'; ?>

    <main>
        <div class="container mt-5">
            <div class="row justify-content-center">
                <div class="col-md-8 text-center">

                    <h1 class="fw-bold mb-3">Benvenuto su ComicGalaxy</h1>
                    <p class="lead text-muted mb-4">
                        Il tuo punto di riferimento per fumetti, manga e collezionabili.
                        Sfoglia il nostro catalogo, scopri le ultime uscite, accumula punti con la tessera fedeltà e resta sempre aggiornato sulle novità del mondo del fumetto.
                    </p>

                </div>
            </div>
        </div>
    </main>

    <footer class="text-center text-muted py-3 bg-light">
        © <?= date('Y') ?> ComicGalaxy
    </footer>

</body>

</html>