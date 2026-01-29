<?php
session_start();

include 'lib/functions.php';

if (isset($_SESSION['user'])) {
    header('Location: index.php');
    exit();
}
$errore = null;
$successo = null;

if (isset($_POST['registrati'])) {

    $nome = $_POST['nome'];
    $cognome = $_POST['cognome'];
    $mail = $_POST['mail'];
    $password = $_POST['password'];
    $telefono = $_POST['telefono'];
    $cf = $_POST['cf'];

    // funzione da definire in functions.php
    $esito = crea_cliente($mail, $nome, $cognome, $telefono, $password, $cf);

    if ($esito === true) {
        $successo = "Registrazione avvenuta con successo! Ora puoi accedere.";
    } else {
        if (str_contains($esito, $mail)) {
            $errore = $esito;
        } else if (str_contains($esito, $cf)) {
            $errore = "Codice Fiscale già in uso.";
        } else if (str_contains($esito, $telefono)) {
            $errore = "Numero di telefono non valido.";
        } else {
        $errore = $esito ?? "Errore durante la registrazione.";
    }
}
}
?>
<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <title>Registrazione - ComicGalaxy</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

    <div class="container d-flex justify-content-center align-items-center" style="min-height: 100vh;">

        <div class="card shadow-lg p-4" style="width: 420px;">

            <h2 class="text-center mb-4 text-primary fw-bold">
                Registrati su ComicGalaxy
            </h2>

            <?php if (!empty($errore)) : ?>
                <div class="alert alert-danger text-center">
                    <?= $errore ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($successo)) : ?>
                
            <?php endif; ?>

            <?php if ($successo) : ?>
                <div class="alert alert-success text-center">
                    <?= $successo ?>
                </div>
                <a href="login.php" class="btn btn-primary w-100 mt-3">Vai al login</a>
            <?php else : ?>
            <form method="post">

                <div class="mb-3">
                    <label class="form-label">Nome</label>
                    <input type="text" name="nome" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Cognome</label>
                    <input type="text" name="cognome" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">E-mail</label>
                    <input type="email" name="mail" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Telefono</label>
                    <input type="text" name="telefono" class="form-control">
                </div>

                <div class="mb-3">
                    <label class="form-label">Codice Fiscale</label>
                    <input type="text" name="cf" class="form-control" required>
                </div>

                <button type="submit" name="registrati" class="btn btn-primary w-100">
                    Registrati
                </button>

            </form>
            <?php endif; ?>

            <div class="text-center mt-3">
                <?php if (!$successo) : ?>
                <a href="login.php" class="text-decoration-none">
                    Hai già un account? Accedi
                </a>
                <?php endif; ?>
                <a href="/index.php" class="d-block mt-2 text-decoration-none">
                    Torna alla Home
                </a>
            </div>

        </div>

    </div>

</body>

</html>
