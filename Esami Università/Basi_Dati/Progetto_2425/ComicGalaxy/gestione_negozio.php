<?php
session_start();

// Protezione: solo manager loggato
if (!isset($_SESSION['user']) || $_SESSION['ruolo'] !== 'manager') {
    header("Location: login.php");
    exit();
}

include 'lib/functions.php';

$negozio=getNegozio($_SESSION['user']);
$prodotti=getProdotti($negozio['id']);
$orari=getOrarioNegozio($negozio['id']);
$ordini=getOrdiniNegozio($negozio['id']);

// salvataggio orari
if (isset($_POST['save'])) {

    $giorni = $_POST['giorno'];
    $aperture = $_POST['apertura'];
    $chiusure = $_POST['chiusura'];

    foreach ($giorni as $i => $g) {

        $is_closed = isset($_POST['chiuso_'.$g]);

        $apertura = $is_closed ? null : $aperture[$i];
        $chiusura = $is_closed ? null : $chiusure[$i];

        aggiornaOrario($negozio['id'], $g, $apertura, $chiusura, $is_closed);
    }

    header("Location: gestione_negozio.php?success=1");
    exit;
}

//ritiro ordine
if (isset($_POST['ritira'])) {
    $id_ordine = $_POST['id_ordine'];
    ritiraOrdine($id_ordine);
    header("Location: gestione_negozio.php?ritirato=1");
    exit;
}

// se manager non gestisce nessun negozio
if ($negozio === null) {
    echo '<!DOCTYPE html>
    <html lang="it">
    <head>
        <meta charset="UTF-8">
        <title>Errore - ComicGalaxy</title>
        <link href="https://fonts.googleapis.com/css2?family=Comic+Neue:wght@400;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="css/style.css">
    </head>
    <body>
        <div class="main" style="text-align:center; padding:50px;">
            <div class="login-container" style="max-width:500px; margin:auto;">
                <h2>Errore </h2>
                <div class="error">Non sei associato a nessun negozio. Contatta l\'amministratore.</div>
                <div class="back-link">
                    <a href="index.php">Torna alla Home</a>
                </div>
            </div>
        </div>
    </body>
    </html>';
    exit();
}


?>


<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Gestione Negozio - ComicGalaxy</title>
    <link href="https://fonts.googleapis.com/css2?family=Comic+Neue:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="main">
    
    <h1>Gestione Negozio </h1>
    <h2><?= htmlspecialchars($negozio['nome']) ?></h2>


    <!-- Orari di apertura -->
    <div class="management-section">
        <h2>Orari di apertura</h2>
    
        <form method="POST">
            <button type="submit" name="edit" class="button">Modifica orari</button>
        </form>

        <table class="table">
            <tr>
                <th>Giorno</th>
                <th>Apertura</th>
                <th>Chiusura</th>
            </tr>

            <?php foreach ($orari as $o): ?>
            <tr>
                <td><?= $o['giorno'] ?></td>

                <?php if ($o['ora_apertura'] === null): ?>
                    <td colspan="2">Chiuso</td>

                <?php else: ?>
                    <td><?= $o['ora_apertura'] ?></td>
                    <td><?= $o['ora_chiusura'] ?></td>
                <?php endif; ?>
            </tr>
            <?php endforeach; ?>
        </table>

        <!-- Modifica orari -->
        <?php if (isset($_POST['edit'])): ?>

        <form method="POST">
        <table class="table">
            <tr>
                <th>Giorno</th>
                <th>Apertura</th>
                <th>Chiusura</th>
                <th>Chiuso</th>
            </tr>

        <?php foreach ($orari as $o): ?>
            <tr>
                <td>
                    <?= $o['giorno'] ?>
                    <input type="hidden" name="giorno[]" value="<?= $o['giorno'] ?>">
                </td>

                <td>
                    <input type="time" name="apertura[]" value="<?= $o['ora_apertura'] ?>">
                </td>

                <td>
                    <input type="time" name="chiusura[]" value="<?= $o['ora_chiusura'] ?>">
                </td>

                <td>
                    <input type="checkbox" name="chiuso_<?= $o['giorno'] ?>" 
                    <?= $o['ora_apertura'] === null ? 'checked' : '' ?>>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>

        <button type="submit" name="save" class="button">Salva</button>
        </form>

        <?php endif; ?>


    </div>


    <!-- Prodotti -->
    <div class="management-section">
        <h2>Prodotti</h2>
        <button class="button" onclick="location.href='gestione_prodotti.php'">Gestisci prodotti</button>
        <div class="table-scroll">
            <table class="table">
                <tr>
                    <th>Nome</th>
                    <th>Prezzo</th>
                    <th>Quantità</th>
                </tr>
                
            
                <?php foreach($prodotti as $p): ?>
                <tr>
                    <td><?= htmlspecialchars($p['nome']) ?></td>
                    <td>€<?= htmlspecialchars($p['prezzo']) ?></td>
                    <td><?= htmlspecialchars($p['quantita']) ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
        
    </div>

    <!-- Ordini -->
    <div class="management-section">
        <h2>Ordini</h2>
        <button class="button" onclick="location.href='crea_ordine.php'">Crea ordine</button>
        <div class="table-scroll">
        <table class="table">
                <tr>
                    <th>ID Ordine</th>
                    <th>Data ordine</th>
                    <th>Data consegna</th>
                    <th>Totale</th>
                    <th>Stato</th>
                </tr>
            <tbody>
                <?php foreach($ordini as $o): ?>
                <tr>
                    <td>
                        <a href="dettaglio_ordine.php?id=<?= $o['id'] ?>" class="order-link">
                            <?= $o['id'] ?>
                        </a>
                    </td>

                    <td><?= htmlspecialchars($o['data_ordine']) ?></td>
                    <td><?= htmlspecialchars($o['data_consegna']) ?></td>
                    <td>€<?= number_format($o['totale'], 2) ?></td>
                    <td>
                        <span><?= htmlspecialchars($o['stato']) ?></span>
                        <?php if ($o['stato'] === 'Da ritirare' ): ?>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="id_ordine" value="<?= $o['id'] ?>">
                            <button type="submit" name="ritira" class="button">Ritira</button>
                        </form>
                    <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
                </div>
    </div>

    <div class="management-section">
        <h2>Clienti</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>ID Cliente</th>
                    <th>Nome</th>
                    <th>Email</th>
                    <th>Punti Tessera</th>
                </tr>
            </thead>
            <tbody>
                <!-- Dati clienti da database -->
            </tbody>
        </table>
    </div>
    

    
</div>

</body>
</html>
