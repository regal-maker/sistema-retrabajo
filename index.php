<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

// Cabeceras para evitar caché de forma agresiva
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <?php include 'includes/header.php'; ?>
    <style>
        /* Estilos específicos para que la tarjeta flote al centro de la pantalla */
        body { background-color: #e9ecef; height: 100vh; display: flex; align-items: center; justify-content: center; }
        .card-login { border-radius: 1rem; box-shadow: 0 10px 25px rgba(0,0,0,0.2); width: 100%; max-width: 450px; }
        .logo-regal { font-weight: 800; color: var(--regal-blue); letter-spacing: 2px; }
    </style>
</head>
<body>

<div class="container d-flex justify-content-center">
    <div class="card card-login p-4">
        <div class="text-center mb-4">
            <a class="container d-flex justify-content-center" >
            <img src="assets/img/icono-app.png" alt="Regal Rexnord" style="height: 35px; width: auto; max-width: 150px; object-fit: contain;">
        </a>
            <p class="text-muted small">Escanee su credencial para ingresar</p>
            
            <?php if(isset($_GET['error'])): ?>
                <div class="alert alert-danger py-2 mt-2 small fw-bold"><i class="bi bi-exclamation-triangle-fill"></i> Credenciales incorrectas</div>
            <?php endif; ?>
        </div>

        <form id="loginForm" action="backend/auth.php" method="POST" autocomplete="off">
            <div class="mb-3">
                <label class="form-label small fw-bold">Código de Empleado (Scan)</label>
                <input type="password" name="codigo_credencial" id="inputUser" 
                       class="form-control form-control-lg" 
                       placeholder="Escanee aquí..." autofocus required autocomplete="new-password">
            </div>
            <div class="mb-4">
                <label class="form-label small fw-bold">Contraseña</label>
                <input type="password" name="password" id="inputPass" 
                       class="form-control form-control-lg" 
                       placeholder="******" required autocomplete="new-password">
            </div>
            <button type="submit" class="btn btn-primary w-100 btn-lg fw-bold shadow-sm">INICIAR SESIÓN</button>
        </form>
    </div>
</div>

<script>
    // Función para limpiar campos al regresar
    function limpiarCampos() {
        document.getElementById('inputUser').value = "";
        document.getElementById('inputPass').value = "";
    }

    window.onload = limpiarCampos;

    // Se ejecuta cuando el usuario navega hacia atrás
    window.addEventListener('pageshow', function(event) {
        if (event.persisted) {
            limpiarCampos();
            window.location.reload(); 
        }
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>




