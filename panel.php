
<?php
session_start();
include('conexion.php');

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}
$rol = $_SESSION['rol'];
$usuario_id = $_SESSION['usuario_id'];
$tab = $_GET['tab'] ?? 'panel';

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

$mensaje = "";

/**
 * ========== PROCESAR FORMULARIO DE JUSTIFICACIÓN (ESTUDIANTE) ==========
 * Solo si el tab es 'justificar', el rol es 'Estudiante' y la petición es POST.
 */
if ($tab === "justificar" && $rol === "Estudiante" && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $grupo_id       = intval($_POST['grupo_id'] ?? 0);
    $fecha_ausencia = $_POST['fecha'] ?? "";
    $just_texto     = trim($_POST['justificacion_texto'] ?? "");
    $nombre_archivo = null;

    // Validaciones básicas
    if ($grupo_id <= 0 || empty($fecha_ausencia)) {
        $mensaje = "❌ Debes seleccionar grupo y fecha válidos.";
    } else {
        // ----- GUARDAR ARCHIVO ADJUNTO (SI SE ENVIÓ) -----
        if (isset($_FILES['justificacion_archivo']) && $_FILES['justificacion_archivo']['error'] === UPLOAD_ERR_OK) {
            $tmp_name  = $_FILES['justificacion_archivo']['tmp_name'];
            $orig_name = $_FILES['justificacion_archivo']['name'];
            $ext       = strtolower(pathinfo($orig_name, PATHINFO_EXTENSION));
            $ext_permitidas = ['pdf','jpg','jpeg','png','doc','docx','gif'];
            if (in_array($ext, $ext_permitidas)) {
                $upload_dir = __DIR__ . '/uploads/justificaciones/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                $nombre_archivo = 'just_' . $usuario_id . '_' . time() . '.' . $ext;
                $destino        = $upload_dir . $nombre_archivo;
                if (!move_uploaded_file($tmp_name, $destino)) {
                    $mensaje = "❌ Error al guardar el archivo adjunto.";
                }
            } else {
                $mensaje = "❌ Extensión de archivo no permitida.";
            }
        }

        // Si no hubo error guardando el archivo (o no se envió), procedemos a insertar/actualizar
        if ($mensaje === "") {
            // Primero verificamos si ya existe un registro para esa fecha, estudiante y grupo:
            $stmtCheck = $conn->prepare("
                SELECT id 
                FROM asistencias 
                WHERE estudiante_id = ? 
                  AND grupo_id = ? 
                  AND fecha = ?
            ");
            $stmtCheck->bind_param("iis", $usuario_id, $grupo_id, $fecha_ausencia);
            $stmtCheck->execute();
            $stmtCheck->bind_result($existe_id);
            $stmtCheck->fetch();
            $stmtCheck->close();

            if ($existe_id) {
                // Si ya existe registro, actualizamos estado, justificacion y archivo_justificacion
                $stmtUpd = $conn->prepare("
                  UPDATE asistencias 
                  SET estado = 'Justificado',
                      justificacion = ?,
                      archivo_justificacion = ?
                  WHERE id = ?
                ");
                $stmtUpd->bind_param("ssi", $just_texto, $nombre_archivo, $existe_id);
                $ok = $stmtUpd->execute();
                $stmtUpd->close();
            } else {
                // Si no existe, INSERT con archivo_justificacion
                $stmtIns = $conn->prepare("
                  INSERT INTO asistencias 
                    (estudiante_id, grupo_id, fecha, estado, justificacion, archivo_justificacion)
                  VALUES (?, ?, ?, 'Justificado', ?, ?)
                ");
                $stmtIns->bind_param("iisss", $usuario_id, $grupo_id, $fecha_ausencia, $just_texto, $nombre_archivo);
                $ok = $stmtIns->execute();
                $stmtIns->close();
            }

            if ($ok) {
                $mensaje = "✅ Justificación enviada correctamente.";
            } else {
                $mensaje = "❌ Error al guardar la justificación en la base de datos.";
            }
        }
    }
}


/* =====================================================================
   PROCESAR FORMULARIO DE CONFIGURACIÓN DE PERFIL (tab = settings)
   ===================================================================== */
if ($tab === "settings" && $_SERVER['REQUEST_METHOD'] === 'POST') {

    /* ─── 2.1) Actualizar zona horaria ───────────────────────────── */
    if (isset($_POST['timezone'])) {
        $tz = $_POST['timezone'];
        $stmtTZ = $conn->prepare("UPDATE usuarios SET zona_horaria = ? WHERE id = ?");
        $stmtTZ->bind_param("si", $tz, $usuario_id);
        $stmtTZ->execute();
        $stmtTZ->close();
        $zona = $tz;                // actualiza la variable en memoria
        $mensaje = "✅ Cambios guardados.";
    }

    /* ─── 2.2) Subir y guardar nueva foto de perfil ──────────────── */
    if (isset($_FILES['profile_photo']) &&
        $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {

        $tmpName  = $_FILES['profile_photo']['tmp_name'];
        $origName = $_FILES['profile_photo']['name'];
        $ext      = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
        $allowed  = ['jpg','jpeg','png','gif','webp'];

        if (in_array($ext, $allowed)) {

            /* Borra la foto anterior (si no es la de “nopicture”) */
            if ($foto_perfil && file_exists(__DIR__."/images/perfiles/$foto_perfil")) {
                @unlink(__DIR__."/images/perfiles/$foto_perfil");
            }

            $newName = "avatar_{$usuario_id}_".time().".$ext";
            $dest    = __DIR__."/images/perfiles/$newName";

            if (move_uploaded_file($tmpName, $dest)) {
                /* Guarda en la BD y refresca variable en memoria */
                $stmtPic = $conn->prepare("UPDATE usuarios SET foto_perfil = ? WHERE id = ?");
                $stmtPic->bind_param("si", $newName, $usuario_id);
                $stmtPic->execute();
                $stmtPic->close();

                $foto_perfil = $newName;
                $ruta_foto   = "/savesa/images/perfiles/$newName";
                $mensaje     = "✅ Foto de perfil actualizada.";
            } else {
                $mensaje = "❌ No pude guardar la imagen.";
            }
        } else {
            $mensaje = "❌ Formato de imagen no permitido.";
        }
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Panel - Asistencia Virtual</title>
  <link rel="stylesheet" href="/savesa/css/dashboard.css">
  <link rel="stylesheet" href="/savesa/css/settings.css">
  <style>
    .dashboard-cards-grid {
      display: flex;
      gap: 34px;
      justify-content: center;
      flex-wrap: wrap;
      margin-bottom: 40px;
    }
    .dashboard-card {
      background: #f8f9fb;
      border-radius: 14px;
      box-shadow: 0 1px 12px rgba(115,92,255,0.05);
      padding: 32px 26px 20px 26px;
      min-width: 330px;
      max-width: 420px;
      flex: 1 1 340px;
      display: flex;
      flex-direction: column;
      justify-content: flex-start;
    }
    .dashboard-card .card-title {
      font-size: 1.21rem;
      margin: 0 0 2px 0;
      font-weight: 600;
      color: #2b2d42;
    }
    .dashboard-card ul {
      margin-top: 15px;
      margin-bottom: 0;
    }
    .dashboard-card li {
      font-size: 1rem;
      margin-bottom: 10px;
    }
    @media (max-width: 950px) {
      .dashboard-cards-grid {
        flex-direction: column;
        gap: 24px;
        align-items: center;
      }
    }
    .dashboard-title {
      text-align: center;
      margin-bottom: 30px;
      margin-top: 12px;
      font-size: 2rem;
    }
    .attendance-access-btn {
      display: inline-block;
      background: #735cff;
      color: #fff;
      padding: 12px 32px;
      font-size: 1.09rem;
      border-radius: 7px;
      margin-top: 8px;
      text-decoration: none;
      transition: 0.2s;
    }
    .attendance-access-btn:hover { background: #5a47cb; }

    .attendance-wrapper {max-width:950px;margin:0 auto;}
    .attendance-tabs {margin-bottom:22px;}
    .tab-btn {padding:8px 18px;border:none;background:#f4f4fb;margin-right:6px;cursor:pointer;}
    .tab-btn.active {border-bottom:2px solid #735cff;color:#735cff;font-weight:600;}
    .attendance-tab {margin-top:20px;}
    .attendance-table {width:100%;border-collapse:collapse;background:#fff;}
    .attendance-table th, .attendance-table td {padding:10px 8px;}
    .avatar-mini {width:32px;height:32px;border-radius:50%;vertical-align:middle;margin-right:6px;}
    .attendance-btn {border:none;padding:7px 13px;margin:1px 2px;border-radius:7px;font-size:0.96rem;cursor:pointer;}
    .attendance-btn.presente {background:#75e6a9;color:#104f3d;}
    .attendance-btn.ausente {background:#fde6e3;color:#b72121;}
    .attendance-btn.justificado {background:#b3a6ff;color:#292343;}
    .attendance-btn:active {opacity:.82;}
    @media (max-width:700px) {
      .attendance-wrapper {padding:2vw;}
      .attendance-table th, .attendance-table td {font-size:0.99rem;}
    }

    /* Estilos para la sección de justificar (estudiante) */
    .profile-message {
      background: #f0f4ff;
      border: 1px solid #d1dbf0;
      padding: 10px;
      border-radius: 6px;
      margin-bottom: 16px;
      color: #2c3e50;
      font-weight: 500;
    }
    .profile-form label {
      display: block;
      margin-top: 12px;
      font-weight: 600;
      color: #2b2d42;
    }
    .profile-form input[type="date"],
    .profile-form input[type="file"],
    .profile-form select,
    .profile-form textarea {
      width: 100%;
      padding: 8px 10px;
      border: 1px solid #ccc;
      border-radius: 6px;
      margin-top: 4px;
      box-sizing: border-box;
      font-size: 1rem;
      color: #2b2d42;
    }
    .profile-save-btn {
      background: #735cff;
      color: #fff;
      padding: 10px 24px;
      border: none;
      border-radius: 6px;
      font-size: 1rem;
      margin-top: 16px;
      cursor: pointer;
      transition: background 0.2s;
    }
    .profile-save-btn:hover {
      background: #5a47cb;
    }
    .justificaciones-list {
      margin-top: 24px;
      border-collapse: collapse;
      width: 100%;
    }
    .justificaciones-list th, .justificaciones-list td {
      border: 1px solid #e1e1e1;
      padding: 8px 10px;
      text-align: left;
      font-size: 0.95rem;
    }
    .justificaciones-list th {
      background: #f4f4fb;
      font-weight: 600;
      color: #2b2d42;
    }
    .justificaciones-list tr:nth-child(even) {
      background: #fafafa;
    }
    .justificaciones-list a {
      color: #735cff;
      text-decoration: none;
    }
    .justificaciones-list a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>
  <div id="root" class="dashboard-container">
    <!-- SIDEBAR -->
    <aside class="sidebar">
      <div class="sidebar-header">
        <h2>Asistencia Virtual</h2>
      </div>
      <nav class="sidebar-nav">
        <a class="sidebar-item <?php if ($tab=='panel') echo 'active'; ?>" href="panel.php?tab=panel">
          <span class="icon">📊</span>
          <span class="label">Panel</span>
        </a>
        <?php if ($rol === 'Administrador'): ?>
          <a class="sidebar-item" href="materias.php">
            <span class="icon">📚</span>
            <span class="label">Materias</span>
          </a>
          <a class="sidebar-item" href="crear_materia.php">
            <span class="icon">➕</span>
            <span class="label">Crear Materia</span>
          </a>
          <a class="sidebar-item" href="crear_grupo.php">
            <span class="icon">👥</span>
            <span class="label">Crear Grupo</span>
          </a>
          <a class="sidebar-item" href="asignar_profesor.php">
            <span class="icon">🧑‍🏫</span>
            <span class="label">Asignar Profesor</span>
          </a>
          <a class="sidebar-item" href="inscribir_estudiante.php">
            <span class="icon">👨‍🎓</span>
            <span class="label">Inscribir Estudiante</span>
          </a>
        <?php elseif ($rol === 'Profesor'): ?>
          <a class="sidebar-item <?php if ($tab=='asistencia') echo 'active'; ?>" href="panel.php?tab=asistencia">
            <span class="icon">✅</span>
            <span class="label">Gestión Asistencia</span>
          </a>
        <?php elseif ($rol === 'Estudiante'): ?>
          <a class="sidebar-item <?php if ($tab=='justificar') echo 'active'; ?>" href="panel.php?tab=justificar">
            <span class="icon">📝</span>
            <span class="label">Justificar Ausencia</span>
          </a>
        <?php endif; ?>
        <a class="sidebar-item <?php if ($tab=='settings') echo 'active'; ?>" href="panel.php?tab=settings">
          <span class="icon">⚙️</span>
          <span class="label">Configuración</span>
        </a>
      </nav>
      <div class="sidebar-footer">
        <img src="<?php echo htmlspecialchars($ruta_foto); ?>?v=<?php echo time(); ?>"
          alt="Avatar"
          class="avatar"
          onerror="this.onerror=null;this.src='/savesa/images/perfiles/nopicture.png';"
        />
        <div>
          <div class="user-name"><?php echo htmlspecialchars($mostrar); ?></div>
          <div class="user-role"><?php echo htmlspecialchars($rol); ?></div>
        </div>
        <form action="logout.php" method="post" style="margin:0;">
          <button class="logout-btn" type="submit">🚪</button>
        </form>
      </div>
    </aside>

    <!-- MAIN CONTENT SEGÚN TAB -->
    <main class="main-content">
      <?php if ($tab == 'panel'): ?>
        <h1 class="dashboard-title">Resumen</h1>
        <div class="dashboard-cards-grid">
          <!-- ADMINISTRADOR -->
          <?php if ($rol === 'Administrador'): ?>
            <div class="dashboard-card">
              <div style="display:flex;align-items:center;gap:12px;">
                <span style="font-size:28px;color:#735CFF;">📝</span>
                <div class="card-title">Actividad Reciente</div>
              </div>
              <ul>
                <?php
                $ultimas_materias = @$conn->query("SELECT nombre, id, fecha_creacion FROM materias ORDER BY fecha_creacion DESC LIMIT 3");
                if ($ultimas_materias && $ultimas_materias->num_rows > 0) {
                  while ($m = $ultimas_materias->fetch_assoc()) {
                    echo "<li><b>Materia creada:</b> ".htmlspecialchars($m['nombre'])." <span style='color:#888;'>(".date('d/m/Y', strtotime($m['fecha_creacion'])).")</span></li>";
                  }
                }
                $ultimos_grupos = @$conn->query("SELECT g.id, g.nombre as grupo, m.nombre as materia FROM grupos g JOIN materias m ON g.materia_id=m.id ORDER BY g.id DESC LIMIT 3");
                if ($ultimos_grupos && $ultimos_grupos->num_rows > 0) {
                  while ($g = $ultimos_grupos->fetch_assoc()) {
                    echo "<li><b>Grupo creado:</b> {$g['id']} (<span style='color:#735cff'>".htmlspecialchars($g['materia'])."</span>)</li>";
                  }
                }
                $ultimas_asignaciones = @$conn->query("
                    SELECT u.nombre_completo as profesor, g.id as grupo
                    FROM materia_profesor mp 
                    JOIN usuarios u ON mp.profesor_id=u.id
                    JOIN grupos g ON mp.grupo_id=g.id
                    ORDER BY mp.grupo_id DESC LIMIT 3
                ");
                if ($ultimas_asignaciones && $ultimas_asignaciones->num_rows > 0) {
                  while ($a = $ultimas_asignaciones->fetch_assoc()) {
                    echo "<li><b>Profesor asignado:</b> ".htmlspecialchars($a['profesor'])." a grupo <b>{$a['grupo']}</b></li>";
                  }
                }
                if (
                    (!$ultimas_materias || $ultimas_materias->num_rows == 0) && 
                    (!$ultimos_grupos || $ultimos_grupos->num_rows == 0) && 
                    (!$ultimas_asignaciones || $ultimas_asignaciones->num_rows == 0)
                  ) {
                  echo '<li style="color:#888;">No hay actividad reciente aún.</li>';
                }
                ?>
              </ul>
            </div>
          <!-- PROFESOR -->
          <?php elseif ($rol === 'Profesor'): ?>
            <div class="dashboard-card">
              <div style="display:flex;align-items:center;gap:12px;">
                <span style="font-size:28px;color:#735CFF;">📝</span>
                <div class="card-title">Actividad Reciente</div>
              </div>
              <ul>
                <?php
                $grupos = @$conn->query("
                  SELECT g.id, g.nombre AS grupo, m.nombre AS materia
                  FROM materia_profesor mp
                  JOIN grupos g ON mp.grupo_id = g.id
                  JOIN materias m ON g.materia_id = m.id
                  WHERE mp.profesor_id = $usuario_id
                  ORDER BY g.id DESC LIMIT 5
                ");
                if ($grupos && $grupos->num_rows > 0) {
                  while ($g = $grupos->fetch_assoc()) {
                    echo "<li><b>Grupo asignado:</b> {$g['id']} (<span style='color:#735cff'>".htmlspecialchars($g['materia'])."</span>)</li>";
                  }
                } else {
                  echo '<li style="color:#888;">No tienes grupos asignados recientemente.</li>';
                }
                ?>
              </ul>
            </div>
          <!-- ESTUDIANTE -->
          <?php elseif ($rol === 'Estudiante'): ?>
            <div class="dashboard-card">
              <div style="display:flex;align-items:center;gap:12px;">
                <span style="font-size:28px;color:#735CFF;">📝</span>
                <div class="card-title">Actividad Reciente</div>
              </div>
              <ul>
                <?php
                $grupos = @$conn->query("
                  SELECT g.id, g.nombre AS grupo, m.nombre AS materia
                  FROM grupo_estudiante ge
                  JOIN grupos g ON ge.grupo_id = g.id
                  JOIN materias m ON g.materia_id = m.id
                  WHERE ge.estudiante_id = $usuario_id
                  ORDER BY g.id DESC LIMIT 5
                ");
                if ($grupos && $grupos->num_rows > 0) {
                  while ($g = $grupos->fetch_assoc()) {
                    echo "<li><b>Inscrito en grupo:</b> {$g['id']} (<span style='color:#735cff'>".htmlspecialchars($g['materia'])."</span>)</li>";
                  }
                } else {
                  echo '<li style="color:#888;">No tienes inscripciones recientes.</li>';
                }
                ?>
              </ul>
            </div>
          <?php endif; ?>
        </div>

      <?php elseif ($tab == 'asistencia' && $rol === 'Profesor'): ?>
        <!-- ==== GESTIÓN ASISTENCIA PROFESOR ==== -->
        <div class="attendance-wrapper">
          <h1 class="dashboard-title">Gestión de Asistencia</h1>
          <div class="attendance-tabs">
            <button class="tab-btn active" onclick="showTab('marcar')">Marcar Asistencia</button>
            <button class="tab-btn" onclick="showTab('justificar')">Justificación de Ausencias</button>
          </div>
          <div id="tab-marcar" class="attendance-tab active">
            <h3 style="margin-bottom:14px;">Clase de Hoy</h3>
            <form method="get" style="margin-bottom:16px;">
              <input type="hidden" name="tab" value="asistencia">
              <label for="grupo_sel" style="margin-right:7px;">Grupo:</label>
              <select id="grupo_sel" name="grupo_id" onchange="this.form.submit()" style="padding:8px 12px;border-radius:7px;">
                <?php
                $grupos = $conn->query("
                  SELECT g.id, g.nombre, m.nombre as materia
                  FROM materia_profesor mp
                  JOIN grupos g ON mp.grupo_id = g.id
                  JOIN materias m ON g.materia_id = m.id
                  WHERE mp.profesor_id = $usuario_id
                ");
                $grupo_id = $_GET['grupo_id'] ?? ($grupos->num_rows > 0 ? $grupos->fetch_assoc()['id'] : '');
                $grupos->data_seek(0);
                while ($gr = $grupos->fetch_assoc()) {
                  echo '<option value="'.$gr['id'].'"'.($grupo_id == $gr['id'] ? ' selected' : '').'>'.htmlspecialchars($gr['materia']).' - '.htmlspecialchars($gr['nombre']).'</option>';
                }
                ?>
              </select>
            </form>
            <div style="overflow-x:auto;">
            <table class="attendance-table">
              <thead>
                <tr>
                  <th>Estudiante</th>
                  <th>Estado</th>
                  <th>Justificación</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody>
                <?php
              if ($grupo_id) {
                $estudiantes = $conn->query("
                  SELECT u.id, u.nombre_completo, u.foto_perfil,
                    a.estado,
                    a.justificacion
                  FROM grupo_estudiante ge
                  JOIN usuarios u ON ge.estudiante_id = u.id
                  LEFT JOIN asistencias a ON a.estudiante_id = u.id AND a.fecha = CURDATE() AND a.grupo_id = $grupo_id
                  WHERE ge.grupo_id = $grupo_id
                ");
                while ($estu = $estudiantes->fetch_assoc()) {
                  echo "<tr>
                    <td><img src='/savesa/images/perfiles/".($estu['foto_perfil']?$estu['foto_perfil']:'nopicture.png')."' class='avatar-mini'> ".htmlspecialchars($estu['nombre_completo'])."</td>
                    <td class='td-estado'>".($estu['estado'] ?? 'Sin marcar')."</td>
                    <td class='td-justificacion'>".(($estu['justificacion'] && trim($estu['justificacion']) != '') ? 'Sí' : '-')."</td>
                    <td>
                      <button class='attendance-btn presente'
                        data-action='Presente'
                        data-id='".$estu['id']."'
                        data-grupo='".$grupo_id."'>Presente</button>
                      <button class='attendance-btn ausente'
                        data-action='Ausente'
                        data-id='".$estu['id']."'
                        data-grupo='".$grupo_id."'>Ausente</button>
                    </td>
                  </tr>";
                }
              } else {
                echo "<tr><td colspan='4'>No tienes grupos asignados.</td></tr>";
              }
              ?>

              </tbody>
            </table>
            </div>
          </div>

          <div id="tab-justificar" class="attendance-tab" style="display:none;">
            <h3 style="margin-bottom:14px;">Justificación de Ausencias</h3>
            <div style="overflow-x:auto;">
            <table class="attendance-table">
              <thead>
                <tr>
                  <th>Estudiante</th>
                  <th>Fecha</th>
                  <th>Estado</th>
                  <th>Justificación</th>
                  <th>Archivo</th>
                </tr>
              </thead>
              <tbody>
              <?php
              if ($grupo_id) {
                $justificaciones = $conn->query("
                  SELECT u.nombre_completo, u.foto_perfil, a.fecha, a.estado, a.justificacion, a.archivo_justificacion
                  FROM asistencias a
                  JOIN usuarios u ON a.estudiante_id = u.id
                  WHERE a.grupo_id = $grupo_id 
                    AND a.estado IN ('Ausente', 'Justificado')
                  ORDER BY a.fecha DESC
                  LIMIT 7
                ");
                if ($justificaciones && $justificaciones->num_rows > 0) {
                  while ($j = $justificaciones->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td><img src='/savesa/images/perfiles/".($j['foto_perfil'] ? $j['foto_perfil'] : 'nopicture.png')."' class='avatar-mini'> ".htmlspecialchars($j['nombre_completo'])."</td>";
                    echo "<td>".date('d/m/Y', strtotime($j['fecha']))."</td>";
                    echo "<td>".htmlspecialchars($j['estado'])."</td>";
                    echo "<td>".($j['justificacion'] ? nl2br(htmlspecialchars($j['justificacion'])) : '-')."</td>";
                    if (!empty($j['archivo_justificacion'])) {
                      $urlArchivo = "/savesa/uploads/justificaciones/".urlencode($j['archivo_justificacion']);
                      echo "<td><a href=\"{$urlArchivo}\" target=\"_blank\">Ver adjunto</a></td>";
                    } else {
                      echo "<td>-</td>";
                    }
                    echo "</tr>";
                  }
                } else {
                  echo "<tr><td colspan='5'>No hay justificaciones recientes.</td></tr>";
                }
              } else {
                echo "<tr><td colspan='5'>Selecciona un grupo para ver las justificaciones.</td></tr>";
              }
              ?>
              </tbody>
            </table>
            </div>
          </div>
        </div>

        <script>
        function showTab(tab) {
          document.getElementById('tab-marcar').style.display = (tab==='marcar') ? '' : 'none';
          document.getElementById('tab-justificar').style.display = (tab==='justificar') ? '' : 'none';
          var btns = document.querySelectorAll('.tab-btn');
          btns[0].classList.toggle('active', tab==='marcar');
          btns[1].classList.toggle('active', tab==='justificar');
        }

        document.addEventListener('DOMContentLoaded', function() {
          document.querySelectorAll('.attendance-btn').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
              e.preventDefault();
              const estudiante_id = this.dataset.id;
              const grupo_id      = this.dataset.grupo;
              const estado        = this.dataset.action;
              const btnActual     = this;
              fetch('gestion_asistencia.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: new URLSearchParams({
                  estudiante_id, grupo_id, estado
                })
              })
              .then(resp => resp.json())
              .then(data => {
                if (data.ok) {
                  const row = btnActual.closest('tr');
                  row.querySelector('.td-estado').textContent = estado;
                  row.querySelector('.td-justificacion').textContent = '-';
                  row.style.background = '#e2ffe8';
                  setTimeout(()=>{row.style.background='';}, 600);
                } else {
                  alert(data.msg || "Error guardando asistencia.");
                }
              })
              .catch(() => {
                alert("Error de conexión.");
              });
            });
          });
        });
        </script>

      <?php elseif ($tab == 'justificar' && $rol === 'Estudiante'): ?>
        <!-- ========== SECCIÓN JUSTIFICAR AUSENCIA (ESTUDIANTE) ========== -->
        <div class="attendance-wrapper">
          <h1 class="dashboard-title">Justificar Ausencia</h1>

          <?php if (!empty($mensaje)): ?>
            <div class="profile-message"><?php echo htmlspecialchars($mensaje); ?></div>
          <?php endif; ?>

          <form class="profile-form" method="POST" enctype="multipart/form-data" action="panel.php?tab=justificar">
            <label for="grupo_id">Grupo:</label>
            <select id="grupo_id" name="grupo_id" required>
              <?php
              // Traer los grupos en los que está inscrito el estudiante
              $grupos = $conn->query("
                SELECT g.id, g.nombre, m.nombre AS materia
                FROM grupo_estudiante ge
                JOIN grupos g ON ge.grupo_id = g.id
                JOIN materias m ON g.materia_id = m.id
                WHERE ge.estudiante_id = $usuario_id
              ");
              while ($gr = $grupos->fetch_assoc()) {
                echo '<option value="'.$gr['id'].'">'.htmlspecialchars($gr['materia']).' - '.htmlspecialchars($gr['nombre']).'</option>';
              }
              ?>
            </select>

            <label for="fecha">Fecha de ausencia:</label>
            <input type="date" id="fecha" name="fecha" max="<?php echo date('Y-m-d'); ?>" required>

            <label for="justificacion_texto">Motivo (texto):</label>
            <textarea id="justificacion_texto" name="justificacion_texto" rows="3" maxlength="600" placeholder="Describe brevemente el motivo de tu ausencia (opcional)"></textarea>

            <label for="justificacion_archivo">Adjuntar archivo o imagen (opcional):</label>
            <input type="file" id="justificacion_archivo" name="justificacion_archivo"
                   accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.gif,image/*,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document">

            <button type="submit" class="profile-save-btn">Enviar Justificación</button>
          </form>

          <!-- Lista de las últimas 7 justificaciones del estudiante -->
          <h2 style="margin-top:30px; margin-bottom:12px; font-size:1.25rem; color:#2b2d42;">
            Tus últimas justificaciones
          </h2>
          <div style="overflow-x:auto;">
            <table class="justificaciones-list">
              <thead>
                <tr>
                  <th>Grupo</th>
                  <th>Fecha</th>
                  <th>Estado</th>
                  <th>Motivo (texto)</th>
                  <th>Archivo</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $sqlList = "
                  SELECT 
                    a.grupo_id,
                    g.nombre        AS grupo_nombre,
                    m.nombre        AS materia,
                    a.fecha,
                    a.estado,
                    a.justificacion,
                    a.archivo_justificacion
                  FROM asistencias a
                  JOIN grupos   g ON a.grupo_id   = g.id
                  JOIN materias m ON g.materia_id = m.id
                  WHERE a.estudiante_id = $usuario_id
                    AND a.estado = 'Justificado'
                  ORDER BY a.fecha DESC
                  LIMIT 7
                ";
                $rsList = $conn->query($sqlList);

                if ($rsList && $rsList->num_rows > 0) {
                  while ($row = $rsList->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>".htmlspecialchars($row['materia'])." - ".htmlspecialchars($row['grupo_nombre'])."</td>";
                    echo "<td>".date('d/m/Y', strtotime($row['fecha']))."</td>";
                    echo "<td>".htmlspecialchars($row['estado'])."</td>";
                    echo "<td>".( $row['justificacion'] 
                                  ? nl2br(htmlspecialchars($row['justificacion'])) 
                                  : '-' ) ."</td>";

                    if (!empty($row['archivo_justificacion'])) {
                      $urlArchivo = "/savesa/uploads/justificaciones/".urlencode($row['archivo_justificacion']);
                      echo "<td><a href=\"{$urlArchivo}\" target=\"_blank\">Ver adjunto</a></td>";
                    } else {
                      echo "<td>-</td>";
                    }

                    echo "</tr>";
                  }
                } else {
                  echo "<tr>
                          <td colspan='5' style='text-align:center; color:#888;'>
                            Aún no tienes justificaciones registradas.
                          </td>
                        </tr>";
                }
                ?>
              </tbody>
            </table>
          </div>
        </div>

      <?php elseif ($tab == 'settings'): ?>
        <!-- ========== SECCIÓN CONFIGURACIÓN/PERFIL ========== -->
        <div class="settings-wrapper">
          <div class="profile-header" style="text-align:center;">
            <h1 class="profile-title">Configuración de Perfil</h1>
          </div>
          <div class="profile-content" style="text-align:center">
            <h2 class="profile-main-title">Información Personal</h2>
            <?php if ($mensaje): ?>
              <div class="profile-message"><?php echo htmlspecialchars($mensaje); ?></div>
            <?php endif; ?>
            <form class="profile-form" method="POST" action="panel.php?tab=settings" enctype="multipart/form-data">
              <div class="profile-photo-box">
                <img src="<?php echo htmlspecialchars($ruta_foto); ?>?v=<?php echo time(); ?>"
                     alt="Foto de perfil"
                     class="profile-photo"
                     onerror="this.onerror=null;this.src='/savesa/images/perfiles/nopicture.png';"
                />
                <label class="profile-photo-btn" tabindex="0">
                  Cambiar foto
                  <input type="file" id="profile_photo" name="profile_photo" accept="image/*" style="display:none;" onchange="this.form.submit()">
                </label>
              </div>
              <label for="fullname">Nombre completo</label>
              <input type="text" id="fullname" name="fullname" value="<?php echo htmlspecialchars($nombre); ?>" readonly>

              <label for="email">Correo electrónico</label>
              <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($correo); ?>" readonly>

              <label for="timezone">Zona Horaria</label>
              <select id="timezone" name="timezone">
                <option<?php if($zona=="GMT+0") echo " selected"; ?>>GMT+0</option>
                <option<?php if($zona=="GMT-3 (Argentina, Brasil)") echo " selected"; ?>>GMT-3 (Argentina, Brasil)</option>
                <option<?php if($zona=="GMT-5 (Colombia, Perú, México)") echo " selected"; ?>>GMT-5 (Colombia, Perú, México)</option>
                <option<?php if($zona=="GMT-6 (Centroamérica)") echo " selected"; ?>>GMT-6 (Centroamérica)</option>
              </select>
              <button type="submit" class="profile-save-btn">Guardar Cambios</button>
            </form>
          </div>
        </div>
        <script>
        // Oculta el mensaje de guardado después de 2.5 segundos
        window.onload = function() {
          var msg = document.querySelector('.profile-message');
          if(msg) {
            setTimeout(function() {
              msg.style.display = 'none';
            }, 2500);
          }
        }
        </script>
      <?php endif; ?>
    </main>
  </div>
</body>
</html>
