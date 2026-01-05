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

if (!isset($_GET['id'])) {
    header("Location: acquisti_cliente.php");
    exit();
}

$id = $_GET['id'];

$tessera = getTessera($_SESSION['user']);
$acquisto = getDettaglioAcquisto($id);
$sconto = $acquisto[0]['sconto'] ?? 0;
if (!$acquisto) {
    header("Location: acquisti_cliente.php");
    exit();
}

$punti_guadagnati = floor($totale_finale);

?>
<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <title>Dettaglio Acquisto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

    <?php include '../navbar.php'; ?>

    <div class="container mt-5">
        <h1 class="fw-bold text-primary text-center">Dettaglio Acquisto #<?= $id ?></h1>
        <div class="mt-4 text-start">
            <a href="/cliente/acquisti_cliente.php" class="btn btn-secondary">
                Torna allo storico
            </a>
        </div>
        <div class="card shadow-sm mt-4">

            <div class="card-header fw-bold">Prodotti acquistati</div>
            <div class="card-body table-responsive">
                <table class="table table-striped table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Prodotto</th>
                            <th>Quantità</th>
                            <th>Prezzo Unitario</th>
                            <th>Subtotale</th>
                        </tr>
                    </thead>
                    <tbody>

                        <?php
                        $totale_prodotti = 0;

                        foreach ($acquisto as $riga):
                            $sub = $riga['quantita'] * $riga['prezzo'];
                            $totale_prodotti += $sub;
                        ?>
                            <tr>
                                <td><?= $riga['nome_prodotto'] ?></td>
                                <td><?= $riga['quantita'] ?></td>
                                <td>€<?= number_format($riga['prezzo'], 2) ?></td>
                                <td>€<?= number_format($sub, 2) ?></td>
                            </tr>
                        <?php endforeach; ?>

                        <!-- Totale prodotti -->
                        <tr class="table-secondary fw-bold">
                            <td colspan="3" class="text-end">Totale prodotti:</td>
                            <td>€<?= number_format($totale_prodotti, 2) ?></td>
                        </tr>

                        <!-- Sconto -->
                        <tr class="table-warning fw-bold">
                            <td colspan="3" class="text-end">
                                Sconto applicato (<?= $sconto ?>%):
                            </td>
                            <td>
                                - €<?= number_format($totale_prodotti * ($sconto / 100), 2) ?>
                            </td>
                        </tr>

                        <!-- Totale finale -->
                        <?php
                        $totale_finale = $totale_prodotti - ($totale_prodotti * ($sconto / 100));
                        ?>
                        <tr class="table-info fw-bold">
                            <td colspan="3" class="text-end">Totale finale:</td>
                            <td>€<?= number_format($totale_finale, 2) ?></td>
                        </tr>

                        <?php
                        $punti_guadagnati = floor($totale_finale);
                        ?>
                        <tr class="table-success fw-bold">
                            <td colspan="3" class="text-end">Punti guadagnati:</td>
                            <td><?= $punti_guadagnati ?> pts</td>
                        </tr>


                    </tbody>
                </table>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>