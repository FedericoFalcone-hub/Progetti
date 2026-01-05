<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['ruolo'] !== 'manager') {
    header("Location: ../login.php");
    exit();
}
if ($_SESSION['sospeso'] === 't') {
    header("Location: area_manager.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <title>Report - ComicGalaxy</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body>

    <?php include '../navbar.php'; ?>

    <div class="container text-center mt-5">

        <h1 class="fw-bold text-primary mb-4">Report</h1>
        


        <div class="row row-cols-1 row-cols-md-3 g-4 mt-4">

            <div class="col">
                <a href="tesserati_negozi.php" class="text-decoration-none">
                    <div class="card shadow h-100">
                        <div class="card-body text-center">
                            <div class="display-4">🪪</div>
                            <h5 class="mt-3">Tesserati per Negozio</h5>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col">
                <a href="tesserati_punti_elevati.php" class="text-decoration-none">
                    <div class="card shadow h-100">
                        <div class="card-body text-center">
                            <div class="display-4">🎖️</div>
                            <h5 class="mt-3">Tesserati con +300 Punti</h5>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col">
                <a href="ordini_fornitore.php" class="text-decoration-none">
                    <div class="card shadow h-100">
                        <div class="card-body text-center">
                            <div class="display-4">📦</div>
                            <h5 class="mt-3">Ordini per Fornitore</h5>
                        </div>
                    </div>
                </a>
            </div>

        </div>

        <div class="mt-4">
            <a href="area_manager.php" class="btn btn-secondary">Torna all’Area Manager</a>
        </div>

    </div>


</body>

</html>