<?php
session_start();

if (!isset($_SESSION["user"]) || $_SESSION["ruolo"] !== "manager") {
    header("Location: ../login.php");
    exit();
}
if ($_SESSION['sospeso'] === 't') {
    header("Location: area_manager.php");
    exit();
}

require_once __DIR__ . "/../lib/functions.php";

if (!isset($_GET["id"])) {
    header("Location: fatture_negozio.php");
    exit();
}

$id_fattura = $_GET["id"];
$fattura = getFatturaById($id_fattura);


if (!$fattura) {
    $_SESSION["error"] = "Fattura non trovata o accesso negato";
    header("Location: fatture_negozio.php");
    exit();
}

$prodotti = getProdottiFattura($id_fattura);


?>

<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <title>Dettaglio Fattura - ComicGalaxy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

    <?php include "../navbar.php"; ?>

    <div class="container my-5">

        <h2 class="text-primary text-center mb-4">
            Dettaglio Fattura #<?= $fattura["id"] ?>
        </h2>

        <div class="card shadow-sm mb-4">
            <div class="card-header">
                <h4 class="mb-0">Informazioni Fattura</h4>
            </div>
            <div class="card-body">
                <p><strong>Data:</strong> <?= $fattura["data_acquisto"] ?></p>
                <p><strong>Sconto applicato:</strong> <?= $fattura["sconto"] ?>%</p>
                <p><strong>Importo totale:</strong> €<?= number_format($fattura["totale"], 2) ?></p>
                <p><strong>Cliente:</strong> <?= $fattura["nome"] . " " . $fattura["cognome"] ?></p>
                <p><strong>Codice fiscale:</strong> <?= $fattura["cf_cliente"] ?></p>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header">
                <h4 class="mb-0">Prodotti Acquistati</h4>
            </div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-striped table-hover table-bordered mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Prodotto</th>
                            <th>Prezzo Unitario</th>
                            <th>Quantità</th>
                            <th>Subtotale</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($prodotti as $p): ?>
                            <tr>
                                <td><?= $p["nome"] ?></td>
                                <td>€<?= number_format($p["prezzo"], 2) ?></td>
                                <td><?= $p["quantita"] ?></td>
                                <td>€<?= number_format($p["prezzo"] * $p["quantita"], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4 text-center">
            <a href="fatture_negozio.php" class="btn btn-secondary">
                Torna indietro
            </a>
        </div>

    </div>

</body>

</html>