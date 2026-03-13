<?php
session_start();
require_once '../config/conexion.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_ticket'])) {
    $id = $_POST['id_ticket'];
    $motor = trim($_POST['motor']);
    $tipo = $_POST['tipo'];
    $cantidad = $_POST['cantidad'];
    $severidad = $_POST['severidad'];
    $id_defecto = $_POST['id_defecto'];

    try {
        $pdo->beginTransaction();

        // 1. Actualizar tabla principal
        $stmt = $pdo->prepare("UPDATE tickets SET id_motor = ?, tipo_motor_captura = ?, cantidad_motores = ?, severidad = ?, id_defecto = ? WHERE id = ?");
        $stmt->execute([$motor, $tipo, $cantidad, $severidad, $id_defecto, $id]);

        // 2. Limpiar piezas previas
        $stmt_del = $pdo->prepare("DELETE FROM ticket_piezas WHERE id_ticket = ?");
        $stmt_del->execute([$id]);

        // 3. Insertar nuevas piezas
        if (!empty($_POST['piezas_id'])) {
            $stmt_ins = $pdo->prepare("INSERT INTO ticket_piezas (id_ticket, id_pieza, cantidad) VALUES (?, ?, ?)");
            foreach ($_POST['piezas_id'] as $index => $p_id) {
                $p_qty = $_POST['piezas_cant'][$index];
                $stmt_ins->execute([$id, $p_id, $p_qty]);
            }
        }

        $pdo->commit();
        header("Location: ../tickets_abiertos.php?msg=success_edit");
        exit();

    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("Error en modificar_ticket: " . $e->getMessage());
        header("Location: ../tickets_abiertos.php?msg=error_edit");
        exit();
    }
}
