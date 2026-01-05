<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">

        <a class="navbar-brand" href="/index.php">ComicGalaxy</a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
            data-bs-target="#navbarSupportedContent">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">

            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="/lista_negozi.php">I nostri negozi</a></li>
                <li class="nav-item"><a class="nav-link" href="/lista_prodotti.php">I nostri prodotti</a></li>
            </ul>

            <ul class="navbar-nav ms-auto">

                <?php if (!isset($_SESSION['user'])) : ?>

                    <li class="nav-item">
                        <a class="btn btn-warning fw-bold" href="login.php">Login</a>
                    </li>

                <?php else : ?>

                    <li class="nav-item d-flex align-items-center me-3 text-white fw-bold">
                        Ciao, <a href="/profilo.php" class="ms-1 text-white text-decoration-underline">
                            <?= $_SESSION['nome'] ?>
                        </a>
                    </li>

                    <?php if ($_SESSION['ruolo'] === 'manager') : ?>
                        <li class="nav-item">
                            <a class="btn btn-light me-2" href="/manager/area_manager.php">Area Riservata</a>
                        </li>

                    <?php elseif ($_SESSION['ruolo'] === 'cliente') : ?>
                        <li class="nav-item">
                            <a class="btn btn-light me-2" href="/cliente/area_clienti.php">La mia Area</a>
                        </li>
                    <?php endif; ?>

                    <li class="nav-item">
                        <a class="btn btn-danger" href="/index.php?logout=1">Logout</a>
                    </li>

                <?php endif; ?>

            </ul>

        </div>
    </div>
</nav>