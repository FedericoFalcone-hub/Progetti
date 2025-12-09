<?php
session_start();


if (!isset($_SESSION["user"]) || $_SESSION["ruolo"] !== "manager") {
    header("Location: ../login.php");
    exit();
}

require_once __DIR__ . "/../lib/functions.php";

$negozio = getNegozio($_SESSION["user"]);
$prodotti = getProdotti($negozio["id"]);
$isClosed = !is_null($negozio["data_chiusura"]);
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Prodotti del Negozio - ComicGalaxy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<?php include "../navbar.php"; ?>

<div class="container my-5">

        <div class="text-center mb-4">
        <h2 class="text-primary"><?= htmlspecialchars($negozio["nome"]) ?></h2>
        <h3 class="text-secondary"><?= htmlspecialchars($negozio["citta"] . ", " . $negozio["via"] . " " . $negozio["civico"]) ?></h3>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Lista Prodotti</h4>
            <?php if (!$isClosed): ?>
                <button class="btn btn-primary btn-sm" onclick="location.href='gestione_prodotti.php'">
                    Gestisci Prodotti
                </button>
            <?php endif; ?>
        </div>

        <div class="card-body p-0 table-responsive">
            <table class="table table-striped table-hover table-bordered mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nome</th>
                        <th>Prezzo</th>
                        <th>Quantità</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($prodotti as $p): ?>
                    <tr>
                        <td><?= htmlspecialchars($p["nome"]) ?></td>
                        <td>
                            <?= $p["prezzo"] === null ? "<strong>Non in vendita</strong>" : "€" . htmlspecialchars($p["prezzo"]) ?>
                        </td>
                        <td><?= htmlspecialchars($p["quantita"]) ?></td>
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
