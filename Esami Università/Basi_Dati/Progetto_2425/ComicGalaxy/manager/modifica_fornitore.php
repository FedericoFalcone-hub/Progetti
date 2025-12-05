<?php
session_start();

// Protezione: solo manager loggato
if (!isset($_SESSION['user']) || $_SESSION['ruolo'] !== 'manager') {
    header("Location: ../login.php");
    exit();
}

include '../lib/functions.php';

// Ottieni l'id del cliente da modificare (passato via GET)
if (!isset($_GET['p_iva'])) {
    header("Location: gestione_fornitori.php");
    exit();
}

$success_msg = null;
if (isset($_SESSION['success_msg'])) {
    $success_msg = $_SESSION['success_msg'];
    unset($_SESSION['success_msg']);
}

$error_msg = null;
if (isset($_SESSION['error_msg'])) {
    $error_msg = $_SESSION['error_msg'];
    unset($_SESSION['error_msg']);
}


$p_iva = $_GET['p_iva'];
$fornitore = getFornitoreByPIVA($p_iva);
$return_page = isset($_GET['return']) ? $_GET['return'] : 'gestione_fornitori';

if (isset($_POST['save'])) {
    $f_p_iva = $_POST['f_p_iva'];
    $nome = $_POST['nome'];
    $telefono= $_POST['telefono'];
    $mail = $_POST['mail'];
    $via = $_POST['via'];
    $civico = $_POST['civico'];
    $citta = $_POST['citta'];
    
    $result = aggiorna_fornitore($p_iva, $f_p_iva, $nome, $telefono, $mail, $via, $civico, $citta);
    if ($result === true) {
        $_SESSION['success_msg'] = "Fornitore aggiornato correttamente!";
        $p_iva = $f_p_iva;
        $fornitore = getFornitoreByPIVA($p_iva); 
         header("Location: modifica_fornitore.php?p_iva=" . urlencode($f_p_iva));
    exit();
    } else {
        $_SESSION['error_msg'] = "Errore durante l'aggiornamento: " . htmlspecialchars($result);
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Modifica Fornitore - ComicGalaxy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<?php include '../navbar.php'; ?>

<div class="container my-5">

    <div class="text-center mb-4">
        <h1 class="fw-bold">Modifica Fornitore</h1>
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
            <input type="text" name="nome" class="form-control" value="<?= htmlspecialchars($fornitore['nome']) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Partita IVA</label>
            <input type="text" name="f_p_iva" class="form-control" value="<?= htmlspecialchars($p_iva) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="mail" class="form-control" value="<?= htmlspecialchars($fornitore['mail']) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Telefono</label>
            <input type="text" name="telefono" class="form-control" value="<?= htmlspecialchars($fornitore['telefono']) ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Via</label>
            <input type="text" name="via" class="form-control" value="<?= htmlspecialchars($fornitore['via']) ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Civico</label>
            <input type="number" name="civico" class="form-control" value="<?= htmlspecialchars($fornitore['civico']) ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Città</label>
            <input type="text" name="citta" class="form-control" value="<?= htmlspecialchars($fornitore['citta']) ?>">
        </div>


        <div class="d-flex justify-content-between">
            <a href="<?= htmlspecialchars($return_page) ?>.php" class="btn btn-secondary">Indietro</a>
            <button type="submit" name="save" class="btn btn-success">Salva Modifiche</button>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
