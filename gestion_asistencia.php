<?php
session_start();
include('conexion.php');
date_default_timezone_set('America/Bogota'); 
header('Content-Type: application/json');

// Solo profesores pueden marcar asistencia
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'Profesor') {
    echo json_encode(['ok' => false, 'msg' => 'No autorizado']);
    exit;
}

$estudiante_id = intval($_POST['estudiante_id'] ?? 0);
$grupo_id = intval($_POST['grupo_id'] ?? 0);
$estado = $_POST['estado'] ?? '';

if (!$estudiante_id || !$grupo_id || !in_array($estado, ['Presente', 'Ausente'])) {
    echo json_encode(['ok' => false, 'msg' => 'Datos inválidos']);
    exit;
}

$fecha = date('Y-m-d');

// Verifica si ya existe la asistencia para hoy
$stmt = $conn->prepare("SELECT id FROM asistencias WHERE estudiante_id=? AND grupo_id=? AND fecha=?");
$stmt->bind_param("iis", $estudiante_id, $grupo_id, $fecha);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->close();
    $stmt2 = $conn->prepare("UPDATE asistencias SET estado=? WHERE estudiante_id=? AND grupo_id=? AND fecha=?");
    $stmt2->bind_param("siis", $estado, $estudiante_id, $grupo_id, $fecha);
    $stmt2->execute();
    $stmt2->close();
} else {
    $stmt->close();
    $stmt2 = $conn->prepare("INSERT INTO asistencias (estudiante_id, grupo_id, fecha, estado) VALUES (?, ?, ?, ?)");
    $stmt2->bind_param("iiss", $estudiante_id, $grupo_id, $fecha, $estado);
    $stmt2->execute();
    $stmt2->close();
}

echo json_encode(['ok' => true]);
exit;
