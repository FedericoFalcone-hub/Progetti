<?php
session_start();


if (!isset($_SESSION['user']) || $_SESSION['ruolo'] !== 'manager') {
    header("Location: login.php");
    exit();
}
if ($_SESSION['sospeso'] === 't') {
    header("Location: area_manager.php");
    exit();
}
require_once "../lib/functions.php";


$negozi = getNegozi();

$negozio_scelto = $_GET['negozio'] ?? '';

if ($negozio_scelto !== '') {
    $tesserati = tesseratiNegozio($negozio_scelto);
}
if (isset($_POST['sospendi'])) {
    $cf = $_POST['cf'];
    if (sospendi_tessera($cf)) {
        $success_msg = "Tessera sospesa con successo!";
    } else {
        $error_msg = "Errore durante la sospensione dell'utente.";
    }

    $_SESSION["success"] = "Tessera sospesa con successo.";

    header("Location: tesserati_negozi.php?negozio=" . urlencode($negozio_scelto));
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

    header("Location: tesserati_negozi.php?negozio=" . urlencode($negozio_scelto));
    exit();
}

if (isset($_POST['rinnova'])) {
    $cf = $_POST['cf'];
    var_dump($cf);
    $result = rinnova_tessera($cf);

    if ($result === true) {
        $_SESSION["success"] = "Tessera rinnovata con successo.";
    } else {
        $_SESSION["error"] = "Impossibile rinnovare la tessera di un cliente sospeso";
    }

    header("Location: tesserati_negozi.php?negozio=" . urlencode($negozio_scelto));
    exit();
}

?>
<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <title>Tesserati per Negozio</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body>

    <?php include '../navbar.php'; ?>

    <div class="container mt-5">


        <h1 class="text-primary fw-bold mb-4 text-center">Tesserati per Negozio</h1>

        <form method="GET" class="card p-4 shadow mb-4">
            <label class="form-label fw-semibold">Seleziona Negozio</label>
            <div class="input-group">
                <select name="negozio" class="form-select" required>
                    <option value="">-- Scegli un negozio --</option>
                    <?php foreach ($negozi as $n): ?>
                        <option value="<?= $n['id'] ?>"
                            <?= $negozio_scelto == $n['id'] ? 'selected' : '' ?>>
                            <?= $n['nome'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button class="btn btn-primary">Mostra</button>
            </div>
        </form>
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

        <?php if ($negozio_scelto !== ''): ?>

            <div class="card shadow-sm">
                <div class="card-body p-0 table-responsive">
                    <table class="table table-striped table-hover table-bordered mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Nome</th>
                                <th>Cognome</th>
                                <th>Mail</th>
                                <th>Saldo punti</th>
                                <th>Stato Tessera</th>
                                <th>Azioni</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($tesserati)): ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted">
                                        Nessun tesserato trovato per questo negozio.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($tesserati as $t): ?>
                                    <tr>
                                        <td><?= $t['nome'] ?></td>
                                        <td><?= $t['cognome'] ?></td>
                                        <td><?= $t['mail'] ?></td>
                                        <td><?= $t['saldo'] ?></td>
                                        <td>
                                            <?php if ($t["stato"] === "attiva"): ?>
                                                <span class="badge bg-success">Attivo</span>
                                            <?php elseif ($t["stato"] === "scaduta"): ?>
                                                <span class="badge bg-warning text-dark">Scaduta</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Sospeso</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <form action="tesserati_negozi.php?negozio=<?= urlencode($negozio_scelto) ?>" method="POST" style="display:inline;">

                                                <input type="hidden" name="cf" value="<?= $t['cf'] ?>">
                                                <input type="hidden" name="return" value="gestione_negozio">
                                                <?php if ($t["stato"] === "attiva"): ?>
                                                    <button type="submit" name="sospendi" class="btn btn-sm btn-danger">
                                                        Sospendi tessera
                                                    </button>
                                                <?php elseif ($t["stato"] === "scaduta"): ?>
                                                    <button type="submit" name="rinnova" class="btn btn-sm btn-warning">
                                                        Rinnova tessera
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
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <?php endif; ?>

        <div class="mt-4 text-center">
            <a href="report.php" class="btn btn-secondary">Torna al Report</a>
        </div>

    </div>

</body>

</html>