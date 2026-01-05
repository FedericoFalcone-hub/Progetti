<?php
session_start();

if (!isset($_SESSION["user"]) || $_SESSION["ruolo"] !== "manager") {
    header("Location: ../login.php");
    exit();
}
if ($_SESSION['sospeso'] === 't') {
    header("Location: area_manager.php");
    exit();
}
require_once __DIR__ . "/../lib/functions.php";

$negozio = getNegozio($_SESSION["user"]);
$prodotti = getProdotti($negozio["id"]);
$orari = getOrarioNegozio($negozio["id"]);
$ordini = getOrdiniNegozio($negozio["id"]);

$isClosed = !is_null($negozio["data_chiusura"]);
$fatture = getFattureNegozio($negozio["id"]);

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

if (isset($_POST["ritira"])) {
    $id_ordine = $_POST["id_ordine"];
    ritiraOrdine($id_ordine);
    header("Location: gestione_negozio.php?ritirato=1");
    exit();
}

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
<style>
    .cliccabile-card {
        cursor: pointer;
        transition: transform 0.1s, box-shadow 0.2s;
    }

    .cliccabile-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }
</style>

<body>

    <?php include "../navbar.php"; ?>

    <div class="container my-5">



        <div class="text-center mb-4">
            <h1 class="fw-bold">Gestione Negozio</h1>
            <h2 class="text-primary"><?= $negozio["nome"] ?></h2>
            <h3 class="text-secondary"><?= $negozio["citta"] . ", " . $negozio["via"] . " " . $negozio["civico"] ?></h3>
        </div>

        <?php if ($isClosed): ?>
            <div class="container text-center mt-5">
                <div class="alert alert-danger text-center">
                    <h4>Negozio chiuso definitivamente</h4>
                    <p>Questo negozio è stato chiuso in data <strong><?= $negozio["data_chiusura"]; ?></strong>.</p>

                </div>
            </div>
        <?php endif; ?>

        <?php if (!$isClosed): ?>
            <div class="card mb-4 shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Orari di apertura</h4>
                    <?php if (empty($orari)): ?>

                        <a href="inserimento_orari.php?id=<?= $negozio['id'] ?>" class="btn btn-primary btn-sm">
                            Inserisci Orari
                        </a>
                    <?php else: ?>
                        <form method="POST">
                            <button type="submit" name="edit" class="btn btn-primary btn-sm">Modifica orari</button>
                        </form>
                    <?php endif; ?>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Giorno</th>
                                    <th>Apertura</th>
                                    <th>Chiusura</th>
                                    <?php if (isset($_POST["edit"])) echo "<th>Chiuso</th>"; ?>
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

                                        <?php if (isset($_POST["edit"])): ?>
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
    </div>


    <div class="container text-center mt-5">

        <div class="row row-cols-1 row-cols-md-3 g-4 mt-4">

            <div class="col">
                <a href="prodotti_negozio.php" class="text-decoration-none">
                    <div class="card shadow h-100">
                        <div class="card-body text-center">
                            <div class="display-4">📚</div>
                            <h5 class="mt-3">Prodotti in magazzino</h5>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col">
                <a href="ordini_negozio.php" class="text-decoration-none">
                    <div class="card shadow h-100">
                        <div class="card-body text-center">
                            <div class="display-4">📦</div>
                            <h5 class="mt-3">Ordini</h5>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col">
                <a href="tesserati_negozio.php" class="text-decoration-none">
                    <div class="card shadow h-100">
                        <div class="card-body text-center">
                            <div class="display-4">🪪</div>
                            <h5 class="mt-3">Clienti tesserati</h5>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col">
                <a href="fatture_negozio.php" class="text-decoration-none">
                    <div class="card shadow h-100">
                        <div class="card-body text-center">
                            <div class="display-4">🧾</div>
                            <h5 class="mt-3">Fatture emesse</h5>
                        </div>
                    </div>
                </a>
            </div>
            <?php if (!$isClosed): ?>
            <div class="col">
                <form method="POST" id="chiudiForm"></form>

                <div class="card shadow h-100 cliccabile-card"
                    onclick="confermaChiusura()">
                    <div class="card-body text-center">
                        <div class="display-4">🔒</div>
                        <h5 class="mt-3">Chiudi Negozio Definitivamente</h5>
                    </div>
                </div>
            </div>

            <script>
                function confermaChiusura() {
                    if (confirm("Sei sicuro di voler chiudere DEFINITIVAMENTE il negozio?\nL'azione è IRREVERSIBILE.")) {
                        // recupero il form
                        const form = document.getElementById("chiudiForm");

                        // creo l'input nascosto come se fosse il bottone submit
                        let hidden = document.createElement("input");
                        hidden.type = "hidden";
                        hidden.name = "chiudi_negozio";
                        hidden.value = "1"; // o qualsiasi valore tu controlli in PHP
                        form.appendChild(hidden);

                        // invio il form al server
                        form.submit();
                    }
                }
            </script>
            <?php endif; ?>
        </div>

        <div class="mt-4">
            <a href="area_manager.php" class="btn btn-secondary">Torna all’Area Manager</a>
        </div>

    </div>

</body>

</html>