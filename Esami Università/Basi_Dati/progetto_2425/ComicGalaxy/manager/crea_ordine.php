<?php
session_start();

if (!isset($_SESSION["user"]) || $_SESSION["ruolo"] !== "manager") {
    header("Location: ../login.php");
    exit();
}

if ($_SESSION["sospeso"] === "t") {
    header("Location: area_manager.php");
    exit();
}

include "../lib/functions.php";

$negozio = getNegozio($_SESSION["user"]);
if ($negozio === null) {
    die('<div class="container text-center mt-5">
            <div class="card shadow p-4">
                <h2 class="text-danger">Errore</h2>
                <p>Non sei associato a nessun negozio.</p>
                <a href="index.php" class="btn btn-primary">Torna alla Home</a>
            </div>
        </div>');
}

$success_msg = $error_msg = null;
if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["aggiungi_carrello"])
) {
    foreach ($_POST["quantita"] as $id_prodotto => $qta) {
        $qta = intval($qta);

        if ($qta > 0) {
            if (isset($_SESSION["carrello"][$id_prodotto])) {
                $_SESSION["carrello"][$id_prodotto] += $qta;
            } else {
                $_SESSION["carrello"][$id_prodotto] = $qta;
            }
        }
    }

    $success_msg = "Prodotti aggiunti al carrello!";
}

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

    <?php include "../navbar.php"; ?>

    <div class="container my-5">
        <div class="text-center mb-4">
            <h1 class="fw-bold">Crea Ordine</h1>
            <h2 class="text-primary"><?= $negozio["nome"] ?></h2>
            <a href="visualizza_carrello.php" class="btn btn-secondary mt-2">
                Visualizza Carrello (<?= isset($_SESSION["carrello"])
                    ? array_sum($_SESSION["carrello"])
                    : 0 ?>)
            </a>
        </div>

        <?php if ($success_msg): ?>
            <div class="alert alert-success"><?= $success_msg ?></div>
        <?php endif; ?>
        <?php if ($error_msg): ?>
            <div class="alert alert-danger"><?= $error_msg ?></div>
        <?php endif; ?>

        <div class="card shadow-sm">
            <div class="card-body p-0 table-responsive">
                <form method="POST">
                <table class="table table-striped table-hover table-bordered mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Prodotto</th>
                            <th>Prezzo medio</th>
                            <th>Quantità disponibile</th>
                            <th>Quantità</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($prodotti_fornitori as $p): ?>
                            <?php
                            $selezionati = intval($_SESSION["carrello"][$p["id_prodotto"]] ?? 0);
                            $disponibile = $p["quantita"] - $selezionati;
                            ?>
                            <tr>
                                <td><?= $p["nome_prodotto"] ?></td>

                                <td>€<?= number_format($p["prezzo"], 2) ?></td>

                                <td><?= $disponibile ?></td>

                                <td style="width:120px">
                                    <input type="number"
                                        name="quantita[<?= $p["id_prodotto"] ?>]"
                                        min="0"
                                        max="<?= $disponibile?>"
                                        value="0"
                                        class="form-control form-control-sm">
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
                <button type="submit" name="aggiungi_carrello" class="btn btn-success w-100 mt-3">
                            ➕ Aggiungi al Carrello
                        </button>
                </form>
                
            </div>
        </div>
    </div>

</body>

</html>