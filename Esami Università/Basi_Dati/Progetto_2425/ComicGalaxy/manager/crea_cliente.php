<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['ruolo'] !== 'manager') {
    header("Location: ../login.php");
    exit();
}

include '../lib/functions.php';

$success_msg = $error_msg = null;

// Gestione creazione nuovo utente
if (isset($_POST['crea'])) {
    $mail = $_POST['mail'];
    $nome = $_POST['nome'];
    $cf = $_POST['codice_fiscale'];
    $cognome = $_POST['cognome'];
    $telefono = str_replace(' ', '', $_POST['telefono']);
    $password = $_POST['password'];

    $result = crea_cliente($mail, $nome, $cognome, $telefono, $password, $cf);

    if ($result === true) {
        $success_msg = "Nuovo cliente creato correttamente!";
    } else {
        if (strpos($result, 'duplicate key') !== false || strpos($result, 'already exists') !== false) {
            $error_msg = "Errore: la mail inserita è già presente nel sistema.";
        } else {
            $error_msg = "Errore nella creazione dell'utente: " . htmlspecialchars($result);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Crea Utente - ComicGalaxy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<?php include '../navbar.php'; ?>

<div class="container my-5">

    <div class="text-center mb-4">
        <h1 class="fw-bold">Crea Nuovo Cliente</h1>
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
            <input type="text" name="nome" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Cognome</label>
            <input type="text" name="cognome" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Codice Fiscale</label>
            <input type="text" name="codice_fiscale" class="form-control" required minlength="16" maxlength="16">
        </div>

        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="mail" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Telefono</label>
            <input type="text" name="telefono" class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>

        <div class="d-flex justify-content-between">
            <a href="gestione_clienti.php" class="btn btn-secondary">Indietro</a>
            <button type="submit" name="crea" class="btn btn-success">Crea Cliente</button>
        </div>
    </form>

</div>

</body>
</html>
