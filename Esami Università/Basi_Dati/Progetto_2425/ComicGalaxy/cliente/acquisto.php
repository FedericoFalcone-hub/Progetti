<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['ruolo'] !== 'cliente') {
    header("Location: ../login.php");
    exit();
}
if ($_SESSION['sospeso'] === 't') {
    header("Location: area_clienti.php");
    exit();
}

if (!isset($_SESSION['carrello'])) {
    $_SESSION['carrello'] = [];
}


require_once '../lib/functions.php';

$negozi = getNegozi_aperti();

$prodotti = [];
$selected = null;
$success = null;
$error = null;

if (isset($_GET['negozio'])) {
    $selected = $_GET['negozio'];
    $carrello = [];
    $prodotti = getProdottiNegozio($selected);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aggiungi_carrello'])) {

    $negozio = $_POST['negozio'];
    $quantita = $_POST['quantita'] ?? [];
    foreach ($quantita as $id_prodotto => $qta) {
        if ($qta > 0) {

            $nome = $_POST['nome'][$id_prodotto];
            $prezzo = floatval($_POST['prezzo'][$id_prodotto]);
            if (isset($_SESSION['carrello'][$id_prodotto])) {
                $_SESSION['carrello'][$id_prodotto]['quantita'] += $qta;
            } else {
                $_SESSION['carrello'][$id_prodotto] = [
                    'nome' => $nome,
                    'prezzo' => $prezzo,
                    'quantita' => $qta,
                    'negozio' => $negozio
                ];
            }
        }
    }


    $success = "Prodotti aggiunti al carrello!";
}


?>
<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <title>Acquista - ComicGalaxy</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

    <?php include '../navbar.php'; ?>

    <div class="container mt-5">

        <h1 class="fw-bold text-primary text-center">🛒 Acquista Prodotti</h1>
        <p class="text-center text-secondary">Scegli un negozio e completa il tuo ordine.</p>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="mt-4 text-start">
            <a href="/cliente/area_clienti.php" class="btn btn-secondary">
                Torna all'area riservata
            </a>
        </div>
        <div class="card shadow-sm mt-4">

            <div class="card-header fw-bold">Seleziona un negozio</div>

            <div class="card-body">

                <form method="GET" class="row gx-2 gy-2 align-items-end">
                    <div class="col-md-6">
                        <label class="form-label">Negozio</label>
                        <select name="negozio" class="form-select" required>
                            <option value="">Scegli...</option>
                            <?php foreach ($negozi as $n): ?>
                                <option value="<?= $n['id'] ?>" <?= ($selected == $n['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($n['nome']) ?> — <?= htmlspecialchars($n['indirizzo']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <button class="btn btn-primary w-100">Mostra Prodotti</button>
                    </div>
                </form>

            </div>
        </div>

        <?php if ($selected && $prodotti): ?>
            <div class="card shadow-sm mt-4">

                <div class="card-header d-flex justify-content-between align-items-center fw-bold">
                    <span>Prodotti disponibili</span>
                    <a href="acquisto_carrello.php?negozio=<?= urlencode($selected) ?>" class="btn btn-primary btn-sm">
                        🛒 Vai al Carrello
                    </a>
                </div>


                <div class="card-body table-responsive">

                    <form method="POST">

                        <input type="hidden" name="negozio" value="<?= $selected ?>">

                        <table class="table table-bordered table-striped">
                            <thead class="table-light">
                                <tr>
                                    <th>Prodotto</th>
                                    <th>Descrizione</th>
                                    <th>Prezzo</th>
                                    <th>Disponibilità</th>
                                    <th>Quantità</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php foreach ($prodotti as $p): ?>
                                    <tr>
                                        <input type="hidden" name="nome[<?= $p['id'] ?>]" value="<?= htmlspecialchars($p['nome']) ?>">


                                        <td><?= htmlspecialchars($p['nome']) ?></td>
                                        <td><?= htmlspecialchars($p['descrizione']) ?></td>
                                        <input type="hidden" name="prezzo[<?= $p['id'] ?>]" value="<?= $p['prezzo'] ?>">
                                        <td>€<?= number_format($p['prezzo'], 2) ?></td>
                                        <td><?= $p['quantita'] - (intval($_SESSION['carrello'][$p['id']]['quantita'] ?? 0)) ?></td>
                                        <td style="width:120px;">
                                            <input type="number"
                                                name="quantita[<?= $p['id'] ?>]"
                                                class="form-control"
                                                min="0"
                                                max="<?= $p['quantita'] ?>"
                                                value="0">
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                        <button type="submit" name="aggiungi_carrello" class="btn btn-success w-100 mt-3">
                            ➕ Aggiungi al Carrello
                        </button>

                    </form>


                </div>
            </div>
        <?php elseif ($selected): ?>
            <div class="alert alert-warning mt-4">Nessun prodotto disponibile in questo negozio.</div>
        <?php endif; ?>

    </div>


</body>

</html>