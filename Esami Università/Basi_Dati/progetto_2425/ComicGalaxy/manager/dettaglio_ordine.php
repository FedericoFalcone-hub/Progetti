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
include '../lib/functions.php';

if (!isset($_GET['id'])) {
    die("ID ordine mancante.");
}

$id_ordine = intval($_GET['id']);

$ordine = getDettaglioOrdine($id_ordine);
if (!$ordine) {
    die("Ordine non trovato.");
}

$prodotti = getProdottiOrdine($id_ordine);

?>
<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <title>Dettaglio Ordine #<?= $id_ordine ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

    <?php include '../navbar.php'; ?>

    <div class="container my-5">
        <div class="text-center mb-4">
            <h1 class="fw-bold">Dettaglio Ordine #<?= $id_ordine ?></h1>
        </div>



        <div class="card mb-4 shadow-sm">
            <div class="card-header">
                <h4 class="mb-0">Informazioni ordine</h4>
            </div>
            <div class="card-body">
                <p><strong>Fornitore:</strong> <?= $ordine['nome_fornitore'] ?></p>
                <p><strong>Data ordine:</strong> <?= $ordine['data_ordine'] ?></p>
                <p><strong>Data consegna:</strong> <?= $ordine['data_consegna'] ?></p>
                <p><strong>Totale:</strong> €<?= number_format($ordine['totale'], 2) ?></p>
                <p><strong>Stato:</strong> <?= $ordine['stato'] ?></p>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header">
                <h4 class="mb-0">Prodotti ordinati</h4>
            </div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-striped table-hover table-bordered mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Prodotto</th>
                            <th>Quantità</th>
                            <th>Prezzo unitario</th>
                            <th>Totale</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($prodotti as $p): ?>
                            <tr>
                                <td><?= $p['nome_prodotto'] ?></td>
                                <td><?= $p['quantita'] ?></td>
                                <td>€<?= number_format($p['prezzo'], 2) ?></td>
                                <td>€<?= number_format($p['totale'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-4 text-center">
            <a href="<?= $_GET['return'] ?? '../manager/ordini_negozio.php' ?>" class="btn btn-secondary">Torna Indietro</a>
        </div>
    </div>

</body>

</html>