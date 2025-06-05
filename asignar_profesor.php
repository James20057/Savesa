<?php
/**
 * ------------------------------------------------------------------------
 * asignar_profesor.php – Enlazar un profesor a un grupo concreto
 * ------------------------------------------------------------------------
 *
 * Visibilidad          : Solo Administradores
 *
 * Funciones principales
 * =====================
 * 1.  Mostrar un formulario con selección de grupo y profesor.
 * 2.  Evitar duplicados en la tabla `materia_profesor`.
 * 3.  Insertar la asignación y mostrar mensaje de confirmación.
 *
 * Flujo
 * -----
 * 1.  Verifica la sesión y que el rol sea 'Administrador'.  
 * 2.  Obtiene datos del usuario para la barra lateral.  
 * 3.  Carga listas de grupos y profesores para los `<select>`.  
 * 4.  Cuando se envía POST, valida duplicados, inserta o muestra error.  
 * 5.  Imprime feedback (success / error) y autodesaparece vía JS.
 *
 * Requisitos
 * ----------
 * - PHP 7.4+ y extensión mysqli.
 * - Tablas: grupos, materias, usuarios, materia_profesor.
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
/* ---------- Sidebar usuario ---------- */
$usuario_id = $_SESSION['usuario_id'];  

$stmt = $conn->prepare("
    SELECT foto_perfil, nombre_completo
    FROM   usuarios
    WHERE  id = ?
");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();

/* ► Vacía / almacena el resultado para evitar el out-of-sync */
$stmt->store_result();

$stmt->bind_result($foto_perfil, $nombre);
$stmt->fetch();
$stmt->close();





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

/* Nombre abreviado (máx. dos palabras) -------------------------------- */
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
/* 3) LISTAS PARA LOS SELECT                                             */
/* --------------------------------------------------------------------- */

/* 3.1) Grupos con su materia correspondiente -------------------------- */
$grupos = [];
$res = $conn->query("
    SELECT g.id, g.nombre, m.nombre AS materia
    FROM   grupos g
    JOIN   materias m ON g.materia_id = m.id
");
while ($row = $res->fetch_assoc()) $grupos[] = $row;

/* 3.2) Profesores disponibles ---------------------------------------- */
$profesores = [];
$res = $conn->query("
    SELECT id, nombre_completo
    FROM   usuarios
    WHERE  rol = 'Profesor'
");
while ($row = $res->fetch_assoc()) $profesores[] = $row;

/* --------------------------------------------------------------------- */
/* 4) PROCESAR ENVÍO DEL FORMULARIO                                      */
/* --------------------------------------------------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' &&
    !empty($_POST['grupo_id']) &&
    !empty($_POST['profesor_id'])) {

    $grupo_id    = intval($_POST['grupo_id']);
    $profesor_id = intval($_POST['profesor_id']);

    /* 4.1) ¿Ya existe la asignación? -------------------------------- */
    $stmt = $conn->prepare("
        SELECT COUNT(*)
        FROM   materia_profesor
        WHERE  grupo_id    = ?
          AND  profesor_id = ?
    ");
    $stmt->bind_param("ii", $grupo_id, $profesor_id);
    $stmt->execute();
    $stmt->bind_result($existe);
    $stmt->fetch();
    $stmt->close();

    if ($existe) {
        $mensaje  = "Ya está asignado ese profesor a ese grupo.";
        $msg_tipo = "error";
    } else {
        /* 4.2) Insertar asignación ---------------------------------- */
        $stmt = $conn->prepare("
            INSERT INTO materia_profesor (grupo_id, profesor_id)
            VALUES (?, ?)
        ");
        $stmt->bind_param("ii", $grupo_id, $profesor_id);
        $stmt->execute();
        $stmt->close();

        $mensaje  = "Profesor asignado correctamente.";
        $msg_tipo = "success";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Asignar Profesor a Grupo</title>

  <!-- Estilos globales -->
  <link rel="stylesheet" href="/savesa/css/dashboard.css">
  <link rel="stylesheet" href="/savesa/css/form-admin.css">
</head>
<body>
<div class="dashboard-container">

  <!-- ===============================================================
       SIDEBAR
       =============================================================== -->
  <aside class="sidebar">
    <div class="sidebar-header"><h2>Asistencia Virtual</h2></div>

    <nav class="sidebar-nav">
      <a class="sidebar-item"        href="panel.php"><span class="icon">📊</span><span class="label">Panel</span></a>
      <a class="sidebar-item"        href="materias.php"><span class="icon">📚</span><span class="label">Materias</span></a>
      <a class="sidebar-item"        href="crear_materia.php"><span class="icon">➕</span><span class="label">Crear Materia</span></a>
      <a class="sidebar-item"        href="crear_grupo.php"><span class="icon">👥</span><span class="label">Crear Grupo</span></a>
      <a class="sidebar-item active" href="asignar_profesor.php"><span class="icon">🧑‍🏫</span><span class="label">Asignar Profesor</span></a>
      <a class="sidebar-item"        href="inscribir_estudiante.php"><span class="icon">👨‍🎓</span><span class="label">Inscribir Estudiante</span></a>
      <a class="sidebar-item"        href="panel.php?tab=settings"><span class="icon">⚙️</span><span class="label">Configuración</span></a>
    </nav>

    <!-- Footer con avatar y logout --------------------------------- -->
    <div class="sidebar-footer">
      <img src="<?php echo htmlspecialchars($ruta_foto); ?>?v=<?php echo time(); ?>"
           alt="Avatar" class="avatar" />
      <div>
        <div class="user-name"><?php echo htmlspecialchars($mostrar);       ?></div>
        <div class="user-role"><?php echo htmlspecialchars($_SESSION['rol']);?></div>
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
    <header class="overview-header"><h1>Asignar Profesor a Grupo</h1></header>

    <section class="section">
      <!-- Mensaje de respuesta ------------------------------------- -->
      <?php if (isset($mensaje)): ?>
        <p class="msg-<?php echo $msg_tipo; ?>">
          <?php echo htmlspecialchars($mensaje); ?>
        </p>
      <?php endif; ?>

      <!-- Formulario ---------------------------------------------- -->
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

        <!-- Selector de profesor -->
        <label>Profesor:</label>
        <select name="profesor_id" required>
          <option value="">Seleccionar</option>
          <?php foreach ($profesores as $prof): ?>
            <option value="<?php echo $prof['id']; ?>">
              <?php echo htmlspecialchars($prof['nombre_completo']); ?>
            </option>
          <?php endforeach; ?>
        </select>

        <!-- Botón enviar -->
        <button type="submit" class="card-button">Asignar Profesor</button>
      </form>
    </section>
  </main>
</div><!-- /.dashboard-container -->

<!-- ===============================================================
     JS: Ocultar mensaje flash después de 3 s
     =============================================================== -->
<script>
setTimeout(() => {
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
