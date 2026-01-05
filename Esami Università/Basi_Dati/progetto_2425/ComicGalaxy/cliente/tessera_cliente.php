<?php
session_start();

// Accesso consentito solo ai clienti
if (!isset($_SESSION['user']) || $_SESSION['ruolo'] !== 'cliente') {
    header("Location: ../login.php");
    exit();
}
if ($_SESSION['sospeso'] === 't') {
    header("Location: area_clienti.php");
    exit();
}
require_once '../lib/functions.php';

// Recupero la tessera del cliente loggato
$tessera = getTessera($_SESSION['user']);

if (isset($_POST['rinnova'])) {
    var_dump($tessera);
    $cf_cliente = $_POST['cf_cliente'];
    $result = rinnova_tessera($cf_cliente);

    if ($result === true) {
        $_SESSION["success"] = "Tessera rinnovata con successo.";
    } else {
        $_SESSION["error"] = "Errore durante il rinnovo della tessera: " . $result;
    }

    header("Location: tessera_cliente.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <title>Tessera Punti - ComicGalaxy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

    <?php include '../navbar.php'; ?>

    <div class="container mt-5">



        <h1 class="fw-bold text-primary text-center mt-3">La Mia Tessera</h1>
        <p class="text-center text-secondary">
            Qui puoi visualizzare tutte le informazioni relative alla tua tessera.
        </p>
        <?php if (isset($_SESSION["success"])): ?>
            <div class="alert alert-success"><?= $_SESSION["success"] ?></div>
            <?php unset($_SESSION["success"]); ?>
        <?php endif; ?>
        <?php if (isset($_SESSION["error"])): ?>
            <div class="alert alert-danger"><?= $_SESSION["error"] ?></div>
            <?php unset($_SESSION["error"]); ?>
        <?php endif; ?>
        <div class="mt-3 text-start">
            <a href="area_clienti.php" class="btn btn-secondary">Torna all'area riservata</a>
        </div>

        <?php if (!$tessera): ?>
            <div class="alert alert-warning text-center mt-4">
                Non risulta alcuna tessera associata al tuo account.
            </div>
            <div class="text-center mt-3">
                <a href="acquisto.php" class="btn btn-primary">Vai agli acquisti</a>
            </div>

        <?php else: ?>

            <?php
            $oggi = date('Y-m-d');
            if ($tessera['data_scadenza'] < $oggi) {
                $stato = "Scaduta";
                $badge = "warning";
            } elseif ($tessera['sospeso'] === 't') {
                $stato = "Sospesa";
                $badge = "danger";
            } else {
                $stato = "Attiva";
                $badge = "success";
            }
            ?>

            <div class="card shadow-sm mt-4">
                <div class="card-header fw-bold">Dettagli Tessera</div>
                <div class="card-body">

                    <p><strong>Codice Tessera:</strong> <?= $tessera['id'] ?></p>
                    <p><strong>Negozio Emissione:</strong> <?= $tessera['nome_negozio'] ?></p>
                    <p><strong>Punti Attuali:</strong> <?= $tessera['saldo'] ?></p>
                    <p><strong>Data Creazione:</strong> <?= $tessera['data_emissione'] ?></p>
                    <p><strong>Data Scadenza:</strong> <?= $tessera['data_scadenza'] ?></p>
                    <p>
                        <strong>Stato:</strong>
                        <span class="badge bg-<?= $badge ?>"><?= $stato ?></span>
                    </p>

                    <?php if ($stato === "Scaduta"): ?>
                        <form method="post" class="mt-3">
                            <input type="hidden" name="cf_cliente" value="<?= $tessera['cf'] ?>">
                            <button type="submit" class="btn btn-success" name="rinnova">
                                Rinnova Tessera
                            </button>
                        </form>
                    <?php endif; ?>

                    <?php if (isset($tessera['negozio'])): ?>
                        <p class="mt-3"><strong>Negozio di Emissione:</strong> <?= $tessera['negozio'] ?></p>
                    <?php endif; ?>

                </div>
            </div>


        <?php endif; ?>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>