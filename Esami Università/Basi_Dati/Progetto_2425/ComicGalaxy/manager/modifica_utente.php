<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['ruolo'] !== 'manager') {
    header("Location: ../login.php");
    exit();
}

include '../lib/functions.php';

if (!isset($_GET['mail'])) {
    die("Utente non specificato.");
}
    
$mail = $_GET['mail'];
$utente = getUtente($mail);
$return_page = isset($_GET['return']) ? $_GET['return'] : 'gestione_utenze';

// Gestione aggiornamento dati cliente
$success_msg = $error_msg = null;
if (isset($_POST['save'])) {
    $nome = $_POST['nome'];
    $cognome = $_POST['cognome'];   
    $mail = $_POST['mail'];
    $telefono = str_replace(' ', '', $_POST['telefono']);
    $result=aggiornaUtente($utente['mail'], $mail, $nome, $cognome, $telefono);
    if ($result) {
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
        <h1 class="fw-bold">Modifica Utente</h1>
    </div>

    <?php if ($success_msg): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success_msg) ?></div>
    <?php endif; ?>
    <?php if ($error_msg): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error_msg) ?></div>
    <?php endif; ?>

    <form method="POST" class="mx-auto" style="max-width:500px;">
        <div class="mb-3">
            <label class="form-label">Nome</label>
            <input type="text" name="nome" class="form-control" value="<?= htmlspecialchars($utente['nome']) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Cognome</label>
            <input type="text" name="cognome" class="form-control" value="<?= htmlspecialchars($utente['cognome']) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="mail" class="form-control" value="<?= htmlspecialchars($utente['mail']) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Telefono</label>
            <input type="text" name="telefono" class="form-control" value="<?= htmlspecialchars($utente['telefono']) ?>" minlength="7" maxlength="15">
        </div>

        <div class="d-flex justify-content-between">
            <a href="<?= htmlspecialchars($return_page) ?>.php" class="btn btn-secondary">Indietro</a>
            <button type="submit" name="save" class="btn btn-success">Salva Modifiche</button>
            

        </div>
    </form>

</div>


</body>
</html>
