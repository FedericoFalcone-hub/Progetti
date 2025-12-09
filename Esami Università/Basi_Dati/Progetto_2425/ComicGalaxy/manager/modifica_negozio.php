<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['ruolo'] !== 'manager') {
    header("Location: ../login.php");
    exit();
}

include '../lib/functions.php';

if (!isset($_GET['id'])) {
    header("Location: gestione_negozi.php");
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

$id = $_GET['id'];

$negozio = getNegozioById($id);
$return_page = isset($_GET['return']) ? $_GET['return'] : 'gestione_negozi';

if (isset($_POST['save'])) {
    $nome = $_POST['nome'];
    $telefono= $_POST['telefono'];
    
    $via = $_POST['via'];
    $civico = $_POST['civico'];
    $citta = $_POST['citta'];
    
    $result = aggiorna_negozio(intval($id), $nome, $telefono, $citta, $via, $civico);
    var_dump($result);
    if ($result) {
        $_SESSION['success_msg'] = "Negozio aggiornato correttamente!";
        
        header("Location: modifica_negozio.php?id=" . urlencode($id));
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
    <title>Modifica Negozio - ComicGalaxy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<?php include '../navbar.php'; ?>

<div class="container my-5">

    <div class="text-center mb-4">
        <h1 class="fw-bold">Modifica Negozio</h1>
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
            <input type="text" name="nome" class="form-control" value="<?= htmlspecialchars($negozio['nome']) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Telefono</label>
            <input type="text" name="telefono" class="form-control" value="<?= htmlspecialchars($negozio['telefono']) ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Via</label>
            <input type="text" name="via" class="form-control" value="<?= htmlspecialchars($negozio['via']) ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Civico</label>
            <input type="number" name="civico" class="form-control" value="<?= htmlspecialchars($negozio['civico']) ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Città</label>
            <input type="text" name="citta" class="form-control" value="<?= htmlspecialchars($negozio['citta']) ?>">
        </div>


        <div class="d-flex justify-content-between">
            <a href="<?= htmlspecialchars($return_page) ?>.php" class="btn btn-secondary">Indietro</a>
            <button type="submit" name="save" class="btn btn-success">Salva Modifiche</button>
        </div>
    </form>
</div>

</body>
</html>
