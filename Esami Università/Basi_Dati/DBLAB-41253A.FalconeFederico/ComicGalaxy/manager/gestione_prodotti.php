<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['ruolo'] !== 'manager') {
    header("Location: ../login.php");
    exit();
}
if ($_SESSION['sospeso'] === 't') {
    header("Location: area_manager.php");
    exit();
}
include '../lib/functions.php';

$negozio = getNegozio($_SESSION['user']);
if ($negozio === null) {
    die('<div class="container text-center mt-5">
            <div class="card shadow p-4">
                <h2 class="text-danger">Errore</h2>
                <p>Non sei associato a nessun negozio.</p>
                <a href="index.php" class="btn btn-primary">Torna alla Home</a>
            </div>
        </div>');
}

$prodotti = getProdotti($negozio['id']);

$success_msg = $error_msg = null;

if (isset($_POST['modifica'])) {
    $id_prodotto = $_POST['id_prodotto'];
    $prezzo = $_POST['prezzo'];

    if (aggiornaPrezzoProdotto($negozio['id'], $id_prodotto, $prezzo)) {
        $success_msg = "Prezzo aggiornato correttamente!";
        $prodotti = getProdotti($negozio['id']);
    } else {
        $error_msg = "Errore nell'aggiornamento del prezzo.";
    }
}

if (isset($_POST['elimina'])) {
    $id_prodotto = $_POST['id_prodotto'];

    if (eliminaFornituraProdotto($negozio['id'], $id_prodotto)) {
        $success_msg = "Prodotto eliminato correttamente!";
        $prodotti = getProdotti($negozio['id']);
    } else {
        $error_msg = "Errore durante l'eliminazione del prodotto.";
    }
}

if (isset($_POST['rimuovi_vendita'])) {
    $id_prodotto = $_POST['id_prodotto'];

    if (rimuoviVenditaProdotto($negozio['id'], $id_prodotto)) {
        $success_msg = "Vendita del prodotto sospesa correttamente!";
        $prodotti = getProdotti($negozio['id']);
    } else {
        $error_msg = "Errore durante la sospensione della vendita del prodotto.";
    }
}
?>

<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <title>Gestione Prodotti - ComicGalaxy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

    <?php include '../navbar.php'; ?>

    <div class="container my-5">

        <div class="text-center mb-4">
            <h1 class="fw-bold">Gestione Prodotti</h1>
            <h2 class="text-primary"><?=$negozio['nome'] ?></h2>
        </div>

        <?php if ($success_msg): ?>
            <div class="alert alert-success"><?= $success_msg ?></div>
        <?php endif; ?>
        <?php if ($error_msg): ?>
            <div class="alert alert-danger"><?= $error_msg ?></div>
        <?php endif; ?>

        <div class="card shadow-sm">
            <div class="card-body p-0 table-responsive">
                <table class="table table-striped table-hover table-bordered mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Nome</th>
                            <th>Prezzo</th>
                            <th>Quantità</th>
                            <th>Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($prodotti as $p): ?>
                            <tr>
                                <td><?= $p['nome'] ?></td>
                                <td><?= $p['prezzo'] !== null ? "€" . $p['prezzo'] : "<strong>Non in vendita</strong>" ?></td>
                                <td><?= $p['quantita'] ?></td>
                                <td>
                                    <form method="post" class="d-flex gap-2">
                                        <input type="number" step="0.01" name="prezzo" class="form-control form-control-sm" value="<?= $p['prezzo'] ?>">
                                        <input type="hidden" name="id_prodotto" value="<?= $p['id_prodotto'] ?>">
                                        <button type="submit" name="modifica" class="btn btn-primary btn-sm">Modifica</button>
                                        <button type="submit" name="elimina" class="btn btn-danger btn-sm" onclick="return confirm('Sei sicuro di voler eliminare questo prodotto?');">Elimina</button>
                                         <button type="submit" name="rimuovi_vendita" class="btn btn-warning btn-sm text-nowrap <?= $p['prezzo'] === null ? 'invisible' : '' ?>">Sospendi vendita

                                         </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($prodotti)): ?>
                            <tr>
                                <td colspan="4" class="text-center">Nessun prodotto presente</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
    <div class="text-center mb-4">
        <a href="prodotti_negozio.php" class="btn btn-secondary mb-3">Torna indietro</a>

    </div>

</body>

</html>