<?php
session_start();

if (!isset($_SESSION['user']) || !in_array($_SESSION['ruolo'], ['admin', 'manager'])) {
    header("Location: ../login.php");
    exit();
}
if ($_SESSION['sospeso'] === 't') {
    header("Location: area_manager.php");
    exit();
}
require_once '../lib/functions.php';


if (!isset($_GET['p_iva'])) {
    header("Location: gestione_fornitori.php");
    exit();
}
$p_iva = $_GET['p_iva'];


$fornitore = getFornitoreByPIVA($p_iva);
if (!$fornitore) {
    header("Location: gestione_fornitori.php");
    exit();
}

$prodotti = getProdottiFornitore($p_iva);

?>
<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <title>Prodotti del Fornitore - ComicGalaxy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

    <?php include '../navbar.php'; ?>

    <div class="container my-5">

        <div class="mb-4 text-center">
            <h1 class="fw-bold">Prodotti di <?= htmlspecialchars($fornitore['nome']) ?> 📦</h1>
            <p class="text-muted">Elenco dei prodotti forniti da questo fornitore</p>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-0 table-responsive">
                <table class="table table-striped table-hover table-bordered mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Nome Prodotto</th>
                            <th>Prezzo (€)</th>
                            <th>Quantità disponibile</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($prodotti)): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-3">Nessun prodotto trovato.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($prodotti as $p): ?>
                                <tr>
                                    <td><?= htmlspecialchars($p['nome']) ?></td>
                                    <td><?= number_format($p['prezzo'], 2) ?></td>
                                    <td><?= htmlspecialchars($p['quantita']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4">
            <a href="gestione_fornitori.php" class="btn btn-secondary">Torna ai fornitori</a>
        </div>

    </div>

</body>

</html>