<?php
session_start();


if (!isset($_SESSION['user']) || $_SESSION['ruolo'] !== 'manager') {
     header("Location: login.php");
     exit();
}

require_once "../lib/functions.php";


$negozi = getNegozi();

$negozio_scelto = $_GET['negozio'] ?? '';

if ($negozio_scelto !== '') {
    $tesserati = tesseratiNegozio($negozio_scelto);
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

    <!-- SELEZIONE NEGOZIO -->
    <form method="GET" class="card p-4 shadow mb-4">
        <label class="form-label fw-semibold">Seleziona Negozio</label>
        <div class="input-group">
            <select name="negozio" class="form-select" required>
                <option value="">-- Scegli un negozio --</option>
                <?php foreach ($negozi as $n): ?>
                    <option value="<?= $n['id'] ?>" 
                        <?= $negozio_scelto == $n['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($n['nome']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button class="btn btn-primary">Mostra</button>
        </div>
    </form>

    <!-- RISULTATI -->
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
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($tesserati)): ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted">
                                    Nessun tesserato trovato per questo negozio.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($tesserati as $t): ?>
                                <tr>
                                    <td><?= htmlspecialchars($t['nome']) ?></td>
                                    <td><?= htmlspecialchars($t['cognome']) ?></td>
                                     <td><?= htmlspecialchars($t['mail']) ?></td>
                                    <td><?= htmlspecialchars($t['saldo']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    <?php endif; ?>

    <div class="mt-4 text-center">
        <a href="statistiche.php" class="btn btn-secondary">Torna alle Statistiche</a>
    </div>

</div>

</body>
</html>
