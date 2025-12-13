<?php
session_start();


if (!isset($_SESSION["user"]) || $_SESSION["ruolo"] !== "manager") {
    header("Location: ../login.php");
    exit();
}
if ($_SESSION['sospeso'] === 't') {
    header("Location: area_manager.php");
    exit();
}
require_once "../lib/functions.php";

$fornitori = getFornitori();
if (isset($_POST["sospendi"])) {
    $p_iva = $_POST["p_iva"];
    if (sospendi_fornitore($p_iva)) {
        $success_msg = "Fornitore sospeso con successo!";
    } else {
        $error_msg = "Errore durante la sospensione del fornitore.";
    }

    $_SESSION["success"] = "Fornitore sospeso con successo.";

    header("Location: gestione_fornitori.php");
    exit();
}

if (isset($_POST["riattiva"])) {
    $p_iva = $_POST["p_iva"];
    if (riattiva_fornitore($p_iva)) {
        $success_msg = "Fornitore riattivato con successo!";
    } else {
        $error_msg = "Errore durante la riattivazione del fornitore.";
    }

    $_SESSION["success"] = "Fornitore riattivato con successo.";

    header("Location: gestione_fornitori.php");
    exit();
}

if (isset($_POST['crea_fornitore'])) {
    $nome = $_POST['nome'];
    $f_p_iva = $_POST['f_p_iva'];
    $mail = $_POST['mail'];
    $citta = $_POST['citta'];
    $via = $_POST['via'];
    $civico = intval($_POST['civico']);
    $telefono = str_replace(' ', '', $_POST['telefono']);

    $result = crea_fornitore($nome, $f_p_iva, $mail, $citta, $via, $civico, $telefono);
    if ($result === true) {
        $_SESSION["success"] = "Fornitore creato con successo.";
    } else {
        if (strpos($result, '23505') !== false || strpos($result, 'indirizzo_univoco') !== false) {
            $_SESSION["error"] = "Errore: questo indirizzo è già associato ad un fornitore o negozio";
        } else {
            $_SESSION["error"] = "Errore nella creazione del fornitore: " . htmlspecialchars($result);
        }
    }
    $fornitori = getFornitori();

    header("Location: gestione_fornitori.php");
    exit();
}

$success = $_SESSION["success"] ?? null;
unset($_SESSION["success"]);
$error = $_SESSION["error"] ?? null;
unset($_SESSION["error"]);
?>

<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <title>Gestione Fornitori - ComicGalaxy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

    <?php include "../navbar.php"; ?>

    <div class="container my-5">

        <div class="text-center mb-4">
            <h1 class="fw-bold">Gestione Fornitori - ComicGalaxy</h1>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <!-- Aggiungi Fornitore -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header">Aggiungi Nuovo Fornitore</div>

            <div class="card-body">
                <form method="POST">

                    <div class="d-flex flex-wrap align-items-end gap-3 mb-3">

                        <div class="col-auto flex-grow-1">
                            <label class="form-label">Nome</label>
                            <input type="text" name="nome" class="form-control" required>
                        </div>

                        <div class="col-auto flex-grow-1">
                            <label class="form-label">Partita IVA</label>
                            <input type="text" name="f_p_iva" class="form-control" required>
                        </div>

                        <div class="col-auto flex-grow-1">
                            <label class="form-label">Email</label>
                            <input type="email" name="mail" class="form-control" required>
                        </div>

                        <div class="col-auto flex-grow-1">
                            <label class="form-label">Telefono</label>
                            <input type="text" name="telefono" class="form-control" required>
                        </div>

                    </div>

                    <div class="d-flex flex-wrap align-items-end gap-3">

                        <div class="col-auto flex-grow-1">
                            <label class="form-label">Via</label>
                            <input type="text" name="via" class="form-control" required>
                        </div>

                        <div class="col-auto flex-grow-1">
                            <label class="form-label">Città</label>
                            <input type="text" name="citta" class="form-control" required>
                        </div>

                        <div class="col-auto" style="max-width: 90px;">
                            <label class="form-label">Civico</label>
                            <input type="text" name="civico" class="form-control" required>
                        </div>

                        <div class="col-auto">
                            <button type="submit" name="crea_fornitore" class="btn btn-success" style="height:38px;">Crea</button>
                        </div>

                    </div>

                </form>
            </div>
        </div>



        <!-- Lista Fornitori -->
        <div class="card shadow-sm">
            <div class="card-body p-0 table-responsive">
                <table class="table table-striped table-hover table-bordered mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Nome</th>
                            <th>Partita IVA</th>
                            <th>Email</th>
                            <th>Telefono</th>
                            <th>Stato</th>
                            <th style="width: 260px;">Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($fornitori as $f): ?>
                            <tr>
                                <td><?= htmlspecialchars($f["nome"]) ?></td>
                                <td><?= htmlspecialchars($f["p_iva"]) ?></td>
                                <td><?= htmlspecialchars($f["mail"]) ?></td>
                                <td><?= htmlspecialchars($f["telefono"]) ?></td>
                                <td>
                                    <?php if ($f["sospeso"] == "f"): ?>
                                        <span class="badge bg-success">Attivo</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Sospeso</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="prodotti_fornitore.php?p_iva=<?= urlencode($f["p_iva"]) ?>&return=gestione_fornitori" class="btn btn-sm btn-info">Prodotti</a>
                                        <a href="modifica_fornitore.php?p_iva=<?= urlencode($f["p_iva"]) ?>&return=gestione_fornitori" class="btn btn-sm btn-warning">Modifica</a>
                                        <form action="gestione_fornitori.php" method="POST" style="display:inline;">
                                            <input type="hidden" name="p_iva" value="<?= htmlspecialchars(
                                                                                            $f["p_iva"]
                                                                                        ) ?>">
                                            <input type="hidden" name="return" value="gestione_fornitori">
                                            <?php if ($f["sospeso"] === "f"): ?>
                                                <button type="submit" name="sospendi" class="btn btn-sm btn-danger">
                                                    Sospendi
                                                </button>
                                            <?php else: ?>
                                                <button type="submit" name="riattiva" class="btn btn-sm btn-success">
                                                    Riattiva
                                                </button>
                                            <?php endif; ?>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4">
            <a href="area_manager.php" class="btn btn-secondary">Torna all'area riservata</a>
        </div>

    </div>

</body>

</html>