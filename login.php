<?php
/**
 * ------------------------------------------------------------------------
 * login.php – Formulario de inicio de sesión de SAVESA
 * ------------------------------------------------------------------------
 *
 * Permite la autenticación de los tres roles: Estudiante, Profesor
 * y Administrador.  Valida las credenciales contra la tabla `usuarios`
 * y, si son correctas, crea la sesión y redirige a panel.php.
 *
 * Flujo resumido
 * --------------
 * 1.  Arranca la sesión (`session_start()`).
 * 2.  Si llega POST:
 *     a)  Verifica que estén completos los campos rol, correo y contraseña.
 *     b)  Busca el usuario por correo y rol.
 *     c)  Comprueba la contraseña con `password_verify`.
 *     d)  Registra variables de sesión y redirige al panel.
 * 3.  Si hay errores, se muestran sobre el formulario.
 *
 * Requisitos
 * ----------
 * - PHP 7.4+ y extensión mysqli.
 * - Tabla `usuarios` con columnas: id, correo_electronico, contrasena
 *   (hash), rol, nombre_completo.
 *
 * @author   savesa-team
 * @license  MIT
 * @version  1.0
 */

session_start();
include('conexion.php');

$error = "";   // Mensaje de error que se mostrará en la vista

/* --------------------------------------------------------------------- */
/* 1) PROCESAR EL FORMULARIO (método POST)                                */
/* --------------------------------------------------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /* 1.1) Recoger campos del formulario ---------------------------- */
    $rol   = $_POST['rol']               ?? '';
    $email = $_POST['correo_electronico']?? '';
    $pass  = $_POST['contrasena']        ?? '';

    if ($rol && $email && $pass) {

        /* 1.2) Buscar usuario por correo y rol ---------------------- */
        $stmt = $conn->prepare("
            SELECT id, contrasena, rol, nombre_completo
            FROM   usuarios
            WHERE  correo_electronico = ? AND rol = ?
        ");
        $stmt->bind_param("ss", $email, $rol);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {

            /* 1.3) Verificar contraseña ----------------------------- */
            if (password_verify($pass, $row['contrasena'])) {

                /* 1.4) Credenciales válidas → crear sesión ---------- */
                $_SESSION['usuario_id'] = $row['id'];
                $_SESSION['rol']        = $row['rol'];
                $_SESSION['nombre']     = $row['nombre_completo'];

                header("Location: panel.php");
                exit();

            } else {
                $error = "❌ Contraseña incorrecta.";
            }

        } else {
            $error = "❌ Usuario no encontrado para ese rol.";
        }
        $stmt->close();

    } else {
        $error = "❗ Completa todos los campos.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Login - SAVESA</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- Hoja de estilos específica del login -->
  <link rel="stylesheet" href="/savesa/css/login.css">
</head>
<body>

  <!-- ================================================================
       CONTENEDOR DEL FORMULARIO DE LOGIN
       ================================================================ -->
  <div class="login-container">
    <img src="SAVESA_LOGO.png" alt="Logo SAVESA" class="login-logo">

    <h2>Iniciar Sesión</h2>
    <p class="subtitle">Accede a tu cuenta</p>

    <!-- --------------------------------------------------------------
         FORMULARIO
         -------------------------------------------------------------- -->
    <form method="POST" action="login.php">

      <!-- Selector de rol (radio buttons estilizados como pestañas) -->
      <div class="tabs">
        <label class="tab active">
          <input type="radio" name="rol" value="Estudiante" checked hidden>
          Estudiante
        </label>
        <label class="tab">
          <input type="radio" name="rol" value="Profesor" hidden>
          Profesor
        </label>
        <label class="tab">
          <input type="radio" name="rol" value="Administrador" hidden>
          Administrador
        </label>
      </div>

      <!-- Mensaje de error (si lo hay) -->
      <?php if ($error): ?>
        <div class="error-message"><?php echo $error; ?></div>
      <?php endif; ?>

      <!-- Campo: correo electrónico -->
      <label for="correo_electronico">Correo electrónico</label>
      <input type="email" name="correo_electronico"
             placeholder="Ingresa tu correo" required>

      <!-- Campo: contraseña -->
      <label for="contrasena">Contraseña</label>
      <input type="password" name="contrasena"
             placeholder="Ingresa tu contraseña" required>

      <!-- Botón de envío -->
      <button type="submit">Entrar</button>

      <!-- Enlaces adicionales -->
      <div class="login-links">
        <a class="login-link" href="#">¿Olvidaste tu contraseña?</a>
        <span class="divider">•</span>
        <a class="login-link" href="index.php">Registrarse</a>
      </div>
    </form>
  </div>

  <!-- ================================================================
       JS: Activar/Desactivar pestañas de rol
       ================================================================ -->
  <script>
    /* Al hacer clic en una pestaña, se marca el radio y se resalta */
    document.querySelectorAll('.tab').forEach(tab => {
      tab.addEventListener('click', () => {
        document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
        tab.classList.add('active');
        tab.querySelector('input[type=radio]').checked = true;
      });
    });
  </script>
</body>
</html>
