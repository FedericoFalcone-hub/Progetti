<?php
session_start();


if (!isset($_SESSION["user"]) || $_SESSION["ruolo"] !== "manager") {
    header("Location: ../login.php");
    exit();
}

require_once __DIR__ . "/../lib/functions.php";

$negozio = getNegozio($_SESSION["user"]);
$fatture = getFattureNegozio($negozio["id"]);
$isClosed = !is_null($negozio["data_chiusura"]);

if (isset($_POST['sospendi'])) {
    $cf = $_POST['cf'];
    if (sospendi_tessera($cf)) {
        $success_msg = "Tessera sospesa con successo!";
    } else {
        $error_msg = "Errore durante la sospensione dell'utente.";
    }

    $_SESSION["success"] = "Tessera sospesa con successo.";

    header("Location: tesserati_negozio.php");
    exit();
}

if (isset($_POST['riattiva'])) {
    $cf = $_POST['cf'];
    if (riattiva_tessera($cf)) {
        $success_msg = "Tessera riattivata con successo!";
    } else {
        $error_msg = "Errore durante la riattivazione dell'utente.";
    }

    $_SESSION["success"] = "Tessera riattivata con successo.";

    header("Location: tesserati_negozio.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Clienti Tesserati del Negozio - ComicGalaxy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<?php include "../navbar.php"; ?>

<div class="container my-5">

    <div class="text-center mb-4">
        <h2 class="text-primary"><?= htmlspecialchars($negozio["nome"]) ?></h2>
        <h3 class="text-secondary"><?= htmlspecialchars($negozio["citta"] . ", " . $negozio["via"] . " " . $negozio["civico"]) ?></h3>
    </div>

    <?php if (isset($_SESSION["success"])): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($_SESSION["success"]) ?>
        </div>
        <?php unset($_SESSION["success"]); ?>
    <?php endif; ?>
    <?php if (isset($_SESSION["error"])): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($_SESSION["error"]) ?>
        </div>
        <?php unset($_SESSION["error"]); ?>
    <?php endif; ?>
    
    <div class="card mb-4 shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Fatture Negozio</h4>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-striped table-hover table-bordered mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID Fattura</th>
                        <th>Data Acquisto</th>
                        <th>Sconto applicato</th>
                        <th>Importo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                   
                    foreach ($fatture as $f): ?>
                    <tr>
                        <td><a href="dettaglio_fattura.php?id=<?= $f["id"] ?>"><?= htmlspecialchars($f["id"]) ?></a></td>
                        <td><?= htmlspecialchars($f["data_acquisto"]) ?></td>
                        <td><?= htmlspecialchars($f["sconto"]) ?>%</td>
                        <td>€<?= number_format($f["totale"], 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>


    <div class="mt-4 text-center">
        <a href="gestione_negozio.php" class="btn btn-secondary">Torna alla gestione negozio</a>
    </div>

</div>

</body>
</html>
