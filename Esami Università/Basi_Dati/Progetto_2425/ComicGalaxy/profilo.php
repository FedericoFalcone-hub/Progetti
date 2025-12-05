<?php
session_start();
include 'lib/functions.php';

// Accesso solo se loggato
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// Gestione aggiornamento profilo
$success_msg = $error_msg = null;

if (isset($_POST['update_profile'])) {
    $telefono = trim($_POST['telefono']);
    
    if (aggiornaTelefono($_SESSION['user'], $telefono)) {
        $_SESSION['telefono'] = $telefono;
        $success_msg = "Profilo aggiornato correttamente!";
    } else {
        $error_msg = "Errore durante l'aggiornamento del profilo.";
    }
}

if (isset($_POST['change_password'])) {
    $current = $_POST['current_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];
    
    if ($new !== $confirm) {
        $error_msg = "La nuova password e la conferma non coincidono.";
    } elseif (cambiaPassword($_SESSION['user'], $current, $new)) {
        $success_msg = "Password aggiornata con successo!";
    } else {
        $error_msg = "Password corrente errata.";
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Profilo Utente - ComicGalaxy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="container my-5">
    <div class="card shadow-sm">
        <div class="card-header text-center">
            <h2>Profilo Utente</h2>
        </div>
        <div class="card-body">

            <?php if ($success_msg): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success_msg) ?></div>
            <?php endif; ?>
            <?php if ($error_msg): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error_msg) ?></div>
            <?php endif; ?>

            <p><strong>Nome:</strong> <?= htmlspecialchars($_SESSION['nome']) ?></p>
            <p><strong>Cognome:</strong> <?= htmlspecialchars($_SESSION['cognome']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($_SESSION['user']) ?></p>
            <p><strong>Ruolo:</strong> <?= htmlspecialchars($_SESSION['ruolo']) ?></p>

            <hr>

            <!-- Modifica telefono -->
            <h5>Aggiorna Telefono</h5>
            <form method="post" class="mb-4">
                <div class="mb-3">
                    <input type="text" name="telefono" class="form-control" value="<?= htmlspecialchars($_SESSION['telefono']) ?>" required>
                </div>
                <button type="submit" name="update_profile" class="btn btn-primary">Aggiorna Telefono</button>
            </form>

            <!-- Modifica password -->
            <h5>Cambia Password</h5>
            <form method="post">
                <div class="mb-3">
                    <input type="password" name="current_password" class="form-control" placeholder="Password attuale" required>
                </div>
                <div class="mb-3">
                    <input type="password" name="new_password" class="form-control" placeholder="Nuova password" required>
                </div>
                <div class="mb-3">
                    <input type="password" name="confirm_password" class="form-control" placeholder="Conferma nuova password" required>
                </div>
                <button type="submit" name="change_password" class="btn btn-warning">Aggiorna Password</button>
            </form>

        </div>
        <div class="card-footer text-center">
            <a href="<?=
                $_SESSION['ruolo'] === 'manager'
                    ? 'manager/area_manager.php'
                    : 'area_clienti.php'
            ?>" class="btn btn-secondary">Torna alla tua area riservata</a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
