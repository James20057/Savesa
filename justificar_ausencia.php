<?php
/**
 * ------------------------------------------------------------------------
 * justificar_ajax.php – Registrar o actualizar justificación de ausencia
 * ------------------------------------------------------------------------
 *
 * Punto final que procesa el formulario de justificación enviado por
 * un Estudiante.  Guarda (o actualiza) el registro en la tabla
 * `asistencias` y almacena el archivo adjunto, si existe.
 *
 * Flujo de trabajo
 * ----------------
 * 1.  Verifica que el usuario esté logueado y sea Estudiante.
 * 2.  Obtiene `grupo_id`, texto de justificación y archivo opcional.
 * 3.  Sube el archivo (si llega) a `/justificaciones/`.
 * 4.  Inserta o actualiza la fila en `asistencias` con ON DUPLICATE KEY.
 * 5.  Guarda un mensaje flash en la sesión y redirige al panel.
 *
 * Suposiciones de esquema
 * -----------------------
 *  - La tabla `asistencias` posee una UNIQUE KEY sobre
 *      (estudiante_id, grupo_id, fecha)
 *    para que ON DUPLICATE KEY funcione correctamente.
 *
 * Requisitos
 * ----------
 *  - PHP 7.4+ con extensión mysqli.
 *  - Carpetas con permisos de escritura para subir archivos.
 *
 * @author   Savesa Team
 * @license  MIT
 * @version  1.0
 */

session_start();
include('conexion.php');

/* --------------------------------------------------------------------- */
/* 1) CONTROL DE ACCESO                                                  */
/* --------------------------------------------------------------------- */
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'Estudiante') {
    /* Solo los estudiantes pueden enviar justificaciones */
    header("Location: panel.php?tab=asistencia");
    exit();
}



/* --------------------------------------------------------------------- */
/* 2) DATOS BÁSICOS DEL FORMULARIO                                       */
/* --------------------------------------------------------------------- */
$usuario_id    = $_SESSION['usuario_id'];         // ID del estudiante
$grupo_id      = intval($_POST['grupo_id']);      // Grupo al que pertenece la ausencia
$justificacion = trim($_POST['justificacion'] ?? ''); // Texto opcional

/* --------------------------------------------------------------------- */
/* 3) MANEJO DEL ARCHIVO ADJUNTO (opcional)                              */
/* --------------------------------------------------------------------- */
$nombre_archivo = null;   // Nombre final que se guardará en la BD

if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] === UPLOAD_ERR_OK) {
    /* 3.1) Construir nombre único: justificacion_<uid>_<timestamp>.ext */
    $ext            = strtolower(pathinfo($_FILES['archivo']['name'], PATHINFO_EXTENSION));
    $nombre_archivo = "justificacion_{$usuario_id}_" . time() . ".{$ext}";
    $destino_dir    = __DIR__ . '/justificaciones/';

    /* Crear directorio si no existe --------------------------------- */
    if (!is_dir($destino_dir)) mkdir($destino_dir, 0777, true);

    /* Mover archivo desde tmp al destino ---------------------------- */
    move_uploaded_file($_FILES['archivo']['tmp_name'], $destino_dir . $nombre_archivo);
}

/* --------------------------------------------------------------------- */
/* 4) INSERTAR O ACTUALIZAR REGISTRO EN `asistencias`                    */
/* --------------------------------------------------------------------- */
/**
 * ON DUPLICATE KEY UPDATE:
 *   - Si ya existe fila (por UNIQUE KEY estudiante+grupo+fecha),
 *     actualiza `estado`, `justificacion` y `archivo_justificacion`.
 */
$stmt = $conn->prepare("
    INSERT INTO asistencias
        (estudiante_id, grupo_id, fecha,   estado,      justificacion, archivo_justificacion)
    VALUES
        (?,             ?,        CURDATE(),'Justificado', ?,           ?)
    ON DUPLICATE KEY UPDATE
        estado                = 'Justificado',
        justificacion         = VALUES(justificacion),
        archivo_justificacion = VALUES(archivo_justificacion)
");
$stmt->bind_param("iiss", $usuario_id, $grupo_id, $justificacion, $nombre_archivo);
$stmt->execute();
$stmt->close();

/* --------------------------------------------------------------------- */
/* 5) MENSAJE FLASH Y REDIRECCIÓN                                        */
/* --------------------------------------------------------------------- */
$_SESSION['msg_justificacion'] = "¡Justificación enviada correctamente!";
header("Location: panel.php?tab=asistencia");
exit();
?>
