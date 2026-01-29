<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['ruolo'] !== 'cliente') {
    header("Location: ../login.php");
    exit();
}

if ($_SESSION['sospeso'] === 't') {
    header("Location: area_clienti.php");
    exit();
}
require_once '../lib/functions.php';

$carrello = $_SESSION['carrello'] ?? [];

$totale = 0;
$negozio_id = $_GET['negozio'] ?? null;

foreach ($carrello as $item) {
    $totale += $item['prezzo'] * $item['quantita'];
    $negozio_id = $item['negozio'];
}

if (isset($_POST['rimuovi'])) {
    $id_prodotto = $_POST['id_prodotto'];

    if (isset($carrello[$id_prodotto])) {
        unset($carrello[$id_prodotto]);
        $_SESSION['carrello'] = $carrello;
    }
}
$tessera = getTessera($_SESSION['user']);

if (isset($_POST['richiedi_tessera'])) {
    $result = creaTessera($_SESSION['user'], intval($negozio_id));
    if ($result === true) {
        $success_msg = "Tessera punti creata con successo!";
        $tessera = getTessera($_SESSION['user']);
    } else {
        $error_msg = "Errore durante la creazione della tessera: " . $result;
    }
}
if (isset($_POST['applica_sconto'])) {
    $_SESSION['sconto'] = intval($_POST['sconto'] ?? 0);
} else {
    $sconto = 0;
}

if (isset($_POST['esegui_ordine'])) {
    $sconto = $_SESSION['sconto'] ?? 0;

    $totale_scontato = $totale;
    if ($sconto > 0) {
        $totale_scontato = $totale * (1 - ($sconto / 100));
    }
    if (!$tessera || $tessera['sospeso'] === 't' || $tessera['data_scadenza'] < date('Y-m-d') || $tessera['saldo']<100) {
        $sconto = 0;
    }
    $punti_guadagnati = floor($totale_scontato);
    $result = esegui_acquisto($_SESSION['cf'], $carrello, intval($negozio_id), $sconto);

    if ($result === true) {
        if ($tessera && $tessera['data_scadenza'] >= date('Y-m-d') && $tessera['sospeso'] === 'f') {
            $_SESSION['success_msg'] = "Ordine completato con successo! Hai guadagnato $punti_guadagnati punti.";
        } else {
            $_SESSION['success_msg'] = "Ordine completato con successo!";
        }
        
        $_SESSION['carrello'] = [];
    } else {
        $_SESSION['error_msg'] = "Errore nell'esecuzione dell'ordine: " . $result;
    }
    header("Location: acquisto_carrello.php?negozio=" . urlencode($negozio_id));
    exit();
   
}


?>
<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <title>Carrello - ComicGalaxy</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

    <?php include '../navbar.php'; ?>

    <div class="container mt-5">



        <h1 class="fw-bold text-primary text-center">🛒 Il Tuo Carrello</h1>
        <p class="text-center text-secondary">Rivedi i prodotti selezionati e completa l'acquisto.</p>
        <div class="text-start mb-3">
            <a href="acquisto.php?negozio=<?= urlencode($negozio_id) ?>" class="btn btn-secondary">
                ← Torna agli acquisti
            </a>
        </div>
        <?php if (isset($_SESSION['success_msg'])): ?>
            <div class="alert alert-success"><?= $_SESSION['success_msg'] ?></div>
            <?php unset($_SESSION['success_msg']); ?>
        <?php endif; ?>
        <?php if (isset($_SESSION['error_msg'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['error_msg'] ?></div>
            <?php unset($_SESSION['error_msg']); ?>
        <?php endif; ?>

        <?php if (empty($carrello)): ?>
            <div class="alert alert-warning text-center mt-4">
                Il carrello è vuoto.
            </div>
        <?php else: ?>

            <div class="card shadow-sm mt-4">
                <div class="card-header fw-bold">Prodotti nel carrello</div>

                <div class="card-body table-responsive">

                    <table class="table table-bordered table-striped">
                        <thead class="table-light">
                            <tr>
                                <th>Prodotto</th>
                                <th>Prezzo</th>
                                <th>Quantità</th>
                                <th>Subtotale</th>
                                <th>Azioni</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($carrello as $id => $item): ?>
                                <tr>
                                    <td><?= $item['nome'] ?></td>

                                    <td>€<?= number_format($item['prezzo'], 2) ?></td>

                                    <td><?= $item['quantita'] ?></td>

                                    <td>
                                        €<?= number_format($item['prezzo'] * $item['quantita'], 2) ?>
                                    </td>

                                    <td style="width:120px;">
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="id_prodotto" value="<?= $id ?>">
                                            <button type="submit" name="rimuovi" class="btn btn-danger btn-sm">Rimuovi</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>

                    </table>
                </div>
            </div>

            <div class="card shadow-sm mt-4">
                <div class="card-header fw-bold">Sconti disponibili</div>
                <div class="card-body">
                    <?php if (!$tessera): ?>
                        <p class="text-secondary">
                            Non hai ancora una tessera punti.
                        <form method="POST" style="display:inline;">
                            <button type="submit" name="richiedi_tessera" class="btn btn-sm btn-outline-success">
                                Richiedi tessera
                            </button>
                        </form>
                        </p>
                    <?php elseif ($tessera['sospeso'] === 't'): ?>
                        <p class="text-danger">
                            La tua tessera punti è sospesa. Non puoi applicare sconti finché non viene riattivata.
                        </p>
                    <?php elseif ($tessera['data_scadenza'] < date('Y-m-d')): ?>
                        <p class="text-danger">
                            La tua tessera punti è scaduta. Non puoi applicare sconti finché non la rinnovi.
                        </p>
                    <?php else: ?>
                        <p class="text-secondary">
                            <?php if ($tessera['saldo'] >= 100): ?>
                                Hai <strong><?= $tessera['saldo'] ?> punti</strong>. Puoi applicare uno sconto disponibile:
                            <?php else: ?>
                                Hai <strong><?= $tessera['saldo'] ?> punti</strong>. Nessuno sconto applicabile.
                            <?php endif; ?>
                        </p>
                        <?php if ($tessera['saldo']>=100):?>
                        <form method="POST">
                            <div class="mb-3">
                                

                                <select name="sconto" class="form-select" required>
                                    <option value="0" <?= (isset($_POST['sconto']) && $_POST['sconto'] == 0) ? 'selected' : '' ?>>Nessuno sconto</option>

                                    <?php if ($tessera['saldo'] >= 100): ?>
                                        <option value="5" <?= (isset($_POST['sconto']) && $_POST['sconto'] == 5) ? 'selected' : '' ?>>5% di sconto (100 punti)</option>
                                    <?php endif; ?>

                                    <?php if ($tessera['saldo'] >= 200): ?>
                                        <option value="15" <?= (isset($_POST['sconto']) && $_POST['sconto'] == 15) ? 'selected' : '' ?>>15% di sconto (200 punti)</option>
                                    <?php endif; ?>

                                    <?php if ($tessera['saldo'] >= 300): ?>
                                        <option value="30" <?= (isset($_POST['sconto']) && $_POST['sconto'] == 30) ? 'selected' : '' ?>>30% di sconto (300 punti)</option>
                                    <?php endif; ?>
                                    
                                </select>
                            </div>
                            <button type="submit" name="applica_sconto" class="btn btn-primary w-100">
                                Applica Sconto
                            </button>
                            
                        </form>
<?php endif;?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card shadow-sm mt-4">
                <div class="card-body d-flex flex-column align-items-end">
                    <div class="d-flex justify-content-between w-100 mb-1">
                        <h4 class="fw-bold">Totale provvisorio:</h4>
                        <h4>
                            <?php
                            $totale_scontato = $totale;

                            if (isset($_POST['applica_sconto']) && isset($_POST['sconto']) && $_POST['sconto'] !== "0") {
                                $percentuale = floatval($_POST['sconto']);
                                $totale_scontato = $totale * (1 - $percentuale / 100);
                                if ($totale - $totale_scontato >100){
                                    $totale_scontato= $totale -100;
                                }
                                echo '<span class="text-danger text-decoration-line-through">€' . number_format($totale, 2) . '</span> ';
                                echo '<span class="text-success">€' . number_format($totale_scontato, 2) . '</span>';
                            } else {
                                echo '<span class="text-success">€' . number_format($totale, 2) . '</span>';
                            }
                            ?>
                        </h4>
                    </div>
                    <?php if (isset($percentuale) && $percentuale > 0): ?>
                        <small class="text-muted">Sconto applicato: <?= $percentuale ?>%</small>
                    <?php endif; ?>

                </div>
            </div>
            <div class="text-end mt-3">

                <form method="POST">
                    <button class="btn btn-success btn-lg" style="width:260px;" name="esegui_ordine">
                        Completa Acquisto
                    </button>
                </form>
            </div>

        <?php endif; ?>


    </div>

</body>

</html>