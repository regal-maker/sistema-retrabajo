<?php
include('../config/conexion.php');
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $pdo->beginTransaction();

        $modelo = $_POST['modelo_motor'];
        $tipo_motor = $_POST['tipo_motor'];
        $cantidad_motores = $_POST['cantidad_motores'];
        $id_defecto = $_POST['id_defecto'];
        $id_usuario_apertura = $_SESSION['user_id']; 
        
        $fecha = date('Ymd');
        $stmt_count = $pdo->query("SELECT COUNT(*) FROM tickets WHERE DATE(fecha_apertura) = CURDATE()");
        $consecutivo = str_pad($stmt_count->fetchColumn() + 1, 3, '0', STR_PAD_LEFT);
        $folio = "RT-" . $fecha . "-" . $consecutivo;

        $sql_ticket = "INSERT INTO tickets (folio, id_motor, tipo_motor_captura, cantidad_motores, id_defecto, id_usuario_apertura, estado, fecha_apertura) 
                       VALUES (:folio, :modelo, :tipo, :cantidad, :defecto, :usuario, 'Abierto', NOW())";
        
        $stmt = $pdo->prepare($sql_ticket);
        $stmt->execute([
            'folio' => $folio, 'modelo' => $modelo, 'tipo' => $tipo_motor, 
            'cantidad' => $cantidad_motores, 'defecto' => $id_defecto, 'usuario' => $id_usuario_apertura
        ]);

        $id_ticket_generado = $pdo->lastInsertId();

        if (!empty($_POST['piezas_id'])) {
            $sql_piezas = "INSERT INTO ticket_piezas (id_ticket, id_pieza, cantidad) VALUES (?, ?, ?)";
            $stmt_p = $pdo->prepare($sql_piezas);
            foreach ($_POST['piezas_id'] as $index => $id_pieza) {
                $stmt_p->execute([$id_ticket_generado, $id_pieza, $_POST['piezas_cant'][$index]]);
            }
        }

        // Guardamos los datos en la sesión, NO en la URL
$_SESSION['mensaje_exito'] = "Folio " . $folio . " generado correctamente.";

// Redirigimos a la ruta LIMPIA
header("Location: ../dashboard"); 
exit();
?>
