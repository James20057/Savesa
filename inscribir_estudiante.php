<?php
/**
 * ------------------------------------------------------------------------
 * inscribir_estudiante.php – Añadir un estudiante a un grupo
 * ------------------------------------------------------------------------
 *
 * Visibilidad          : Solo Administradores
 * Funcionalidades
 * =================
 * 1.  Muestra un formulario para seleccionar un grupo y un estudiante.
 * 2.  Inserta la relación (grupo_id, estudiante_id) en `grupo_estudiante`
 *     evitando duplicados.
 * 3.  Proporciona feedback (éxito / error) que se muestra sobre la vista.
 *
 * Flujo
 * -----
 * 1.  Verifica la sesión y el rol (Administrador).
 * 2.  Recupera datos del usuario para la barra lateral.
 * 3.  Obtiene la lista de grupos y estudiantes para los `<select>`.
 * 4.  Si llega POST, valida y registra la inscripción evitando repetición.
 *
 * Requisitos
 * ----------
 * - PHP 7.4+ y extensión mysqli.
 * - Tablas: usuarios, grupos, materias, grupo_estudiante.
 *
 * @author   Savesa Team
 * @license  MIT
 * @version  1.0
 */

include('conexion.php');
session_start();

/* --------------------------------------------------------------------- */
/* 1) CONTROL DE ACCESO                                                  */
/* --------------------------------------------------------------------- */
if ($_SESSION['rol'] !== 'Administrador') {
    header('Location: login.php');
    exit();
}

/* --------------------------------------------------------------------- */
/* 2) DATOS DEL USUARIO (para el sidebar)                                */
/* --------------------------------------------------------------------- */
$usuario_id = $_SESSION['usuario_id'];  

$stmt = $conn->prepare("
    SELECT foto_perfil, nombre_completo
    FROM   usuarios
    WHERE  id = ?
");
$stmt->bind_param("i", $_SESSION['usuario_id']);
$stmt->execute();
$stmt->bind_result($foto_perfil, $nombre);
$stmt->fetch();
$stmt->close();

/* Nombre “abreviado” (solo 2 palabras) -------------------------------- */
$nombres  = explode(' ', $nombre ?: 'Usuario');
$mostrar  = isset($nombres[1]) ? "{$nombres[0]} {$nombres[1]}" : $nombres[0];

// Sidebar usuario

$foto_perfil = null;
// --- OBTENEMOS LA FOTO Y NOMBRE MÁS RECIENTES DE LA BD ---
$stmt = $conn->prepare("SELECT foto_perfil, nombre_completo, correo_electronico, zona_horaria FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$stmt->bind_result($foto_perfil, $nombre, $correo, $zona);
$stmt->fetch();
$stmt->close();

$nombres = explode(' ', $nombre ?? 'Usuario');
$mostrar = isset($nombres[1]) ? $nombres[0] . ' ' . $nombres[1] : $nombres[0];
$ruta_foto = ($foto_perfil && file_exists(__DIR__ . "/images/perfiles/" . $foto_perfil))
    ? "/savesa/images/perfiles/" . $foto_perfil
    : "/savesa/images/perfiles/nopicture.png";


/* --------------------------------------------------------------------- */
/* 3) LISTA DE GRUPOS Y ESTUDIANTES (para los select)                    */
/* --------------------------------------------------------------------- */
$grupos = [];
$res = $conn->query("
    SELECT g.id, g.nombre, m.nombre AS materia
    FROM   grupos g
    JOIN   materias m ON g.materia_id = m.id
");
while ($row = $res->fetch_assoc()) $grupos[] = $row;

$estudiantes = [];
$res = $conn->query("
    SELECT id, nombre_completo
    FROM   usuarios
    WHERE  rol = 'Estudiante'
");
while ($row = $res->fetch_assoc()) $estudiantes[] = $row;

/* --------------------------------------------------------------------- */
/* 4) PROCESAR ENVÍO DEL FORMULARIO                                      */
/* --------------------------------------------------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' &&
    !empty($_POST['grupo_id']) &&
    !empty($_POST['estudiante_id'])) {

    $grupo_id      = intval($_POST['grupo_id']);
    $estudiante_id = intval($_POST['estudiante_id']);

    /* 4.1) Comprobar duplicado ------------------------------------- */
    $stmt = $conn->prepare("
        SELECT COUNT(*)
        FROM   grupo_estudiante
        WHERE  grupo_id      = ?
          AND  estudiante_id = ?
    ");
    $stmt->bind_param("ii", $grupo_id, $estudiante_id);
    $stmt->execute();
    $stmt->bind_result($existe);
    $stmt->fetch();
    $stmt->close();

    if ($existe) {
        $mensaje  = "Este estudiante ya está inscrito en ese grupo.";
        $msg_tipo = "error";
    } else {
        /* 4.2) Insertar inscripción -------------------------------- */
        $stmt = $conn->prepare("
            INSERT INTO grupo_estudiante (grupo_id, estudiante_id)
            VALUES (?, ?)
        ");
        $stmt->bind_param("ii", $grupo_id, $estudiante_id);
        $stmt->execute();
        $stmt->close();

        $mensaje  = "Estudiante inscrito correctamente.";
        $msg_tipo = "success";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Inscribir Estudiante en Grupo</title>

  <!-- Estilos globales -->
  <link rel="stylesheet" href="/savesa/css/dashboard.css">
  <link rel="stylesheet" href="/savesa/css/form-admin.css">
</head>
<body>
<div class="dashboard-container">

  <!-- ===============================================================
       SIDEBAR LATERAL
       =============================================================== -->
  <aside class="sidebar">
    <div class="sidebar-header"><h2>Asistencia Virtual</h2></div>

    <!-- Navegación de administrador -------------------------------- -->
    <nav class="sidebar-nav">
      <a class="sidebar-item"        href="panel.php"><span class="icon">📊</span><span class="label">Panel</span></a>
      <a class="sidebar-item"        href="materias.php"><span class="icon">📚</span><span class="label">Materias</span></a>
      <a class="sidebar-item"        href="crear_materia.php"><span class="icon">➕</span><span class="label">Crear Materia</span></a>
      <a class="sidebar-item"        href="crear_grupo.php"><span class="icon">👥</span><span class="label">Crear Grupo</span></a>
      <a class="sidebar-item"        href="asignar_profesor.php"><span class="icon">🧑‍🏫</span><span class="label">Asignar Profesor</span></a>
      <a class="sidebar-item active" href="inscribir_estudiante.php"><span class="icon">👨‍🎓</span><span class="label">Inscribir Estudiante</span></a>
      <a class="sidebar-item"        href="panel.php?tab=settings"><span class="icon">⚙️</span><span class="label">Configuración</span></a>
    </nav>

    <!-- Pie del sidebar (avatar + logout) -------------------------- -->
    <div class="sidebar-footer">
      <img src="<?php echo htmlspecialchars($ruta_foto); ?>?v=<?php echo time(); ?>"
           alt="Avatar" class="avatar" />
      <div>
        <div class="user-name"><?php echo htmlspecialchars($mostrar);        ?></div>
        <div class="user-role"><?php echo htmlspecialchars($_SESSION['rol']); ?></div>
      </div>
      <form action="logout.php" method="post" style="margin:0;">
        <button class="logout-btn" type="submit">🚪</button>
      </form>
    </div>
  </aside>
  <!-- /SIDEBAR -->

  <!-- ===============================================================
       CONTENIDO PRINCIPAL
       =============================================================== -->
  <main class="main-content">
    <header class="overview-header">
      <h1>Inscribir Estudiante en Grupo</h1>
    </header>

    <section class="section">

      <!-- Mensaje de feedback -------------------------------------- -->
      <?php if (isset($mensaje)): ?>
        <p class="msg-<?php echo $msg_tipo; ?>">
          <?php echo htmlspecialchars($mensaje); ?>
        </p>
      <?php endif; ?>

      <!-- Formulario de inscripción -------------------------------- -->
      <form method="post" action="">
        <!-- Selector de grupo -->
        <label>Grupo:</label>
        <select name="grupo_id" required>
          <option value="">Seleccionar</option>
          <?php foreach ($grupos as $g): ?>
            <option value="<?php echo $g['id']; ?>">
              <?php echo htmlspecialchars($g['materia'].' - '.$g['nombre']); ?>
            </option>
          <?php endforeach; ?>
        </select>

        <!-- Selector de estudiante -->
        <label>Estudiante:</label>
        <select name="estudiante_id" required>
          <option value="">Seleccionar</option>
          <?php foreach ($estudiantes as $estu): ?>
            <option value="<?php echo $estu['id']; ?>">
              <?php echo htmlspecialchars($estu['nombre_completo']); ?>
            </option>
          <?php endforeach; ?>
        </select>

        <!-- Botón enviar -->
        <button type="submit" class="card-button">Inscribir Estudiante</button>
      </form>
    </section>
  </main>
</div><!-- /.dashboard-container -->

<!-- ===============================================================
     JS: Ocultar mensajes flash a los 3 s
     =============================================================== -->
<script>
setTimeout(function () {
  const msg = document.querySelector('.msg-success, .msg-error');
  if (msg) {
    msg.style.transition = 'opacity 0.6s';
    msg.style.opacity    = '0';
    setTimeout(()=> msg.style.display = 'none', 700);
  }
}, 3000);
</script>
</body>
</html>
