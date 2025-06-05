<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Crear Cuenta - SAVESA</title>

  <!--
    Hoja de estilos global.
    Asegúrate de que /savesa/css/styles.css exista y contenga
    los estilos para:
      .register-container, .register-logo, .tab, .active, .btn, etc.
  -->
  <link rel="stylesheet" href="/savesa/css/styles.css" />
</head>
<body>

  <!-- ===============================================================
       CONTENEDOR PRINCIPAL DE REGISTRO
       =============================================================== -->
  <div class="register-container">

    <!-- Logo corporativo (PNG) ------------------------------------ -->
    <!-- Ajusta la ruta si mueves el archivo -->
    <img src="SAVESA_LOGO.png" alt="Logo SAVESA" class="register-logo" />

    <h2>Crear Cuenta</h2>
    <p class="subtitle">Selecciona tu rol</p>

    <!-- ------------------------------------------------------------
         FORMULARIO DE REGISTRO
         send.php es el script que almacenará al usuario en la BD
         ------------------------------------------------------------ -->
    <form action="send.php" method="POST" class="register-form">

      <!-- Mensaje flash de éxito (se muestra si ?registro=exito) -->
      <?php if (isset($_GET['registro']) && $_GET['registro'] === 'exito') : ?>
        <div class="success-message">¡Registro exitoso!</div>
      <?php endif; ?>

      <!-- Selector de rol (radio buttons dentro de <label> .tab) -->
      <div class="roles">
        <label class="tab active">
          <input type="radio" name="rol" value="Estudiante" checked hidden />
          Estudiante
        </label>
        <label class="tab">
          <input type="radio" name="rol" value="Profesor" hidden />
          Profesor
        </label>
        <label class="tab">
          <input type="radio" name="rol" value="Administrador" hidden />
          Administrador
        </label>
      </div>

      <!-- Campos de datos personales ------------------------------ -->
      <label for="nombre_completo">Nombre completo</label>
      <input  type="text" id="nombre_completo" name="nombre_completo"
              placeholder="Ingresa tu nombre completo" required />

      <label for="correo_electronico">Correo electrónico</label>
      <input  type="email" id="correo_electronico" name="correo_electronico"
              placeholder="Ingresa tu correo electrónico" required />

      <label for="id_carnet">Número de ID / Carnet</label>
      <input  type="text" id="id_carnet" name="id_carnet"
              placeholder="Ingresa tu número de ID o carnet" required />

      <label for="contrasena">Contraseña</label>
      <input  type="password" id="contrasena" name="contrasena"
              placeholder="Crea una contraseña" required />

      <label for="confirmar_contrasena">Confirmar contraseña</label>
      <input  type="password" id="confirmar_contrasena" name="confirmar_contrasena"
              placeholder="Repite tu contraseña" required />

      <!-- Botón principal ------------------------------------------ -->
      <button type="submit" class="btn">Registrarse</button>

      <!-- Enlace alternativo a login ------------------------------ -->
      <p class="login-link">
        ¿Ya tienes una cuenta?
        <a href="login.php">Iniciar sesión</a>
      </p>
    </form>
  </div><!-- /register-container -->

  <!-- ===============================================================
       JS #1: Cambiar la pestaña activa de rol
       =============================================================== -->
  <script>
    /**
     * Al hacer clic en un <label class="tab">:
     * 1) Elimina la clase 'active' de todos los tabs.
     * 2) Agrega 'active' al tab clicado.
     * 3) Marca el <input type="radio"> interno como seleccionado.
     */
    document.querySelectorAll('.tab').forEach(tab => {
      tab.addEventListener('click', () => {
        document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
        tab.classList.add('active');
        const radio = tab.querySelector('input[type="radio"]');
        if (radio) radio.checked = true;
      });
    });
  </script>

  <!-- ===============================================================
       JS #2: Animar y ocultar el mensaje de éxito
       =============================================================== -->
  <script>
    const successBox = document.querySelector('.success-message');
    if (successBox) {
      // Desvanece y desplaza el mensaje tras 3 s
      setTimeout(() => {
        successBox.style.opacity = '0';
        successBox.style.transform = 'translateY(-10px)';
      }, 3000);

      // Lo elimina del DOM a los 4 s (opcional)
      setTimeout(() => successBox.remove(), 4000);

      // Limpia el parámetro ?registro=exito de la URL sin recargar
      const params = new URLSearchParams(window.location.search);
      if (params.get('registro') === 'exito') {
        window.history.replaceState({}, document.title, window.location.pathname);
      }
    }
  </script>
</body>
</html>
