<?php
session_start();
include 'lib/functions.php';

// Controllo che sia passato l'id del prodotto
if (!isset($_GET['id'])) {
    header("Location: prodotti.php");
    exit();
}

$id_prodotto = intval($_GET['id']);

// Recupero informazioni sul prodotto
$prodotto = getProdottoById($id_prodotto); // funzione da definire in functions.php
if (!$prodotto) {
    echo "Prodotto non trovato.";
    exit();
}

// Recupero disponibilità nei negozi
$disponibilita = getDisponibilitaProdotto($id_prodotto); // funzione da definire
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Disponibilità - <?= htmlspecialchars($prodotto['nome']) ?> - ComicGalaxy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="container my-5">

    <div class="mb-4">
        <h1 class="fw-bold">Disponibilità di "<?= htmlspecialchars($prodotto['nome']) ?>"</h1>
        <p class="text-muted"><?= htmlspecialchars($prodotto['descrizione']) ?></p>
    </div>

    <?php if (count($disponibilita) > 0): ?>
        <div class="card shadow-sm">
            <div class="card-body p-0 table-responsive">
                <table class="table table-striped table-hover table-bordered mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Negozio</th>
                            <th>Indirizzo</th>
                            <th>Prezzo</th>
                            <th>Quantità disponibile</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($disponibilita as $d): ?>
                            <tr>
                                <td><?= htmlspecialchars($d['nome_negozio']) ?></td>
                                <td><?= htmlspecialchars($d['indirizzo']) ?></td>
                                <td>€ <?= number_format($d['prezzo'], 2, ',', '.') ?></td>
                                <td><?= intval($d['quantita']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-warning">
            Questo prodotto non è disponibile in nessun negozio al momento.
        </div>
    <?php endif; ?>

    <div class="mt-4">
        <a href="lista_prodotti.php" class="btn btn-secondary">Torna ai prodotti</a>
    </div>

</div>

</body>
</html>
