<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard
");
    exit();
}
// Evitar caché para que no se queden datos en el navegador del operador
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <title>Acceso | Regal Rexnord</title>
    <?php include 'includes/header.php'; ?>
    <style>
        body { background-color: #f4f7f6; height: 100vh; display: flex; align-items: center; justify-content: center; }
        .card-login { border-radius: 1rem; box-shadow: 0 15px 35px rgba(0,0,0,0.1); width: 100%; max-width: 400px; border: none; }
        .btn-regal { background-color: #004b8d; border: none; }
        .btn-regal:hover { background-color: #003566; }
    </style>
</head>
<body>

<div class="container d-flex justify-content-center">
    <div class="card card-login p-4">
        <div class="text-center mb-4">
            <img src="assets/img/icono-app.png" alt="Regal Rexnord" style="height: 45px; margin-bottom: 15px;">
            <h5 class="fw-bold text-dark">SISTEMA DE RETRABAJO</h5>
            <p class="text-muted small">Escanee su credencial para ingresar</p>
            
            <?php if(isset($_GET['error'])): ?>
                <div id="error-alert" class="alert alert-danger py-2 mt-2 small fw-bold">
                    <i class="bi bi-exclamation-octagon-fill me-1"></i> Credenciales incorrectas
                </div>
            <?php endif; ?>
        </div>

        <form id="loginForm" action="backend/auth.php" method="POST" autocomplete="off">
            <div class="mb-3">
                <label class="form-label small fw-bold">Código de Empleado</label>
                <input type="password" name="codigo_credencial" id="inputUser" 
                       class="form-control form-control-lg border-2" 
                       placeholder="Escanee aquí..." autofocus required autocomplete="new-password">
            </div>
            <div class="mb-4">
                <label class="form-label small fw-bold">Contraseña</label>
                <input type="password" name="password" id="inputPass" 
                       class="form-control form-control-lg border-2" 
                       placeholder="******" required autocomplete="new-password">
            </div>
            <button type="submit" class="btn btn-primary btn-regal w-100 btn-lg fw-bold shadow-sm">INICIAR SESIÓN</button>
        </form>
    </div>
</div>

<script>
    const inputUser = document.getElementById('inputUser');
    const inputPass = document.getElementById('inputPass');

    function prepararLogin() {
        inputUser.value = "";
        inputPass.value = "";
        inputUser.focus();
    }

    window.onload = prepararLogin;

    // Limpiar la URL de errores después de 4 segundos
    if(window.location.search.includes('error')){
        setTimeout(() => {
            window.history.replaceState({}, document.title, window.location.pathname);
            const alert = document.getElementById('error-alert');
            if(alert) alert.style.display = 'none';
        }, 4000);
    }

    // Asegurar que al regresar con el botón "atrás" se limpie todo
    window.addEventListener('pageshow', (e) => {
        prepararLogin();
        if (e.persisted) window.location.reload();
    });
</script>
</body>
</html>

