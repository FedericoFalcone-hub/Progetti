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

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: gestione_negozio.php");
    exit();
}

$id_negozio = $_GET['id'];
$negozio = getNegozioById($id_negozio);

if (!$negozio) {
    header("Location: gestione_negozio.php");
    exit();
}

$giorni = ["Lunedì", "Martedì", "Mercoledì", "Giovedì", "Venerdì", "Sabato", "Domenica"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $orari = [];

    foreach ($giorni as $g) {
        $apertura = $_POST["apertura_$g"] ?? null;
        $chiusura = $_POST["chiusura_$g"] ?? null;

        if ($apertura === '') $apertura = null;
        if ($chiusura === '') $chiusura = null;

        if ($apertura && $chiusura && $apertura >= $chiusura) {
            $error_msg = "L'orario di chiusura deve essere successivo all'apertura per $g.";
            break;
        }

        $orari[] = [
            "giorno" => $g,
            "apertura" => $apertura,
            "chiusura" => $chiusura
        ];
    }

    if (!isset($error_msg)) {
        var_dump($orari);
        $result = salvaOrariNegozio($id_negozio, $orari);
        if ($result === true) {
            $_SESSION["success"] = "Orari salvati con successo!";
            header("Location: gestione_negozio.php");
            exit();
        } else {
            $error_msg = "Errore durante il salvataggio degli orari: " . htmlspecialchars($result);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <title>Inserisci Orari - <?= htmlspecialchars($negozio['nome']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

    <?php include "../navbar.php"; ?>

    <div class="container my-5">

        <h2 class="text-center text-primary mb-4">🕒 Inserisci Orari - <?= htmlspecialchars($negozio['nome']) ?></h2>

        <?php if (isset($error_msg)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error_msg) ?></div>
        <?php endif; ?>

        <form method="post">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <?php foreach ($giorni as $g): ?>
                        <div class="row mb-3 align-items-center">
                            <div class="col-md-2 fw-bold"><?= $g ?></div>
                            <div class="col-md-5">
                                <input type="time" name="apertura_<?= $g ?>" class="form-control">
                            </div>
                            <div class="col-md-5">
                                <input type="time" name="chiusura_<?= $g ?>" class="form-control">
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="text-center">
                <button type="submit" class="btn btn-primary">Salva Orari</button>
                <a href="gestione_negozio.php" class="btn btn-secondary">Annulla</a>
            </div>
        </form>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>