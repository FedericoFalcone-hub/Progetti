<?php
session_start();

require_once 'lib/functions.php';

// Controllo parametro ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: negozi.php");
    exit();
}

$id_negozio = $_GET['id'];

// Recupero info negozio
$negozio = getNegozioById($id_negozio);
if (!$negozio) {
    header("Location: negozi.php");
    exit();
}

// Recupero orari
$orari = getOrarioNegozio($id_negozio); // Deve restituire array con ['giorno', 'apertura', 'chiusura']
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Orari - <?= htmlspecialchars($negozio['nome']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="container my-5">

    <div class="text-center mb-4">
        <h1 class="fw-bold">🕒 Orari di <?= htmlspecialchars($negozio['nome']) ?></h1>
        <p class="text-secondary"><?= htmlspecialchars($negozio['indirizzo']) ?></p>
    </div>

    <?php if (empty($orari)): ?>
        <div class="alert alert-warning text-center">
            Non sono stati inseriti orari per questo negozio.
        </div>
    <?php else: ?>
        <div class="card shadow-sm">
            <div class="card-body table-responsive">
                <table class="table table-striped table-bordered mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Giorno</th>
                            <th>Orario Apertura</th>
                            <th>Orario Chiusura</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orari as $o): ?>
                        <tr>
                            <td><?= htmlspecialchars($o['giorno']) ?></td>
                            <td><?php if (is_null($o['ora_apertura'])) { echo "Chiuso"; } else { echo htmlspecialchars($o['ora_apertura']); } ?></td>
                            <td><?php if (is_null($o['ora_chiusura'])) { echo "Chiuso"; } else { echo htmlspecialchars($o['ora_chiusura']); } ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

    <div class="mt-4 text-start">
        <a href="lista_negozi.php" class="btn btn-secondary">Torna ai negozi</a>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
