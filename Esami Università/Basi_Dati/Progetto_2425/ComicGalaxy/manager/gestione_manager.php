<?php
session_start();

// Protezione: solo manager o admin loggato
if (!isset($_SESSION['user']) || $_SESSION['ruolo'] !== 'manager') {
    header("Location: login.php");
    exit();
}

include '../lib/functions.php';

$success_msg = $error_msg = null;

// Ottieni tutti gli utenti
$managers = getManager();

if (isset($_POST['sospendi'])) {
    $mail = $_POST['mail'];

    $result = sospendi_utente($mail);
    
    if ($result === true) {
        $_SESSION["success"] = "Utente sospeso con successo.";
    } else {
        if (strpos($result, 'Non è possibile sospendere un manager associato ad un negozio') !== false || strpos($result, 'already exists') !== false) {
            $_SESSION["error"] = "Errore: Non è possibile sospendere un manager associato ad un negozio.";
        } else {
            $_SESSION["error"] = "Errore nella creazione dell'utente: " . htmlspecialchars($result);
        }
    }
    

    header("Location: gestione_manager.php");
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

    header("Location: gestione_manager.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Gestione Manager - ComicGalaxy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<?php include '../navbar.php'; ?>

<div class="container my-5">
    
    <div class="text-center mb-4">
        <h1 class="fw-bold">Gestione Manager</h1>
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

    <div class="d-flex justify-content-center mb-3">
        <button class="btn btn-primary" onclick="location.href='crea_manager.php'">Crea Manager</button>
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
                        <th>Negozio</th>
                        <th>Stato</th>
                        <th style="width: 170px;">Azioni</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($managers as $m): ?>
                    <tr>
                        <td><?= htmlspecialchars($m["nome"]) ?></td>
                        <td><?= htmlspecialchars($m["cognome"]) ?></td>
                        <td><?= htmlspecialchars($m["mail"]) ?></td>
                        <td><?= htmlspecialchars($m["telefono"]) ?></td>
                        <td><?= htmlspecialchars($m["negozio"] ?? '-') ?></td>
                        <td style="text-align:center; vertical-align:middle;">
                            <?php if ($m["sospeso"]=== "t"): ?>
                                <span class="badge bg-danger">Sospeso</span>
                            <?php else: ?>
                                <span class="badge bg-success">Attivo</span>
                            <?php endif; ?>
                        </td>
                        <td >
                            <a href="modifica_utente.php?mail=<?= urlencode($m["mail"]) ?>&return=gestione_manager" class="btn btn-sm btn-warning">Modifica</a>
                            <form action="gestione_manager.php" method="POST" style="display:inline;">
                                <input type="hidden" name="mail" value="<?= htmlspecialchars($m['mail']) ?>">
                                <input type="hidden" name="return" value="gestione_manager">
                                <?php if ($m["sospeso"] === "f"): ?>
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
    

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
