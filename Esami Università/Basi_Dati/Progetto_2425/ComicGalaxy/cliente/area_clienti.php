<?php
session_start();

// Controllo accesso: solo cliente
if (!isset($_SESSION['user']) || $_SESSION['ruolo'] !== 'cliente') {
    header("Location: ../login.php");
    exit();
}
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

    <h1 class="fw-bold text-primary mb-4">Area Riservata Cliente</h1>
    <p class="fs-5 text-secondary">
        Da qui puoi gestire il tuo profilo e monitorare le tue attività su ComicGalaxy.
    </p>

    <!-- Grid cards -->
    <div class="row row-cols-1 row-cols-md-3 g-4 mt-4">

        <div class="col">
            <a href="visualizza_ordini.php" class="text-decoration-none">
                <div class="card shadow h-100">
                    <div class="card-body text-center">
                        <div class="display-4">🛒</div>
                        <h5 class="mt-3">I miei Ordini</h5>
                    </div>
                </div>
            </a>
        </div>

        <div class="col">
            <a href="profilo_cliente.php" class="text-decoration-none">
                <div class="card shadow h-100">
                    <div class="card-body text-center">
                        <div class="display-4">👤</div>
                        <h5 class="mt-3">Profilo</h5>
                    </div>
                </div>
            </a>
        </div>

        <div class="col">
            <a href="punti_fedelta.php" class="text-decoration-none">
                <div class="card shadow h-100">
                    <div class="card-body text-center">
                        <div class="display-4">⭐</div>
                        <h5 class="mt-3">Punti Fedeltà</h5>
                    </div>
                </div>
            </a>
        </div>

        <div class="col">
            <a href="storico_transazioni.php" class="text-decoration-none">
                <div class="card shadow h-100">
                    <div class="card-body text-center">
                        <div class="display-4">📜</div>
                        <h5 class="mt-3">Storico Transazioni</h5>
                    </div>
                </div>
            </a>
        </div>

        <div class="col">
            <a href="coupon_attivi.php" class="text-decoration-none">
                <div class="card shadow h-100">
                    <div class="card-body text-center">
                        <div class="display-4">🎟️</div>
                        <h5 class="mt-3">Coupon Attivi</h5>
                    </div>
                </div>
            </a>
        </div>

        <div class="col">
            <a href="assistenza.php" class="text-decoration-none">
                <div class="card shadow h-100">
                    <div class="card-body text-center">
                        <div class="display-4">💬</div>
                        <h5 class="mt-3">Assistenza</h5>
                    </div>
                </div>
            </a>
        </div>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
    