<?php
include('conexion.php');
session_start();

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
// PROCESAR FORMULARIO
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['nombre'])) {
    $nombre = trim($_POST['nombre']);
    // Revisar si ya existe
    $stmt = $conn->prepare("SELECT COUNT(*) FROM materias WHERE nombre = ?");
    $stmt->bind_param("s", $nombre);
    $stmt->execute();
    $stmt->bind_result($existe);
    $stmt->fetch();
    $stmt->close();
    if ($existe) {
        $mensaje = "Ya existe una materia con ese nombre.";
        $msg_tipo = "error";
    } else {
        $stmt = $conn->prepare("INSERT INTO materias (nombre) VALUES (?)");
        $stmt->bind_param("s", $nombre);
        $stmt->execute();
        $stmt->close();
        header("Location: materias.php?msg=creada");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Crear Materia</title>
  <link rel="stylesheet" href="/savesa/css/dashboard.css">
  <link rel="stylesheet" href="/savesa/css/form-admin.css">
  <style>
    .msg-success {
      background: #d5fbe2;
      color: #217a38;
      border-radius: 8px;
      padding: 13px 16px;
      margin-bottom: 20px;
      border-left: 4px solid #40ce82;
      font-weight: 500;
    }
    .msg-error {
      background: #f8d7da;
      color: #a94442;
      border-radius: 8px;
      padding: 13px 16px;
      margin-bottom: 20px;
      border-left: 4px solid #e53935;
      font-weight: 500;
    }
    .form-card {
      background: #fff;
      padding: 2.3rem 1.3rem 2.1rem 1.3rem;
      border-radius: 13px;
      box-shadow: 0 2px 10px 0 #2221  ;
      max-width: 620px;
      margin-top: 1.3rem;
    }
  </style>
</head>
<body>
  <div class="dashboard-container">
    <!-- SIDEBAR DIRECTO -->
    <aside class="sidebar">
      <div class="sidebar-header">
        <h2>Asistencia Virtual</h2>
      </div>
      <nav class="sidebar-nav">
        <a class="sidebar-item" href="panel.php"><span class="icon">📊</span><span class="label">Panel</span></a>
        <a class="sidebar-item" href="materias.php"><span class="icon">📚</span><span class="label">Materias</span></a>
        <a class="sidebar-item active" href="crear_materia.php"><span class="icon">➕</span><span class="label">Crear Materia</span></a>
        <a class="sidebar-item" href="crear_grupo.php"><span class="icon">👥</span><span class="label">Crear Grupo</span></a>
        <a class="sidebar-item" href="asignar_profesor.php"><span class="icon">🧑‍🏫</span><span class="label">Asignar Profesor</span></a>
        <a class="sidebar-item" href="inscribir_estudiante.php"><span class="icon">👨‍🎓</span><span class="label">Inscribir Estudiante</span></a>
        <a class="sidebar-item" href="panel.php?tab=settings"><span class="icon">⚙️</span><span class="label">Configuración</span></a>
      </nav>
      <div class="sidebar-footer">
        <img src="<?php echo htmlspecialchars($ruta_foto); ?>?v=<?php echo time(); ?>" alt="Avatar" class="avatar" />
        <div>
          <div class="user-name"><?php echo htmlspecialchars($mostrar); ?></div>
          <div class="user-role"><?php echo htmlspecialchars($_SESSION['rol']); ?></div>
        </div>
        <form action="logout.php" method="post" style="margin:0;">
          <button class="logout-btn" type="submit">🚪</button>
        </form>
      </div>
    </aside>
    <!-- FIN SIDEBAR -->
    <main class="main-content" style="display: flex; flex-direction: column; align-items: center; justify-content: flex-start;">
  <header class="overview-header" style="text-align:center;"><h1>Crear Nueva Materia</h1></header>
  <section class="section" style="background:none; box-shadow:none; padding-top:32px; padding-bottom:32px; min-width:350px; max-width:430px;">
      <?php if (isset($mensaje)): ?>
        <div class="msg-<?php echo $msg_tipo ?? 'success'; ?>" id="autohide-msg"><?php echo htmlspecialchars($mensaje); ?></div>
      <?php endif; ?>
      <form method="post" action="" style="width:100%; margin:0 auto; display:flex; flex-direction:column; gap:18px;">
        <label>Nombre de la materia:</label>
        <input type="text" name="nombre" required style="width:100%;padding:10px;border-radius:var(--radius);border:1px solid #ddd;">
        <button type="submit" class="card-button" style="width:100%;">Crear Materia</button>
      </form>
  </section>
</main>
  </div>
  <script>
    window.addEventListener('DOMContentLoaded', function() {
      var msg = document.getElementById('autohide-msg');
      if(msg) setTimeout(() => { msg.style.display = 'none'; }, 3000);
    });
  </script>
</body>
</html>
