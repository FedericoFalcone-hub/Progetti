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

// Aggiungi prodotto al carrello
if (isset($_POST['aggiungi_carrello'])) {
    $id_prodotto = $_POST['id_prodotto'];
    $quantita = intval($_POST['quantita']);
    if ($quantita > 0) {
        if (isset($_SESSION['carrello'][$id_prodotto])) {
            $_SESSION['carrello'][$id_prodotto] += $quantita;
        } else {
            $_SESSION['carrello'][$id_prodotto] = $quantita;
        }
    }
}

// Ottieni prodotti ordinabili dai fornitori
$prodotti_fornitori = getProdottiOrdinabili(); // funzione che restituisce array con 'id_prodotto', 'nome', 'prezzo', 'quantita', 'fornitore'

$success_msg = $error_msg = null;


?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Crea Ordine - ComicGalaxy</title>
    <link href="https://fonts.googleapis.com/css2?family=Comic+Neue:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="main">
    <h1>Crea Ordine - <?= htmlspecialchars($negozio['nome']) ?></h1>

    <?php if ($success_msg) : ?>
        <div class="success"><?= htmlspecialchars($success_msg) ?></div>
    <?php endif; ?>
    <?php if ($error_msg) : ?>
        <div class="error"><?= htmlspecialchars($error_msg) ?></div>
    <?php endif; ?>
    
    <button onclick="window.location.href='visualizza_carrello.php'" class="button">Visualizza Carrello (<?= isset($_SESSION['carrello']) ? array_sum($_SESSION['carrello']) : 0 ?>)</button>
    <form method="POST">
        <table class="table">
            <thead>
                <tr>
                    <th>Prodotto</th>
                    <th>Fornitore</th>
                    <th>Prezzo</th>
                    <th>Quantità disponibile</th>
                    <th>Ordina</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($prodotti_fornitori as $p): ?>
                <tr>
                    <td><?= htmlspecialchars($p['nome_prodotto']) ?></td>
                    <td><?= htmlspecialchars($p['nome_fornitore']) ?></td>
                    <td><?= htmlspecialchars($p['prezzo']) ?></td>
                    <td><?= htmlspecialchars($p['quantita']) ?></td>
                    <td>
                        <form method="post">
                        <input type="hidden" name="id_prodotto" value="<?= $p['id_prodotto'] ?>">
                        <input type="number" name="quantita" min="0" max="<?= $p['quantita'] ?>" value="0" class="qty-input">
                        <button type="submit" name="aggiungi_carrello" class="button">Aggiungi al carrello</button>
                        </form>
                    </td>

                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        
    </form>
</div>
</body>
</html>
