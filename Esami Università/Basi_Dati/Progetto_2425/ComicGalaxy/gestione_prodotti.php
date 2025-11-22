<?php
session_start();

// Protezione: solo manager loggato
if (!isset($_SESSION['user']) || $_SESSION['ruolo'] !== 'manager') {
    header("Location: login.php");
    exit();
}


include 'lib/functions.php';

// Ottieni l'id del negozio associato al manager
$negozio = getNegozio($_SESSION['user']);
if ($negozio === null) {
    die('<div class="main"><div class="login-container"><h2>Errore</h2><div class="error">Non sei associato a nessun negozio.</div></div></div>');
}

// Ottieni prodotti del negozio
$prodotti = getProdotti($negozio['id']);

$success_msg = $error_msg = null;

// Gestione Modifica Prezzo
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

// Gestione Eliminazione Prodotto
if (isset($_POST['elimina'])) {
    $id_prodotto = $_POST['id_prodotto'];

    if (eliminaFornituraProdotto($negozio['id'], $id_prodotto)) {
        $success_msg = "Prodotto eliminato correttamente!";
        $prodotti = getProdotti($negozio['id']); // ricarica prodotti
    } else {
        $error_msg = "Errore durante l'eliminazione del prodotto.";
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Gestione Prodotti - ComicGalaxy</title>
    <link href="https://fonts.googleapis.com/css2?family=Comic+Neue:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="main">
     <a href="gestione_negozio.php" class="button" style="margin-bottom:20px; display:inline-block;">← Torna a Gestione Negozio</a>
    <h1>Gestione Prodotti - <?= htmlspecialchars($negozio['nome']) ?></h1>

    <?php if ($success_msg) : ?>
        <div class="success"><?= htmlspecialchars($success_msg) ?></div>
    <?php endif; ?>
    <?php if ($error_msg) : ?>
        <div class="error"><?= htmlspecialchars($error_msg) ?></div>
    <?php endif; ?>

    <table class="table">
        <thead>
            <tr>
                <th>Nome</th>
                <th>Prezzo</th>
                <th>Quantità</th>
                <th>Azioni</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($prodotti as $p): ?>
            <tr>
                <td><?= htmlspecialchars($p['nome']) ?></td>
                <td>
                    <form method="post" style="display:flex; gap:5px;">
                        <input type="number" step="0.01" name="prezzo" value="<?= htmlspecialchars($p['prezzo']) ?>" required>
                        <input type="hidden" name="id_prodotto" value="<?= $p['id_prodotto'] ?>">
                        <button type="submit" name="modifica" class="button">Modifica</button>
                    </form>
                </td>
                
                <td><?= htmlspecialchars($p['quantita']) ?></td>
                <form method="post">
                    <input type="hidden" name="id_prodotto" value="<?= $p['id_prodotto'] ?>">
                <td><button type="submit" name="elimina" class="button" style="background-color:#e60000;">Elimina</button></td>
                </form>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

</div>

</body>
</html>
