<?php
session_start();

include 'lib/functions.php';

$negozi = getNegozi_aperti();


?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>I nostri negozi - ComicGalaxy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="container my-5">

    <div class="text-center mb-4">
        <h1 class="fw-bold">I nostri negozi</h1>
    </div>

    <!-- TABELLA NEGOZI -->
    <div class="card shadow-sm">
        <div class="card-body p-0 table-responsive">
            <table class="table table-striped table-hover table-bordered mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nome</th>
                        <th>Indirizzo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($negozi as $n): ?>
                    <tr>
                        <td><?= htmlspecialchars($n['nome']) ?></td>
                        <td><?= htmlspecialchars($n['indirizzo']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        <a href="index.php" class="btn btn-secondary">Torna alla home</a>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
