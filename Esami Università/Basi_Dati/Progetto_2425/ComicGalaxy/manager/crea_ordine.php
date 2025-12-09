<?php
session_start();


if (!isset($_SESSION['user']) || $_SESSION['ruolo'] !== 'manager') {
    header("Location: ../login.php");
    exit();
}

include '../lib/functions.php';

// Ottieni l'id del negozio associato al manager
$negozio = getNegozio($_SESSION['user']);
if ($negozio === null) {
    die('<div class="container text-center mt-5">
            <div class="card shadow p-4">
                <h2 class="text-danger">Errore</h2>
                <p>Non sei associato a nessun negozio.</p>
                <a href="index.php" class="btn btn-primary">Torna alla Home</a>
            </div>
        </div>');
}

// Aggiungi prodotto al carrello
$success_msg = $error_msg = null;
if (isset($_POST['aggiungi_carrello'])) {
    $id_prodotto = $_POST['id_prodotto'];
    $quantita = intval($_POST['quantita']);
    if ($quantita > 0) {
        if (isset($_SESSION['carrello'][$id_prodotto])) {
            $_SESSION['carrello'][$id_prodotto] += $quantita;
        } else {
            $_SESSION['carrello'][$id_prodotto] = $quantita;
        }
        $success_msg = "Prodotto aggiunto al carrello!";
    } else {
        $error_msg = "Inserisci una quantità valida.";
    }
}

// Ottieni prodotti ordinabili dai fornitori
$prodotti_fornitori = getProdottiOrdinabili(); 
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Crea Ordine - ComicGalaxy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<?php include '../navbar.php'; ?>

<div class="container my-5">
    <div class="text-center mb-4">
        <h1 class="fw-bold">Crea Ordine</h1>
        <h2 class="text-primary"><?= htmlspecialchars($negozio['nome']) ?></h2>
        <a href="visualizza_carrello.php" class="btn btn-secondary mt-2">
            Visualizza Carrello (<?= isset($_SESSION['carrello']) ? array_sum($_SESSION['carrello']) : 0 ?>)
        </a>
    </div>

    <?php if ($success_msg): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success_msg) ?></div>
    <?php endif; ?>
    <?php if ($error_msg): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error_msg) ?></div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body p-0 table-responsive">
            <table class="table table-striped table-hover table-bordered mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Prodotto</th>
                        <th>Prezzo medio</th>
                        <th>Quantità disponibile</th>
                        <th>Ordina</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($prodotti_fornitori as $p): ?>
                    <tr>
                        <td><?= htmlspecialchars($p['nome_prodotto']) ?></td>
                        <td>€<?= htmlspecialchars($p['prezzo']) ?></td>
                        <td><?= htmlspecialchars($p['quantita']) ?></td>
                        <td>
                            <form method="post" class="d-flex gap-2 align-items-center">
                                <input type="hidden" name="id_prodotto" value="<?= $p['id_prodotto'] ?>">
                                <input type="number" name="quantita" min="1" max="<?= $p['quantita'] ?>" value="1" class="form-control form-control-sm" style="width:80px;">
                                <button type="submit" name="aggiungi_carrello" class="btn btn-primary btn-sm">Aggiungi</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($prodotti_fornitori)): ?>
                        <tr>
                            <td colspan="4" class="text-center">Nessun prodotto disponibile dai fornitori</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>
