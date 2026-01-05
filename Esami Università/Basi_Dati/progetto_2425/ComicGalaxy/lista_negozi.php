<?php
session_start();

include 'lib/functions.php';

$negozi = getNegozi();


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

        <div class="card shadow-sm">
            <div class="card-body p-0 table-responsive">
                <table class="table table-striped table-hover table-bordered mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Nome</th>
                            <th>Indirizzo</th>
                            <th>Stato</th>
                            <th>Orari</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($negozi as $n): ?>
                            <tr>
                                <td><?=$n['nome']?></td>
                                <td><?= $n['indirizzo']?></td>
                                <td>
                                    <span class="badge <?= $n['data_chiusura'] ? 'bg-danger' : 'bg-success' ?>">
                                        <?= $n['data_chiusura'] ? 'Chiuso Definitivamente' : 'Aperto' ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="orari_negozio.php?id=<?= $n['id'] ?>" class="btn btn-sm btn-outline-primary">
                                        Visualizza Orari
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