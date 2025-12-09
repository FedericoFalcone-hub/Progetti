<?php
session_start();


if (!isset($_SESSION['user']) || $_SESSION['ruolo'] !== 'manager') {
     header("Location: login.php");
     exit();
}

require_once "../lib/functions.php";


$fornitori = getFornitori();

$fornitore_scelto = $_GET['fornitore'] ?? '';

if ($fornitore_scelto !== '') {
    $ordini = ordiniFornitore($fornitore_scelto);
}

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Ordini per Fornitore</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

</head>
<body>

<?php include '../navbar.php'; ?>

<div class="container mt-5">

    <h1 class="text-primary fw-bold mb-4 text-center">Ordini per Fornitore</h1>

    <!-- SELEZIONE FORNITORE -->
    <form method="GET" class="card p-4 shadow mb-4">
        <label class="form-label fw-semibold">Seleziona Fornitore</label>
        <div class="input-group">
            <select name="fornitore" class="form-select" required>
                <option value="">-- Scegli un fornitore --</option>
                <?php foreach ($fornitori as $f): ?>
                    <option value="<?= $f['p_iva'] ?>" 
                        <?= $fornitore_scelto == $f['p_iva'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($f['nome']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button class="btn btn-primary">Mostra</button>
        </div>
    </form>

    <!-- RISULTATI -->
    <?php if ($fornitore_scelto !== ''): ?>

        <div class="card shadow-sm">
            <div class="card-body p-0 table-responsive">
                <table class="table table-striped table-hover table-bordered mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Codice ordine</th>
                            <th>Negozio</th>
                            <th>Data Ordine</th>
                            <th>Data Consegna</th>
                            <th>Subtotale</th>
                            <th>Stato</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($ordini)): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted">
                                    Nessun ordine trovato per questo fornitore.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($ordini as $o): ?>
                                <tr>
                                    <td>
                                        <a href="dettaglio_ordine.php?id=<?= htmlspecialchars($o['id_ordine']) ?>&return=<?= urlencode($_SERVER['REQUEST_URI']) ?>">
                                            <?= htmlspecialchars($o['id_ordine']) ?>
                                        </a>
                                    </td>
                                    <td><?= htmlspecialchars($o['nome_negozio']) ?></td>
                                    <td><?= htmlspecialchars($o['data_ordine']) ?></td>
                                    <td><?= htmlspecialchars($o['data_consegna']) ?></td>
                                    <td>€<?= htmlspecialchars($o['totale']) ?></td>
                                    <td><?= htmlspecialchars($o['stato']) ?></td>
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
