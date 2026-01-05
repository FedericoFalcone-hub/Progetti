<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['ruolo'] !== 'cliente') {
    header("Location: ../login.php");
    exit();
}
if ($_SESSION['sospeso'] === 't') {
    header("Location: area_clienti.php");
    exit();
}
require_once '../lib/functions.php';

$acquisti = getAcquistiCliente($_SESSION['user']);
?>
<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <title>I miei acquisti - ComicGalaxy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

    <?php include '../navbar.php'; ?>

    <div class="container mt-5">

        <h1 class="fw-bold text-primary text-center">🛍️ I miei Acquisti</h1>
        <p class="text-center text-secondary">Qui puoi vedere tutti gli acquisti effettuati.</p>
        <div class="mt-4 text-start">
            <a href="/cliente/area_clienti.php" class="btn btn-secondary">
                Torna all'area riservata
            </a>
        </div>

        <?php if (empty($acquisti)): ?>
            <div class="alert alert-warning text-center mt-4">
                Non hai ancora effettuato acquisti.
            </div>
            <div class="text-center mt-3">
                <a href="acquisto.php" class="btn btn-primary">Vai agli acquisti</a>
            </div>
        <?php else: ?>
            <div class="card shadow-sm mt-4">
                <div class="card-header fw-bold">Storico Acquisti</div>
                <div class="card-body table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Codice</th>
                                <th>Data</th>
                                <th>Negozio</th>
                                <th>Sconto Applicato</th>
                                <th>Totale</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($acquisti as $acquisto): ?>
                                <tr>
                                    <td><a href="dettaglio_acquisto.php?id=<?= $acquisto['id'] ?>">
                                            <?= $acquisto['id'] ?>
                                        </a></td>
                                    <td><?= $acquisto['data_acquisto'] ?></td>
                                    <td><?= $acquisto['nome_negozio'] ?></td>
                                    <td><?= $acquisto['sconto'] ?>%</td>
                                    <td>€<?= number_format($acquisto['totale'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>