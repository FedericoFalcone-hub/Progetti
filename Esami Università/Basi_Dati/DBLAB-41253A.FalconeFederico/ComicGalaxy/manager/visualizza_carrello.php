<?php
session_start();
include '../lib/functions.php';

if (!isset($_SESSION['user']) || $_SESSION['ruolo'] !== 'manager') {
    header("Location: ../login.php");
    exit();
}
if ($_SESSION['sospeso'] === 't') {
    header("Location: area_manager.php");
    exit();
}
$negozio = getNegozio($_SESSION['user']);
$success_msg = $error_msg = null;

if (isset($_POST['conferma_ordine'])) {
    if (!empty($_SESSION['carrello'])) {
        $id_prodotti = array_keys($_SESSION['carrello']);
        $quantita = array_values($_SESSION['carrello']);
        $result = creaOrdine($negozio['id'], $id_prodotti, $quantita);
        if ($result === true) {
            $success_msg = "Ordine creato con successo!";
            $_SESSION['carrello'] = [];
        } else {
            $error_msg = "Errore durante la creazione dell'ordine: " . $result;
        }
    } else {
        $error_msg = "Il carrello è vuoto!";
    }
}
if (isset($_POST['rimuovi'])) {
    $id_prodotto = $_POST['id_prodotto'];
    if (isset($_SESSION['carrello'][$id_prodotto])) {
        unset($_SESSION['carrello'][$id_prodotto]);
        $success_msg = "Prodotto rimosso dal carrello.";
    } else {
        $error_msg = "Prodotto non trovato nel carrello.";
    }
}
$carrello = $_SESSION['carrello'] ?? [];
?>

<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <title>Riepilogo Carrello - ComicGalaxy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <?php include '../navbar.php'; ?>

    <div class="container my-5">
        <div class="text-center mb-4">
            <h1 class="fw-bold">Riepilogo Carrello</h1>
            <h2 class="text-primary"><?= $negozio['nome'] ?></h2>
        </div>

        <?php if ($success_msg): ?>
            <div class="alert alert-success"><?= $success_msg ?></div>
        <?php endif; ?>
        <?php if ($error_msg): ?>
            <div class="alert alert-danger"><?= $error_msg ?></div>
        <?php endif; ?>

        <?php if (!empty($carrello)): ?>
            <div class="card shadow-sm">
                <div class="card-body p-0 table-responsive">
                    <table class="table table-striped table-hover table-bordered mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Prodotto</th>
                                <th>Quantità</th>
                                <th>Subtotale</th>
                                <th>Azioni</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $totale = 0.0;
                            foreach ($carrello as $id => $q):
                                $prodotto = getProdottoById($id);
                                $subtotale = getSubtotaleProdotto($id, $q);
                                $totale += $subtotale;
                            ?>
                                <tr>
                                    <td><?= $prodotto['nome'] ?></td>
                                    <td><?= $q ?></td>
                                    <td>€<?= number_format($subtotale, 2) ?></td>
                                    <td style="width:120px;">
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="id_prodotto" value="<?= $id ?>">
                                            <button type="submit" name="rimuovi" class="btn btn-danger btn-sm">Rimuovi</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <tr class="table-primary">
                                <td colspan="2" class="fw-bold">Totale</td>
                                <td class="fw-bold">€<?= number_format($totale, 2) ?></td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <form method="post" class="text-center mt-3">
                <button type="submit" name="conferma_ordine" class="btn btn-success btn-lg">Conferma Ordine</button>
            </form>
        <?php else: ?>
            <div class="alert alert-warning text-center">Il carrello è vuoto.</div>
        <?php endif; ?>

        <div class="text-center mt-3">
            <a href="ordini_negozio.php" class="btn btn-secondary">Torna agli ordini</a>
            <a href="crea_ordine.php" class="btn btn-secondary">Torna indietro</a>
        </div>
    </div>


</body>

</html>