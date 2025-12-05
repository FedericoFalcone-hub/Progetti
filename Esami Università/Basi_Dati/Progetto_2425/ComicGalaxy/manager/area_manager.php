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

    <!-- BOOTSTRAP -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

</head>
<body>

<?php include '../navbar.php'; ?>

<div class="container text-center mt-5">

    <h1 class="fw-bold text-primary mb-4">Area Riservata Manager</h1>
    <p class="fs-5 text-secondary">
        Da qui puoi gestire tutte le operazioni amministrative del tuo negozio ComicGalaxy.
    </p>

    <!-- Grid cards -->
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
            <a href="#" class="text-decoration-none">
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

<!-- BOOTSTRAP JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
