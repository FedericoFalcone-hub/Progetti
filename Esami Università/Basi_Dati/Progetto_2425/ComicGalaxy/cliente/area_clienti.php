<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['ruolo'] !== 'cliente') {
    header("Location: ../login.php");
    exit();
}
require_once '../lib/functions.php';
?>
<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <title>Area Cliente - ComicGalaxy</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body>

    <?php include '../navbar.php'; ?>

    <div class="container text-center mt-5">

        <?php if ($_SESSION['sospeso'] === 't'): ?>
            <div class="alert alert-danger shadow p-4 mt-5">
                <h1 class="fw-bold text-danger">Account Sospeso</h1>
                <p class="fs-5 mt-3">
                    Il tuo account è stato sospeso e non puoi accedere ai servizi di ComicGalaxy.
                </p>
                <p class="text-muted">
                    Per assistenza, contatta il supporto o recati in negozio.
                </p>

                <a href="/index.php?logout=1" class="btn btn-danger mt-3">
                    Esci dal tuo account
                </a>
            </div>

    </div>
</body>

</html>
<?php exit(); ?>
<?php endif; ?>

<h1 class="fw-bold text-primary mb-4">Area Riservata</h1>
<p class="fs-5 text-secondary">
    Da qui puoi gestire il tuo profilo e monitorare le tue attività su ComicGalaxy.
</p>

<div class="row row-cols-1 row-cols-md-3 g-4 mt-4">

    <div class="col">
        <a href="acquisto.php" class="text-decoration-none">
            <div class="card shadow h-100">
                <div class="card-body text-center">
                    <div class="display-4">🛒</div>
                    <h5 class="mt-3">Acquista</h5>
                </div>
            </div>
        </a>
    </div>

    <div class="col">
        <a href="acquisti_cliente.php" class="text-decoration-none">
            <div class="card shadow h-100">
                <div class="card-body text-center">
                    <div class="display-4">🛍️</div>
                    <h5 class="mt-3">I miei Acquisti</h5>
                </div>
            </div>
        </a>
    </div>

    <div class="col">
        <a href="tessera_cliente.php" class="text-decoration-none">
            <div class="card shadow h-100">
                <div class="card-body text-center">
                    <div class="display-4">🪪</div>
                    <h5 class="mt-3">Tessera</h5>
                </div>
            </div>
        </a>
    </div>

</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>