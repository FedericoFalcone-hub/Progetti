<?php
session_start();

// Protezione manager
if (!isset($_SESSION['user']) || $_SESSION['ruolo'] !== 'manager') {
    header("Location: login.php");
    exit();
}

include 'lib/functions.php';

// Controllo parametro
if (!isset($_GET['id'])) {
    die("ID ordine mancante.");
}

$id_ordine = intval($_GET['id']);

// Ottieni dettagli ordine
$ordine = getDettaglioOrdine($id_ordine);
if (!$ordine) {
    die("Ordine non trovato.");
}

// Ottieni elenco prodotti nell'ordine
$prodotti = getProdottiOrdine($id_ordine);

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Dettaglio Ordine #<?= $id_ordine ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="main">
    <a href="gestione_ordini.php" class="button" style="margin-bottom:15px;">⬅ Torna agli ordini</a>

    <h1>Dettaglio Ordine #<?= $id_ordine ?></h1>

    <h3>Informazioni ordine</h3>
    <p><strong>Fornitore:</strong> <?= $ordine['nome_fornitore'] ?></p>
    <p><strong>Data ordine:</strong> <?= $ordine['data_ordine'] ?></p>
    <p><strong>Data consegna:</strong> <?= $ordine['data_consegna'] ?></p>
    <p><strong>Totale:</strong> €<?= $ordine['totale'] ?></p>
    <p><strong>Stato:</strong> <?= $ordine['stato'] ?></p>

    <h3>Prodotti ordinati</h3>

    <table class="table">
        <thead>
            <tr>
                <th>Prodotto</th>
                <th>Quantità</th>
                <th>Prezzo unitario</th>
                <th>Totale</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($prodotti as $p): ?>
            <tr>
                <td><?= $p['nome_prodotto'] ?></td>
                <td><?= $p['quantita'] ?></td>
                <td>€<?= $p['prezzo'] ?></td>
                <td>€<?= $p['totale'] ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

</div>

</body>
</html>
