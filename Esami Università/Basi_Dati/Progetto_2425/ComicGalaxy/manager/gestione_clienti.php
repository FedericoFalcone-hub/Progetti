<?php
session_start();


if (!isset($_SESSION['user']) || $_SESSION['ruolo'] !== 'manager') {
    header("Location: ../login.php");
    exit();
}

include '../lib/functions.php';

$success_msg = $error_msg = null;

$clienti = getClienti();

if (isset($_POST['sospendi'])) {
    $mail = $_POST['mail'];
    if (sospendi_utente($mail)) {
        $success_msg = "Utente sospeso con successo!";
    } else {
        $error_msg = "Errore durante la sospensione dell'utente.";
    }

    $_SESSION["success"] = "Utente sospeso con successo.";

    header("Location: gestione_clienti.php");
    exit();
}

if (isset($_POST['riattiva'])) {
    $mail = $_POST['mail'];
    if (riattiva_utente($mail)) {
        $success_msg = "Utente riattivato con successo!";
    } else {
        $error_msg = "Errore durante la riattivazione dell'utente.";
    }

    $_SESSION["success"] = "Utente riattivato con successo.";

    header("Location: gestione_clienti.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Gestione Clienti - ComicGalaxy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<?php include '../navbar.php'; ?>

<div class="container my-5">
    
    <div class="text-center mb-4">
        <h1 class="fw-bold">Gestione Clienti</h1>
    </div>

    <?php if (isset($_SESSION["success"])): ?>
        <div class="alert alert-success">
            <?= $_SESSION["success"] ?>
        </div>
        <?php unset($_SESSION["success"]); ?>
    <?php endif; ?>

    <div class="d-flex justify-content-center mb-3">
        <button class="btn btn-primary" onclick="location.href='crea_cliente.php'">Crea Cliente</button>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0 table-responsive">
            <table class="table table-striped table-hover table-bordered mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nome</th>
                        <th>Cognome</th>
                        <th>Email</th>
                        <th>Telefono</th>
                        <th>Stato</th>
                        <th style="width: 170px;">Azioni</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clienti as $c): ?>
                    <tr>
                        <td><?= htmlspecialchars($c["nome"]) ?></td>
                        <td><?= htmlspecialchars($c["cognome"]) ?></td>
                        <td><?= htmlspecialchars($c["mail"]) ?></td>
                        <td><?= htmlspecialchars($c["telefono"]) ?></td>
                        <td style="text-align:center; vertical-align:middle;">
                            <?php if ($c["sospeso"]=== "t"): ?>
                                <span class="badge bg-danger">Sospeso</span>
                            <?php else: ?>
                                <span class="badge bg-success">Attivo</span>
                            <?php endif; ?>
                        </td>
                        <td >
                            <a href="modifica_utente.php?mail=<?= urlencode($c["mail"]) ?>&return=gestione_clienti" class="btn btn-sm btn-warning">Modifica</a>
                            <form action="gestione_clienti.php" method="POST" style="display:inline;">
                                <input type="hidden" name="mail" value="<?= htmlspecialchars($c['mail']) ?>">
                                <input type="hidden" name="return" value="gestione_clienti">
                                <?php if ($c["sospeso"] === "f"): ?>
                                    <button type="submit" name="sospendi" class="btn btn-sm btn-danger">
                                        Sospendi
                                    </button>
                                <?php else: ?>
                                    <button type="submit" name="riattiva" class="btn btn-sm btn-success">
                                        Riattiva
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
    
                                     <div class="mt-4">
        <a href="area_manager.php" class="btn btn-secondary">Torna all'area riservata</a>
    </div>

</div>

</body>
</html>
