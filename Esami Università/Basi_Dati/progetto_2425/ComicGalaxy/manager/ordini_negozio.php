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
if (isset($_SESSION['carrello'])){
    $_SESSION['carrello'] = [];
}
require_once __DIR__ . "/../lib/functions.php";

$negozio = getNegozio($_SESSION["user"]);
$ordini = getOrdiniNegozio($negozio["id"]);
$isClosed = !is_null($negozio["data_chiusura"]);

if (isset($_POST["ritira"])) {
    $id_ordine = $_POST["id_ordine"];
    ritiraOrdine($id_ordine);
    $success_msg = "Ordine ritirato con successo.";
    $_SESSION["success"] = "Ordine ritirato con successo.";
    header("Location: ordini_negozio.php?ritirato=1");
    exit();
}
?>

<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <title>Ordini del Negozio - ComicGalaxy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

    <?php include "../navbar.php"; ?>

    <div class="container my-5">

        <div class="text-center mb-4">
            <h2 class="text-primary"><?= $negozio["nome"] ?></h2>
            <h3 class="text-secondary"><?= $negozio["citta"] . ", " . $negozio["via"] . " " . $negozio["civico"] ?></h3>
        </div>

        <?php if (isset($_SESSION["success"])): ?>
            <div class="alert alert-success">
                <?= $_SESSION["success"] ?>
            </div>
            <?php unset($_SESSION["success"]); ?>
        <?php endif; ?>
        <?php if (isset($_SESSION["error"])): ?>
            <div class="alert alert-danger">
                <?= $_SESSION["error"] ?>
            </div>
            <?php unset($_SESSION["error"]); ?>
        <?php endif; ?>

        <div class="card mb-4 shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Ordini</h4>
                <?php if (!$isClosed): ?>
                    <button class="btn btn-primary btn-sm" onclick="location.href='crea_ordine.php'">Crea ordine</button>
                <?php endif; ?>
            </div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-striped table-hover table-bordered mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID Ordine</th>
                            <th>Fornitore</th>
                            <th>Data ordine</th>
                            <th>Data consegna</th>
                            <th>Totale</th>
                            <th>Stato</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ordini as $o): ?>
                            <tr>
                                <td><a href="dettaglio_ordine.php?id=<?= $o["id"] ?>"><?= $o["id"] ?></a></td>
                                <td><?= $o["nome_fornitore"] ?></td>
                                <td><?= $o["data_ordine"] ?></td>
                                <td><?= $o["data_consegna"] ?></td>
                                <td>€<?= number_format($o["totale"], 2) ?></td>
                                <td>
                                    <?= $o["stato"] ?>
                                    <?php if ($o["stato"] === "Da ritirare"): ?>
                                        <form method="POST" class="d-inline ms-2">
                                            <input type="hidden" name="id_ordine" value="<?= $o["id"] ?>">
                                            <button type="submit" name="ritira" class="btn btn-sm btn-success">Ritira</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
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