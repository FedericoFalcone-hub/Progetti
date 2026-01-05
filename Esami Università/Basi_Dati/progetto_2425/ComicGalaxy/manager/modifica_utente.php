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

if (!isset($_GET['mail'])) {
    die("Utente non specificato.");
}

$mail = $_GET['mail'];
$utente = getUtente($mail);
$return_page = isset($_GET['return']) ? $_GET['return'] : 'gestione_utenze';

$success_msg = $error_msg = null;
if (isset($_POST['save'])) {
    $nome = $_POST['nome'];
    $cognome = $_POST['cognome'];
    $mail = $_POST['mail'];
    $telefono = str_replace(' ', '', $_POST['telefono']);
    $cf = $_POST['cf'] ?? null;
    $result = aggiornaUtente($cf,$utente['mail'], $mail, $nome, $cognome, $telefono);
    if ($result===true) {
        $success_msg = "Utente aggiornato correttamente!";
        $utente = getUtente($mail);
    } else {
        $error_msg = $result;
    }
}
?>

<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <title>Modifica Utente - ComicGalaxy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

    <?php include '../navbar.php'; ?>

    <div class="container my-5">

        <div class="text-center mb-4">
            <?php if ($utente['ruolo'] === 'cliente'): ?>
                <?php $cf=getCFByMail($utente['mail']); ?>
            <h1 class="fw-bold">Modifica Cliente</h1>
            <?php elseif ($utente['ruolo'] === 'manager'): ?>
            <h1 class="fw-bold">Modifica Manager</h1>
            <?php else: ?>
            <h1 class="fw-bold">Modifica Utente</h1>
            <?php endif; ?>
        </div>

        <?php if ($success_msg): ?>
            <div class="alert alert-success"><?= $success_msg ?></div>
        <?php endif; ?>
        <?php if ($error_msg): ?>
            <div class="alert alert-danger"><?= $error_msg ?></div>
        <?php endif; ?>

        <form method="POST" class="mx-auto" style="max-width:500px;">
            <div class="mb-3">
                <label class="form-label">Nome</label>
                <input type="text" name="nome" class="form-control" value="<?= $utente['nome'] ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Cognome</label>
                <input type="text" name="cognome" class="form-control" value="<?= $utente['cognome'] ?>" required>
            </div>

            <?php if ($utente['ruolo'] === 'cliente'): ?>
            <div class="mb-3">
                <label class="form-label">Codice Fiscale</label>
                <input type="text" name="cf" class="form-control" value="<?= $cf ?>">
            </div>
            <?php endif; ?>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="mail" class="form-control" value="<?= $utente['mail'] ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Telefono</label>
                <input type="text" name="telefono" class="form-control" value="<?= $utente['telefono'] ?>" minlength="7" maxlength="15">
            </div>

            <div class="d-flex justify-content-between">
                <a href="<?= $return_page ?>.php" class="btn btn-secondary">Indietro</a>
                <button type="submit" name="save" class="btn btn-success">Salva Modifiche</button>


            </div>
        </form>

    </div>


</body>

</html>