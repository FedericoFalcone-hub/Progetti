<?php
session_start();

include 'lib/functions.php';

$prodotti = getAllProdotti();


?>

<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <title>I nostri prodotti - ComicGalaxy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

    <?php include 'navbar.php'; ?>

    <div class="container my-5">

        <div class="text-center mb-4">
            <h1 class="fw-bold">I nostri prodotti</h1>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-0 table-responsive">
                <table class="table table-striped table-hover table-bordered mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Nome</th>
                            <th>Descrizione</th>
                            <th>Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($prodotti as $p): ?>
                            <tr>
                                <td><?= $p['nome'] ?></td>
                                <td><?= $p['descrizione'] ?></td>
                                <td>
                                    <a href="disponibilita.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-primary text-nowrap" >
                                        Verifica disponibilità
                                    </a>
                                </td>
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

</body>

</html>