<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['ruolo'] !== 'manager') {
    header("Location: ../login.php");
    exit();
}
if ($_SESSION['sospeso'] === 't') {
    header("Location: area_manager.php");
    exit();
}
include '../lib/functions.php';

$success_msg = $error_msg = null;

$negozi = getNegozi();
$manager = getManagerLiberi();

if (isset($_POST['crea_negozio'])) {
    $nome = $_POST['nome'];
    $citta = $_POST['citta'];
    $via = $_POST['via'];
    $civico = intval($_POST['civico']);
    $manager = $_POST['manager'];
    $telefono = $_POST['telefono'];

    $result = crea_negozio($nome, $manager, $citta, $via, $civico, $telefono);
    if ($result === true) {
        $_SESSION["success"] = "Negozio creato con successo.";
    } else {
        if (strpos($result, '23505') !== false || strpos($result, 'indirizzo_univoco') !== false) {
            $_SESSION["error"] = "Errore: un negozio è già presente in questo indirizzo";
        } if (strpos($result, 'telefono')){
            $_SESSION["error"] = "Errore: il numero di telefono inserito non è valido (deve contenere il prefisso).";
        } 
        else {
            $_SESSION["error"] = "Errore nella creazione del negozio: " . $result;
        }
    }
    $negozi = getNegozi();

    header("Location: gestione_negozi.php");
    exit();
}

if (isset($_POST['assegna_manager'])) {
    $id_negozio = $_POST['id_negozio'];
    $manager = $_POST['manager'] ?: null;
    $result = aggiorna_manager($id_negozio, $manager);
    if ($result) {
        $_SESSION["success"] = "Manager aggiornato con successo.";
    } else {
        $_SESSION["error"] = "Errore durante l'aggiornamento del manager.";
    }


    header("Location: gestione_negozi.php");
    exit();
}


?>

<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <title>Gestione Negozi - ComicGalaxy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

    <?php include '../navbar.php'; ?>

    <div class="container my-5">

        <div class="text-center mb-4">
            <h1 class="fw-bold">Gestione Negozi 🏪</h1>
        </div>

        <?php if (isset($_SESSION["success"])): ?>
            <div class="alert alert-success">
                <?= $_SESSION["success"] ?>
            </div>
            <?php unset($_SESSION["success"]); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION["error"])): ?>
            <div class="alert alert-danger">
                <?= $_SESSION["error"] ?>
            </div>
            <?php unset($_SESSION["error"]); ?>
        <?php endif; ?>

        <div class="card mb-4 shadow-sm">
            <div class="card-header">Crea Nuovo Negozio</div>
            <div class="card-body">
                <form method="POST" class="d-flex flex-nowrap align-items-end gap-2">
                    <div class="col-auto">
                        <label class="form-label">Nome Negozio</label>
                        <input type="text" name="nome" class="form-control" required style="min-width:120px;" required>
                    </div>
                    <div class="col-auto">
                        <label class="form-label">Città</label>
                        <input type="text" name="citta" class="form-control" required style="min-width:100px;" required>
                    </div>
                    <div class="col-auto">
                        <label class="form-label">Via</label>
                        <input type="text" name="via" class="form-control" required style="min-width:120px;" required>
                    </div>
                    <div class="col-auto">
                        <label class="form-label">Civico</label>
                        <input type="text" name="civico" class="form-control" style="max-width:80px;" required>
                    </div>
                    <div class="col-auto">
                        <label class="form-label">Telefono</label>
                        <input type="text" name="telefono" class="form-control" required style="min-width:120px;" required>
                    </div>
                    <div class="col-auto">
                        <label class="form-label">Manager</label>
                        <select name="manager" class="form-select" style="min-width:150px;" required>
                            <option value="">Seleziona</option>
                            <?php foreach ($manager as $m): ?>
                                <option value="<?=$m['mail']?>">
                                    <?=$m['nome'] . ' ' . $m['cognome']?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-auto">
                        <button type="submit" name="crea_negozio" class="btn btn-success" style="height:38px;">Crea</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-0 table-responsive">
                <table class="table table-striped table-hover table-bordered mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Nome</th>
                            <th>Indirizzo</th>
                            <th>Telefono</th>
                            <th>Manager Attuale</th>
                            <th>Stato</th>
                            <th style="width: 220px;">Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($negozi as $n): ?>
                            <tr>
                                <td><?= $n['nome'] ?></td>
                                <td><?= $n['indirizzo'] ?></td>
                                <td><?= $n['telefono'] ?></td>
                                <td><?= $n['manager'] ?? '-' ?></td>
                                <td>
                                    <?php if ($n['data_chiusura']): ?>
                                        <span class="badge bg-danger">Chiuso</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Aperto</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <form method="POST" class="d-flex gap-2 align-items-center">
                                        <input type="hidden" name="id_negozio" value="<?= $n['id'] ?>">


                                        <?php if (!$n['data_chiusura']): ?>
                                            <select name="manager" class="form-select form-select-sm" style="min-width: 200px;" required>
                                                <option value="">Nessuno</option>
                                                <?php foreach ($manager as $m): ?>
                                                    <option value="<?= $m['mail'] ?>"
                                                        <?= $m['mail'] === $n['manager'] ? 'selected' : '' ?>>
                                                        <?= $m['nome'] . ' ' . $m['cognome'] ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <button type="submit" name="assegna_manager" class="btn btn-sm btn-primary">Assegna</button>
                                            <a href="modifica_negozio.php?id=<?= urlencode($n["id"]) ?>&return=gestione_negozi" class="btn btn-sm btn-warning">Modifica</a>
                                        <?php else: ?>
                                            <span class="text-muted">Non disponibile</span>
                                        <?php endif; ?>
                                    </form>
                                </td>

                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4">
            <a href="area_manager.php" class="btn btn-secondary">Torna all'area riservata</a>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>