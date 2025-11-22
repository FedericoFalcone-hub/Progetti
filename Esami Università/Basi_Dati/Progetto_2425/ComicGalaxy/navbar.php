<?php
// navbar.php
?>

<div class="navbar">
    <div class="nav-left">
        <a href="index.php">ComicGalaxy</a>
        <a href="#">Negozi</a>
        <a href="#">Clienti</a>
        <a href="#">Tessere</a>
        <a href="#">Ordini</a>
        <a href="#">Fornitori</a>
    </div>

    <div class="nav-right">
        <?php if (!isset($_SESSION['user'])) : ?>
            <a href="login.php" class="button">Login</a>
        <?php else : ?>
            <span>Benvenuto, <a href="profilo.php" class="username-link"><?= htmlspecialchars($_SESSION['nome']) ?></a></span>

            <?php if ($_SESSION['ruolo'] === 'manager') : ?>
                <a href="area_manager.php" class="button" style="margin-left:10px;">Area Riservata</a>
            <?php elseif ($_SESSION['ruolo'] === 'cliente') : ?>
                <a href="area_clienti.php" class="button" style="margin-left:10px;">La mia Area</a>
            <?php endif; ?>

            <a href="index.php?logout=1" class="button" style="margin-left:10px;">Logout</a>
        <?php endif; ?>
    </div>
</div>
