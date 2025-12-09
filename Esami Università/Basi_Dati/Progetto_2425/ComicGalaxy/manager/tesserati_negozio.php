<?php
session_start();

if (!isset($_SESSION["user"]) || $_SESSION["ruolo"] !== "manager") {
    header("Location: ../login.php");
    exit();
}

require_once __DIR__ . "/../lib/functions.php";

$negozio = getNegozio($_SESSION["user"]);
$clienti = tesseratiNegozio($negozio["id"]); 
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
        <div class="card-header">
            <h4 class="mb-0">Clienti Tesserati</h4>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-striped table-hover table-bordered mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nome</th>
                        <th>Cognome</th>
                        <th>Email</th>
                        <th>Rilascio Tessera</th>
                        <th>Scadenza Tessera</th>
                        <th>Punti Tessera</th>
                        <th>Stato Tessera</th>
                        <th>Azioni</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clienti as $c): ?>
                    <tr>
                        <td><?= htmlspecialchars($c["nome"]) ?></td>
                        <td><?= htmlspecialchars($c["cognome"]) ?></td>
                        <td><?= htmlspecialchars($c["mail"]) ?></td>
                        <td><?= htmlspecialchars($c["data_emissione"]) ?></td>
                        <td><?= htmlspecialchars($c["data_scadenza"]) ?></td>
                        <td><?= htmlspecialchars($c["saldo"]) ?></td>
                        <td>
                            <?php if ($c["sospeso"] === "f"): ?>
                                <span class="badge bg-success">Attivo</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Sospeso</span>
                            <?php endif; ?>
                        </td>
                        <td>
                           <form action="tesserati_negozio.php" method="POST" style="display:inline;">
                                <input type="hidden" name="cf" value="<?= htmlspecialchars($c['cf']) ?>">
                                <input type="hidden" name="return" value="gestione_negozio">
                                <?php if ($c["sospeso"] === "f"): ?>
                                    <button type="submit" name="sospendi" class="btn btn-sm btn-danger">
                                        Sospendi tessera
                                    </button>
                                <?php else: ?>
                                    <button type="submit" name="riattiva" class="btn btn-sm btn-success">
                                        Riattiva tessera
                                    </button>
                                <?php endif; ?>
                            </form>
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
