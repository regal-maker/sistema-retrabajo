<nav class="navbar navbar-dark p-2 mb-4 sticky-top navbar-custom">
    <div class="container-fluid px-4">
        
        <a class="navbar-brand d-flex align-items-center" href="dashboard.php">
            <img src="src="img/RRX_Logo_White_GreenLeaf.png"" alt="Regal Rexnord" style="height: 35px; width: auto; max-width: 150px; object-fit: contain;">
        </a>

        <div class="d-none d-lg-block border-start ms-3 ps-3" style="border-color: rgba(255,255,255,0.2) !important;">
            <span class="text-white fw-bold text-uppercase" style="font-size: 0.85rem; letter-spacing: 1px;">
                <?php echo $pagina_actual ?? 'SISTEMA DE RETRABAJO'; ?>
            </span>
        </div>

        <div class="ms-auto d-flex align-items-center gap-2">
            <div class="d-none d-md-flex flex-column align-items-end me-3" style="line-height: 1.6;">
                <small class="text-white" style="font-size: 0.65rem; opacity: 0.8; text-transform: uppercase;">Sesión activa:</small>
                <span class="text-white fw-bold" style="font-size: 1.3rem;">
                    <i class="bi bi-person-fill small"></i> <?php echo htmlspecialchars($_SESSION['user_nombre'] ?? 'Usuario'); ?>
                </span>
            </div>
                        
            <a href="dashboard.php" class="btn btn-outline-light btn-sm fw-bold" style="font-size: 0.7rem; padding: 4px 12px; border-radius: 6px;">
                <i class="bi bi-house-door-fill me-1"></i> Inicio
            </a>

            <a href="backend/logout.php" class="btn btn-danger btn-sm shadow-sm fw-bold" style="font-size: 0.7rem; padding: 4px 12px; border: none; border-radius: 6px;">
                <i class="bi bi-door-open-fill"></i> Salir
            </a>
        </div>
    </div>

</nav>


