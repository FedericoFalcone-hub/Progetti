<?php
session_start();
include 'lib/functions.php';

if (!isset($_SESSION['user']) || $_SESSION['ruolo'] !== 'manager') {
    header("Location: login.php");
    exit();
}

$negozio = getNegozio($_SESSION['user']);

$success_msg = $error_msg = null;

// Conferma ordine
if (isset($_POST['conferma_ordine'])) {
    if (!empty($_SESSION['carrello'])) {
        $id_prodotti = array_keys($_SESSION['carrello']);
        $quantita = array_values($_SESSION['carrello']);
        if (creaOrdine($negozio['id'], $id_prodotti, $quantita)) {
            $success_msg = "Ordine creato con successo!";
            $_SESSION['carrello'] = []; 
        } else {
            $error_msg = "Errore durante la creazione dell'ordine.";
        }
    } else {
        $error_msg = "Il carrello è vuoto!";
    }
}

// Ottieni prodotti nel carrello
$carrello = $_SESSION['carrello'];
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Riepilogo Carrello - ComicGalaxy</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include 'navbar.php'; ?>

<div class="main">
    <h1>Riepilogo carrello - <?= htmlspecialchars($negozio['nome']) ?></h1>

    <?php if ($success_msg): ?>
        <div class="success"><?= htmlspecialchars($success_msg) ?></div>
    <?php endif; ?>
    <?php if ($error_msg): ?>
        <div class="error"><?= htmlspecialchars($error_msg) ?></div>
    <?php endif; ?>

    <?php if (!empty($carrello)): ?>
    <table class="table">
        <thead>
            <tr>
                <th>Prodotto</th>
                <th>Quantità</th>
                <th>Subtotale</th>
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
                <td><?= htmlspecialchars($prodotto['nome']) ?></td>
                <td><?= $q ?></td>
                <td>€<?= $subtotale ?></td>
            </tr>
            <?php endforeach; ?>
            <tr>
                <td colspan="2"><strong>Totale</strong></td>
                 <td><strong>€<?= number_format($totale, 2) ?></strong></td>
            </tr>
        </tbody>
    </table>

    <form method="post">
        <button type="submit" name="conferma_ordine" class="button">Conferma Ordine</button>
    </form>
    <?php else: ?>
        <p>Il carrello è vuoto.</p>
    <?php endif; ?>

    <div style="margin-top:15px;">
        <a href="gestione_negozio.php" class="button">Torna alla gestione del negozio</a>
    </div>
</div>
</body>
</html>
