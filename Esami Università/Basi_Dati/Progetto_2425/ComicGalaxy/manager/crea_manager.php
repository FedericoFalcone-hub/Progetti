<?php
session_start();

// Protezione: solo manager loggato
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
    $cognome = $_POST['cognome'];
    $telefono = str_replace(' ', '', $_POST['telefono']);
    $password = $_POST['password'];

    $result = crea_manager($mail, $nome, $cognome, $telefono, $password);

    if ($result === true) {
        $success_msg = "Nuovo manager creato correttamente!";
    } else {
        // Controlla se l'errore riguarda mail duplicata
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
    <title>Crea Manager - ComicGalaxy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<?php include '../navbar.php'; ?>

<div class="container my-5">

    <div class="text-center mb-4">
        <h1 class="fw-bold">Crea Nuovo Manager</h1>
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
            <a href="gestione_manager.php" class="btn btn-secondary">Indietro</a>
            <button type="submit" name="crea" class="btn btn-success">Crea Manager</button>
        </div>
    </form>

</div>

</body>
</html>
