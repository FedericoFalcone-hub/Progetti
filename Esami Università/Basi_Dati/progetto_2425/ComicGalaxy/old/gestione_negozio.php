<?php
session_start();

// Protezione: solo manager loggato
if (!isset($_SESSION["user"]) || $_SESSION["ruolo"] !== "manager") {
    header("Location: ../login.php");
    exit();
}

require_once __DIR__ . "/../lib/functions.php";

$negozio = getNegozio($_SESSION["user"]);
$prodotti = getProdotti($negozio["id"]);
$orari = getOrarioNegozio($negozio["id"]);
$ordini = getOrdiniNegozio($negozio["id"]);
$clienti = tesseratiNegozio($negozio["id"]);
$isClosed = !is_null($negozio["data_chiusura"]);
$fatture = getFattureNegozio($negozio["id"]);

// salvataggio orari
if (isset($_POST["save"])) {
    $giorni = $_POST["giorno"];
    $aperture = $_POST["apertura"];
    $chiusure = $_POST["chiusura"];

    foreach ($giorni as $i => $g) {
        $is_closed = isset($_POST["chiuso_" . $g]);
        $apertura = $is_closed ? null : $aperture[$i];
        $chiusura = $is_closed ? null : $chiusure[$i];
        aggiornaOrario($negozio["id"], $g, $apertura, $chiusura, $is_closed);
    }

    header("Location: gestione_negozio.php?success=1");
    exit();
}

// ritiro ordine
if (isset($_POST["ritira"])) {
    $id_ordine = $_POST["id_ordine"];
    ritiraOrdine($id_ordine);
    header("Location: gestione_negozio.php?ritirato=1");
    exit();
}

// chiusura negozio
if (isset($_POST["chiudi_negozio"])) {
    chiudiNegozioDefinitivamente($negozio["id"]);
    header("Location: gestione_negozio.php");
    exit();
}

if (isset($_POST['sospendi'])) {
    $cf = $_POST['cf'];
    if (sospendi_tessera($cf)) {
        $success_msg = "Tessera sospesa con successo!";
    } else {
        $error_msg = "Errore durante la sospensione dell'utente.";
    }

    $_SESSION["success"] = "Tessera sospesa con successo.";

    header("Location: gestione_negozio.php");
    exit();
}

if (isset($_POST['riattiva'])) {
    $cf = $_POST['cf'];
    if (riattiva_tessera($cf)) {
        $success_msg = "Tessera riattivata con successo!";
    } else {
        $error_msg = "Errore durante la riattivazione dell'utente.";
    }

    $_SESSION["success"] = "Tessera riattivata con successo.";

    header("Location: gestione_negozio.php");
    exit();
}

// se manager non gestisce nessun negozio
if ($negozio === null) {
    echo '<!DOCTYPE html>
    <html lang="it">
    <head>
        <meta charset="UTF-8">
        <title>Errore - ComicGalaxy</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body>
        <div class="container text-center mt-5">
            <div class="card shadow p-4">
                <h2 class="text-danger">Errore</h2>
                <p class="mb-3">Non sei associato a nessun negozio. Contatta l\'amministratore.</p>
                <a href="area_manager.php" class="btn btn-primary">Torna alla Home</a>
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<?php include "../navbar.php"; ?>

<div class="container my-5">

    

    <div class="text-center mb-4">
        <h1 class="fw-bold">Gestione Negozio</h1>
        <h2 class="text-primary"><?= htmlspecialchars($negozio["nome"]) ?></h2>
        <h3 class="text-secondary"><?= htmlspecialchars($negozio["citta"] . ", " . $negozio["via"] . " " . $negozio["civico"]) ?></h3>
    </div>

    <?php if ($isClosed): ?>
        <div class="container text-center mt-5">
        <div class="alert alert-danger text-center">
            <h4>Negozio chiuso definitivamente</h4>
            <p>Questo negozio è stato chiuso in data <strong><?= htmlspecialchars($negozio["data_chiusura"]); ?></strong>.</p>
                
        </div>
    </div>
        <?php endif; ?>
    <!-- Orari di apertura -->
     <?php if (!$isClosed): ?>
    <div class="card mb-4 shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Orari di apertura</h4>
            <form method="POST">
                <button type="submit" name="edit" class="btn btn-primary btn-sm">Modifica orari</button>
            </form>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Giorno</th>
                            <th>Apertura</th>
                            <th>Chiusura</th>
                            <?php if(isset($_POST["edit"])) echo "<th>Chiuso</th>"; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orari as $o): ?>
                        <tr>
                            <td><?= $o["giorno"] ?></td>
                            <?php if ($o["ora_apertura"] === null): ?>
                                <td colspan="<?= isset($_POST['edit']) ? '2' : '2' ?>">Chiuso</td>
                            <?php else: ?>
                                <td><?= $o["ora_apertura"] ?></td>
                                <td><?= $o["ora_chiusura"] ?></td>
                            <?php endif; ?>

                            <?php if(isset($_POST["edit"])): ?>
                                <td class="text-center">
                                    <input type="checkbox" name="chiuso_<?= $o["giorno"] ?>" <?= $o["ora_apertura"] === null ? "checked" : "" ?>>
                                </td>
                            <?php endif; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if (isset($_POST["edit"])): ?>
            <form method="POST" class="mt-2">
                <?php foreach ($orari as $i => $o): ?>
                    <input type="hidden" name="giorno[]" value="<?= $o["giorno"] ?>">
                    <div class="row mb-2 align-items-center">
                        <div class="col-3"><?= $o["giorno"] ?></div>
                        <div class="col-3"><input type="time" name="apertura[]" class="form-control" value="<?= $o["ora_apertura"] ?>"></div>
                        <div class="col-3"><input type="time" name="chiusura[]" class="form-control" value="<?= $o["ora_chiusura"] ?>"></div>
                        <div class="col-3 text-center">
                            <input type="checkbox" name="chiuso_<?= $o["giorno"] ?>" <?= $o["ora_apertura"] === null ? "checked" : "" ?>>
                        </div>
                    </div>
                <?php endforeach; ?>
                <button type="submit" name="save" class="btn btn-success">Salva</button>
            </form>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Prodotti -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Prodotti</h4>
            <?php if (!$isClosed): ?>
            <button class="btn btn-primary btn-sm" onclick="location.href='gestione_prodotti.php'">Gestisci prodotti</button>
            <?php endif; ?>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-striped table-hover table-bordered mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nome</th>
                        <th>Prezzo</th>
                        <th>Quantità</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($prodotti as $p): ?>
                    <tr>
                        <td><?= htmlspecialchars($p["nome"]) ?></td>
                        <td>
                            <?= $p["prezzo"] === null ? "<strong>Non in vendita</strong>" : "€" . htmlspecialchars($p["prezzo"]) ?>
                        </td>
                        <td><?= htmlspecialchars($p["quantita"]) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Ordini -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Ordini</h4>
            <?php if (!$isClosed): ?>
            <button class="btn btn-primary btn-sm" onclick="location.href='crea_ordine.php'">Crea ordine</button>
            <?php endif; ?>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-striped table-hover table-bordered mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID Ordine</th>
                        <th>Fornitore</th>
                        <th>Data ordine</th>
                        <th>Data consegna</th>
                        <th>Totale</th>
                        <th>Stato</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ordini as $o): ?>
                    <tr>
                        <td><a href="dettaglio_ordine.php?id=<?= $o["id"] ?>"><?= $o["id"] ?></a></td>
                        <td><?= htmlspecialchars($o["nome_fornitore"]) ?></td>
                        <td><?= htmlspecialchars($o["data_ordine"]) ?></td>
                        <td><?= htmlspecialchars($o["data_consegna"]) ?></td>
                        <td>€<?= number_format($o["totale"], 2) ?></td>
                        <td>
                            <?= htmlspecialchars($o["stato"]) ?>
                            <?php if ($o["stato"] === "Da ritirare"): ?>
                            <form method="POST" class="d-inline ms-2">
                                <input type="hidden" name="id_ordine" value="<?= $o["id"] ?>">
                                <button type="submit" name="ritira" class="btn btn-sm btn-success">Ritira</button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Clienti -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header">
            <h4 class="mb-0">Clienti Tesserati</h4>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-striped table-hover table-bordered mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nome</th>
                        <th>Cognome</th>
                        <th>Email</th>
                        <th>Rilascio Tessera</th>
                        <th>Scadenza Tessera</th>
                        <th>Punti Tessera</th>
                        <th>Stato Tessera</th>
                        <th>Azioni</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clienti as $c): ?>
                    <tr>
                        <td><?= htmlspecialchars($c["nome"]) ?></td>
                        <td><?= htmlspecialchars($c["cognome"]) ?></td>
                        <td><?= htmlspecialchars($c["mail"]) ?></td>
                        <td><?= htmlspecialchars($c["data_emissione"]) ?></td>
                        <td><?= htmlspecialchars($c["data_scadenza"]) ?></td>
                        <td><?= htmlspecialchars($c["saldo"]) ?></td>
                        <td>
                            <?php if ($c["sospeso"] === "f"): ?>
                                <span class="badge bg-success">Attivo</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Sospeso</span>
                            <?php endif; ?>
                        </td>
                        <td>
                           <form action="gestione_negozio.php" method="POST" style="display:inline;">
                                <input type="hidden" name="cf" value="<?= htmlspecialchars($c['cf']) ?>">
                                <input type="hidden" name="return" value="gestione_negozio">
                                <?php if ($c["sospeso"] === "f"): ?>
                                    <button type="submit" name="sospendi" class="btn btn-sm btn-danger">
                                        Sospendi tessera
                                    </button>
                                <?php else: ?>
                                    <button type="submit" name="riattiva" class="btn btn-sm btn-success">
                                        Riattiva tessera
                                    </button>
                                <?php endif; ?>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Fatture negozio -->
     <div class="card mb-4 shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Fatture Negozio</h4>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-striped table-hover table-bordered mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID Fattura</th>
                        <th>Data Emissione</th>
                        <th>Importo</th>
                        <th>Dettagli</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                   
                    foreach ($fatture as $f): ?>
                    <tr>
                        <td><?= htmlspecialchars($f["id"]) ?></td>
                        <td><?= htmlspecialchars($f["data_emissione"]) ?></td>
                        <td>€<?= number_format($f["importo"], 2) ?></td>
                        <td><a href="dettaglio_fattura.php?id=<?= $f["id"] ?>" class="btn btn-sm btn-info">Visualizza</a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if (!$isClosed): ?>
    <!-- Chiusura negozio -->
    <div class="card mb-4 shadow-sm">
        <div class="card-body text-center">
            <form method="POST" onsubmit="return confirm('Sei sicuro di voler chiudere definitivamente il negozio?');">
                <button type="submit" name="chiudi_negozio" class="btn btn-danger btn-lg">
                    Chiudi definitivamente il negozio
                </button>
            </form>
        </div>
    </div>
    <?php endif; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
