<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['ruolo'] !== 'manager') {
    header("Location: ../login.php");
    exit();
}

?>
<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <title>Area Manager - ComicGalaxy</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body>

    <?php include '../navbar.php'; ?>

    <div class="container text-center mt-5">

        <h1 class="fw-bold text-primary mb-4">Area Riservata</h1>
        <?php if ($_SESSION['sospeso'] === 't'): ?>

            <div class="alert alert-danger fs-5">
                <strong>Il tuo account è stato sospeso.</strong><br>
                Al momento non puoi accedere a nessuna funzionalità amministrativa.
            </div>


    </div>
</body>

</html>
<?php exit(); ?>
<?php endif; ?>



<div class="row row-cols-1 row-cols-md-3 g-4 mt-4">

    <div class="col">
        <a href="gestione_negozio.php" class="text-decoration-none">
            <div class="card shadow h-100">
                <div class="card-body text-center">
                    <div class="display-4">🏪</div>
                    <h5 class="mt-3">Gestione Negozio</h5>
                </div>
            </div>
        </a>
    </div>

    <div class="col">
        <a href="gestione_clienti.php" class="text-decoration-none">
            <div class="card shadow h-100">
                <div class="card-body text-center">
                    <div class="display-4">👥</div>
                    <h5 class="mt-3">Gestione Clienti</h5>
                </div>
            </div>
        </a>
    </div>

    <div class="col">
        <a href="gestione_manager.php" class="text-decoration-none">
            <div class="card shadow h-100">
                <div class="card-body text-center">
                    <div class="display-4">👔</div>
                    <h5 class="mt-3">Gestione Manager</h5>
                </div>
            </div>
        </a>
    </div>

    <div class="col">
        <a href="gestione_negozi.php" class="text-decoration-none">
            <div class="card shadow h-100">
                <div class="card-body text-center">
                    <div class="display-4">🔄</div>
                    <h5 class="mt-3">Gestione Negozi</h5>
                </div>
            </div>
        </a>
    </div>

    <div class="col">
        <a href="gestione_fornitori.php" class="text-decoration-none">
            <div class="card shadow h-100">
                <div class="card-body text-center">
                    <div class="display-4">🚚</div>
                    <h5 class="mt-3">Gestione Fornitori</h5>
                </div>
            </div>
        </a>
    </div>

    <div class="col">
        <a href="statistiche.php" class="text-decoration-none">
            <div class="card shadow h-100">
                <div class="card-body text-center">
                    <div class="display-4">📊</div>
                    <h5 class="mt-3">Report & Statistiche</h5>
                </div>
            </div>
        </a>
    </div>

</div>
</div>

</body>

</html>