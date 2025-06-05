<?php
/**
 * ------------------------------------------------------------------------
 * materias.php – Listado, edición y eliminación de materias
 * ------------------------------------------------------------------------
 *
 * Visibilidad          : Solo Administradores
 * Funciones principales
 * =====================
 * 1. Ver todas las materias registradas.
 * 2. Crear, editar o eliminar materias (acciones rápidas en la tabla).
 * 3. Mostrar mensajes “flash” de éxito tras cada acción.
 *
 * Flujo general
 * -------------
 * 1. Verifica la sesión y el rol (“Administrador”).  
 * 2. Recupera la foto + nombre del usuario para el sidebar.  
 * 3. Si viene `?eliminar=id` ⇒ elimina la materia y redirige.  
 * 4. Si se envía POST con `editar_id` ⇒ actualiza el nombre y redirige.  
 * 5. Obtiene el listado de materias para la tabla.
 *
 * Requisitos
 * ----------
 *  - PHP 7.4+ y extensión mysqli.
 *  - Tabla `materias` con columnas `id`, `nombre`.
 *
 * @author  Savesa Team
 * @license  MIT
 * @version  1.0
 */

include('conexion.php');
session_start();

/* --------------------------------------------------------------------- */
/* 1) CONTROL DE ACCESO                                                  */
/* --------------------------------------------------------------------- */
if ($_SESSION['rol'] !== 'Administrador') {
    /* Cualquier rol distinto de Administrador se redirige al login */
    header('Location: login.php');
    exit();
}

/* --------------------------------------------------------------------- */
/* 2) DATOS DEL USUARIO PARA EL SIDEBAR                                  */
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

/* Nombre “abreviado” (máx. 2 palabras) -------------------------------- */
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
/* 3) ACCIÓN: ELIMINAR MATERIA                                           */
/* --------------------------------------------------------------------- */
if (isset($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);
    $conn->query("DELETE FROM materias WHERE id = $id");
    header("Location: materias.php?msg=eliminada");
    exit();
}

/* --------------------------------------------------------------------- */
/* 4) ACCIÓN: EDITAR MATERIA                                             */
/* --------------------------------------------------------------------- */
if (isset($_POST['editar_id']) && !empty($_POST['nombre'])) {
    $id     = intval($_POST['editar_id']);
    $nombre = trim($_POST['nombre']);

    $stmt = $conn->prepare("UPDATE materias SET nombre = ? WHERE id = ?");
    $stmt->bind_param("si", $nombre, $id);
    $stmt->execute();
    $stmt->close();

    header("Location: materias.php?msg=editada");
    exit();
}

/* --------------------------------------------------------------------- */
/* 5) LISTAR MATERIAS                                                    */
/* --------------------------------------------------------------------- */
$materias = [];
$res = $conn->query("SELECT id, nombre FROM materias");
while ($row = $res->fetch_assoc()) $materias[] = $row;
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Materias</title>

  <!-- Hojas de estilo globales -->
  <link rel="stylesheet" href="/savesa/css/dashboard.css">
  <link rel="stylesheet" href="/savesa/css/form-admin.css">

  <!-- Estilos específicos de esta página ---------------------------- -->
  <style>
    /* ---------- Encabezado (título + botón) ------------------------ */
    .materias-header {
      display: flex; flex-direction: column; align-items: center;
      gap: 16px; margin-bottom: 24px;
    }
    .materias-header h1 { font-size: 2rem; font-weight: 700; margin: 0 0 2px; }
    .materias-header .card-button {
      width: 220px; text-align: center; font-size: 1.08rem;
    }

    /* ---------- Contenedor tabla centrado ------------------------- */
    .section {
      display: flex; flex-direction: column; align-items: center;
      max-width: 1100px;
    }
    .section table {
      width: 95%; max-width: 1000px; min-width: 340px; margin-top: 0;
    }
    .section th, .section td { text-align: center; }
    .section th:first-child, .section td:first-child { text-align: left; }

    /* ---------- Área principal ------------------------------------ */
    .main-content {
      width: 100%; display: flex; flex-direction: column;
      align-items: center; padding: 24px 0;
    }

    /* ---------- Responsive (móviles) ------------------------------ */
    @media (max-width: 700px) {
      .section table { width: 100%; min-width: 0; }
      .materias-header .card-button { width: 100%; }
    }
  </style>
</head>
<body>
<div class="dashboard-container">

<!-- ================================================================
     SIDEBAR
     ================================================================ -->
<aside class="sidebar">
  <div class="sidebar-header"><h2>Asistencia Virtual</h2></div>

  <!-- Menú de administración --------------------------------------- -->
  <nav class="sidebar-nav">
    <a class="sidebar-item"        href="panel.php"><span class="icon">📊</span><span class="label">Panel</span></a>
    <a class="sidebar-item active" href="materias.php"><span class="icon">📚</span><span class="label">Materias</span></a>
    <a class="sidebar-item"        href="crear_materia.php"><span class="icon">➕</span><span class="label">Crear Materia</span></a>
    <a class="sidebar-item"        href="crear_grupo.php"><span class="icon">👥</span><span class="label">Crear Grupo</span></a>
    <a class="sidebar-item"        href="asignar_profesor.php"><span class="icon">🧑‍🏫</span><span class="label">Asignar Profesor</span></a>
    <a class="sidebar-item"        href="inscribir_estudiante.php"><span class="icon">👨‍🎓</span><span class="label">Inscribir Estudiante</span></a>
    <a class="sidebar-item"        href="panel.php?tab=settings"><span class="icon">⚙️</span><span class="label">Configuración</span></a>
  </nav>

  <!-- Pie del sidebar (avatar + logout) ---------------------------- -->
  <div class="sidebar-footer">
    <img src="<?php echo htmlspecialchars($ruta_foto); ?>?v=<?php echo time(); ?>"
         alt="Avatar" class="avatar" />
    <div>
      <div class="user-name"><?php echo htmlspecialchars($mostrar);      ?></div>
      <div class="user-role"><?php  echo htmlspecialchars($_SESSION['rol']); ?></div>
    </div>
    <form action="logout.php" method="post" style="margin:0;">
      <button class="logout-btn" type="submit">🚪</button>
    </form>
  </div>
</aside>
<!-- /SIDEBAR -->

<!-- ================================================================
     CONTENIDO PRINCIPAL
     ================================================================ -->
<main class="main-content">

  <!-- ---------- Encabezado (Título + botón Nueva materia) --------- -->
  <div class="materias-header">
    <h1>Materias</h1>
    <a href="crear_materia.php" class="card-button" style="text-decoration:none;">+ Nueva Materia</a>
  </div>

  <!-- ---------- Tabla de materias -------------------------------- -->
  <section class="section">

    <!-- Mensajes flash -------------------------------------------- -->
    <?php if (isset($_GET['msg'])): ?>
      <?php if      ($_GET['msg'] == 'creada')   : ?><p class="msg-success">Materia creada correctamente.</p>
      <?php elseif ($_GET['msg'] == 'editada')   : ?><p class="msg-success">Materia editada.</p>
      <?php elseif ($_GET['msg'] == 'eliminada') : ?><p class="msg-success">Materia eliminada.</p>
      <?php endif; ?>
    <?php endif; ?>

    <!-- Estructura de la tabla (ID, Nombre, Acciones) ------------- -->
    <table>
      <tr><th>ID</th><th>Nombre</th><th>Acciones</th></tr>

      <?php foreach ($materias as $mat): ?>
        <tr>
          <!-- Columna ID ------------------------------------------ -->
          <td><?php echo $mat['id']; ?></td>

          <!-- Columna Nombre (editable in-line si ?editar=ID) ------ -->
          <td>
            <?php if (isset($_GET['editar']) && $_GET['editar'] == $mat['id']): ?>
              <form method="post" style="display:inline;">
                <input type="hidden" name="editar_id" value="<?php echo $mat['id']; ?>">
                <input type="text" name="nombre"
                       value="<?php echo htmlspecialchars($mat['nombre']); ?>" required>
                <button type="submit" class="card-button btn-small">Guardar</button>
              </form>
            <?php else: ?>
              <?php echo htmlspecialchars($mat['nombre']); ?>
            <?php endif; ?>
          </td>

          <!-- Columna Acciones (Editar / Eliminar) ---------------- -->
          <td>
            <div class="table-actions">
              <a href="materias.php?editar=<?php echo $mat['id']; ?>"
                 class="card-button btn-small">Editar</a>
              <a href="materias.php?eliminar=<?php echo $mat['id']; ?>"
                 onclick="return confirm('¿Eliminar?');"
                 class="card-button btn-small btn-danger">Eliminar</a>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
    </table>
  </section>
</main>
</div><!-- /.dashboard-container -->

<!-- ================================================================
     JS: Ocultar mensajes flash después de 3 s
     ================================================================ -->
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
