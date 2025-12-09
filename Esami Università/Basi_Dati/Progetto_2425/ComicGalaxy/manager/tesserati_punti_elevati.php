<?php
session_start();


if (!isset($_SESSION['user']) || $_SESSION['ruolo'] !== 'manager') {
     header("Location: login.php");
     exit();
}

require_once "../lib/functions.php";


$tesserati = getTesseratiPuntiElevati();

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Tesserati punti elevati</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

</head>
<body>

<?php include '../navbar.php'; ?>

<div class="container mt-5">

    <h1 class="text-primary fw-bold mb-4 text-center">Tesserati punti elevati</h1>


        <div class="card shadow-sm">
            <div class="card-body p-0 table-responsive">
                <table class="table table-striped table-hover table-bordered mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Nome</th>
                            <th>Cognome</th>
                            <th>Mail</th>
                            <th>Negozio</th>
                            <th>Saldo punti</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($tesserati)): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted">
                                    Nessun tesserato con punti elevati trovato.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($tesserati as $t): ?>
                                <tr>
                                    <td><?= htmlspecialchars($t['nome_cliente']) ?></td>
                                    <td><?= htmlspecialchars($t['cognome_cliente']) ?></td>
                                     <td><?= htmlspecialchars($t['mail']) ?></td>
                                    <td><?= htmlspecialchars($t['nome_negozio']) ?></td>
                                    <td><?= htmlspecialchars($t['punti']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    <div class="mt-4 text-center">
        <a href="statistiche.php" class="btn btn-secondary">Torna alle Statistiche</a>
    </div>

</div>

</body>
</html>
