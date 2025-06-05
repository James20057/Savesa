<?php
/**
 * ------------------------------------------------------------------------
 * crear_grupo.php – Registrar un nuevo grupo para una materia
 * ------------------------------------------------------------------------
 *
 * Visibilidad          : Solo Administradores
 *
 * Funcionalidades
 * ================
 * 1.  Despliega un formulario para elegir la materia y asignar un nombre
 *     al grupo.
 * 2.  Evita duplicados comprobando (nombre, materia_id).
 * 3.  Inserta el grupo en `grupos` y muestra mensaje de confirmación.
 *
 * Flujo
 * -----
 * 1.  Verifica la sesión y el rol (Administrador).
 * 2.  Recupera datos de usuario (foto + nombre) para el sidebar.
 * 3.  Carga la lista de materias para el selector.
 * 4.  Si llega POST con materia y nombre → valida duplicado, inserta
 *     y muestra mensaje en la misma vista.
 *
 * Requisitos
 * ----------
 * - PHP 7.4+ y extensión mysqli.
 * - Tablas: materias, grupos.
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

/* Nombre abreviado (máx. 2 palabras) ---------------------------------- */
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
/* 3) LISTADO DE MATERIAS (para el <select>)                             */
/* --------------------------------------------------------------------- */
$materias = [];
$res = $conn->query("SELECT id, nombre FROM materias");
while ($row = $res->fetch_assoc()) $materias[] = $row;

/* --------------------------------------------------------------------- */
/* 4) PROCESAR ENVÍO DEL FORMULARIO                                      */
/* --------------------------------------------------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' &&
    !empty($_POST['materia_id']) &&
    !empty($_POST['nombre'])) {

    $materia_id = intval($_POST['materia_id']);
    $nombre     = trim($_POST['nombre']);

    /* 4.1) Comprobar duplicado (mismo nombre en la misma materia) -- */
    $stmt = $conn->prepare("
        SELECT COUNT(*)
        FROM   grupos
        WHERE  nombre     = ?
          AND  materia_id = ?
    ");
    $stmt->bind_param("si", $nombre, $materia_id);
    $stmt->execute();
    $stmt->bind_result($existe);
    $stmt->fetch();
    $stmt->close();

    if ($existe) {
        $mensaje  = "Ya existe un grupo con ese nombre en la misma materia.";
        $msg_tipo = "error";
    } else {
        /* 4.2) Insertar nuevo grupo -------------------------------- */
        $stmt = $conn->prepare("
            INSERT INTO grupos (materia_id, nombre)
            VALUES (?, ?)
        ");
        $stmt->bind_param("is", $materia_id, $nombre);
        $stmt->execute();
        $stmt->close();

        $mensaje  = "Grupo creado correctamente.";
        $msg_tipo = "success";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Crear Grupo</title>

  <!-- CSS globales -->
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

    <!-- Menú administrador ----------------------------------------- -->
    <nav class="sidebar-nav">
      <a class="sidebar-item"        href="panel.php"><span class="icon">📊</span><span class="label">Panel</span></a>
      <a class="sidebar-item"        href="materias.php"><span class="icon">📚</span><span class="label">Materias</span></a>
      <a class="sidebar-item"        href="crear_materia.php"><span class="icon">➕</span><span class="label">Crear Materia</span></a>
      <a class="sidebar-item active" href="crear_grupo.php"><span class="icon">👥</span><span class="label">Crear Grupo</span></a>
      <a class="sidebar-item"        href="asignar_profesor.php"><span class="icon">🧑‍🏫</span><span class="label">Asignar Profesor</span></a>
      <a class="sidebar-item"        href="inscribir_estudiante.php"><span class="icon">👨‍🎓</span><span class="label">Inscribir Estudiante</span></a>
      <a class="sidebar-item"        href="panel.php?tab=settings"><span class="icon">⚙️</span><span class="label">Configuración</span></a>
    </nav>

    <!-- Avatar + logout ------------------------------------------- -->
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
    <header class="overview-header"><h1>Crear Nuevo Grupo</h1></header>

    <section class="section">
      <!-- Mensaje de feedback -------------------------------------- -->
      <?php if (isset($mensaje)): ?>
        <p class="msg-<?php echo $msg_tipo; ?>">
          <?php echo htmlspecialchars($mensaje); ?>
        </p>
      <?php endif; ?>

      <!-- Formulario de creación ----------------------------------- -->
      <form method="post" action="">
        <!-- Selector de materia -->
        <label>Materia:</label>
        <select name="materia_id" required>
          <option value="">Seleccionar</option>
          <?php foreach ($materias as $mat): ?>
            <option value="<?php echo $mat['id']; ?>">
              <?php echo htmlspecialchars($mat['nombre']); ?>
            </option>
          <?php endforeach; ?>
        </select>

        <!-- Nombre del grupo -->
        <label>Nombre del grupo:</label>
        <input type="text" name="nombre" required />

        <!-- Botón enviar -->
        <button type="submit" class="card-button">Crear Grupo</button>
      </form>
    </section>
  </main>
</div><!-- /.dashboard-container -->

<!-- ===============================================================
     JS: Ocultar mensaje flash tras 3 s
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
