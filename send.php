<?php
session_start();
include('conexion.php');

// Desactivamos temporalmente los REPORTES de error de MySQLi, 
// para poder capturarlos en try/catch
mysqli_report(MYSQLI_REPORT_OFF);

$mensaje = "";   // Aquí almacenaremos cualquier mensaje de error o éxito

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1) Validar que los campos estén presentes
    if (
        isset($_POST['rol'], $_POST['nombre_completo'], $_POST['correo_electronico'], $_POST['id_carnet'], $_POST['contrasena'], $_POST['confirmar_contrasena']) &&
        !empty($_POST['rol']) &&
        !empty($_POST['nombre_completo']) &&
        !empty($_POST['correo_electronico']) &&
        !empty($_POST['id_carnet']) &&
        !empty($_POST['contrasena']) &&
        $_POST['contrasena'] === $_POST['confirmar_contrasena']
    ) {
        $rol    = $_POST['rol'];
        $nombre = trim($_POST['nombre_completo']);
        $email  = trim($_POST['correo_electronico']);
        $id_carnet = trim($_POST['id_carnet']);
        $password  = password_hash($_POST['contrasena'], PASSWORD_DEFAULT);

        // 2) Preparar la inserción usando try/catch
        try {
            $stmt = $conn->prepare("
                INSERT INTO usuarios 
                    (rol, nombre_completo, correo_electronico, id_carnet, contrasena)
                VALUES (?, ?, ?, ?, ?)
            ");
            if (!$stmt) {
                // Si falla la preparación, lanzamos excepción manualmente
                throw new Exception("Error preparando consulta: " . $conn->error);
            }

            $stmt->bind_param("sssss", $rol, $nombre, $email, $id_carnet, $password);

            // Ejecutamos la inserción
            if (!$stmt->execute()) {
                // Si falla la ejecución, lanzamos excepción con el error
                throw new mysqli_sql_exception($stmt->error, $stmt->errno);
            }

            // Si llegamos aquí, inserción exitosa
            $stmt->close();
            $conn->close();

            // Redirigimos al index con un parámetro de éxito (o puedes mostrar mensaje)
            header("Location: index.php?registro=exito");
            exit();

        } catch (mysqli_sql_exception $e) {
            // Si es un error de SQL (MySQLi), podemos revisar el código de error
            if ($e->getCode() === 1062) {
                // 1062 = Duplicate entry
                $mensaje = "❗ Ya existe un usuario con ese ID de carnet.";
            } else {
                $mensaje = "❌ Error de base de datos: " . $e->getMessage();
            }
        } catch (Exception $e) {
            // Cualquier otra excepción
            $mensaje = "❌ “Excepción”: " . $e->getMessage();
        }

    } else {
        // Si faltan campos o las contraseñas no coinciden
        $mensaje = "❗ Verifica que todos los campos estén completos y que las contraseñas coincidan.";
    }
} else {
    // Si entran por GET
    $mensaje = "❌ Acceso no permitido.";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Registro de usuario</title>
  <style>
    /* +––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––+
    |   Estilos muy básicos para la notificación de error / éxito     |
    +––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––+ */
    .notification {
      max-width: 500px;
      margin: 20px auto;
      padding: 12px 18px;
      border-radius: 5px;
      font-family: Arial, sans-serif;
      font-size: 0.95rem;
      box-sizing: border-box;
    }
    .notification.error {
      background-color: #ffe6e6;
      border: 1px solid #ff4d4d;
      color: #800000;
    }
    .notification.success {
      background-color: #e6ffe6;
      border: 1px solid #4dff4d;
      color: #006600;
    }
    form {
      max-width: 500px;
      margin: 20px auto;
      font-family: Arial, sans-serif;
    }
    label {
      display: block;
      margin-top: 12px;
      font-weight: bold;
    }
    input[type=text],
    input[type=email],
    input[type=password],
    select {
      width: 100%;
      padding: 8px;
      margin-top: 4px;
      border: 1px solid #ccc;
      border-radius: 4px;
      box-sizing: border-box;
      font-size: 1rem;
    }
    button {
      margin-top: 18px;
      padding: 10px 20px;
      background-color: #735cff;
      color: white;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      font-size: 1rem;
    }
    button:hover {
      background-color: #5a47cb;
    }
  </style>
</head>
<body>

  <!-- Si existe $mensaje, lo mostramos como notificación en pantalla -->
  <?php if (!empty($mensaje)): ?>
    <div class="notification error">
      <?php echo htmlspecialchars($mensaje); ?>
    </div>
  <?php endif; ?>

  <!-- Formulario de registro -->
  <form method="POST" action="send.php">
    <label for="rol">Rol:</label>
    <select name="rol" id="rol" required>
      <option value="" disabled selected>Selecciona un rol</option>
      <option value="Estudiante">Estudiante</option>
      <option value="Profesor">Profesor</option>
      <option value="Administrador">Administrador</option>
    </select>

    <label for="nombre_completo">Nombre Completo:</label>
    <input type="text" id="nombre_completo" name="nombre_completo" required>

    <label for="correo_electronico">Correo Electrónico:</label>
    <input type="email" id="correo_electronico" name="correo_electronico" required>

    <label for="id_carnet">ID Carnet:</label>
    <input type="text" id="id_carnet" name="id_carnet" required>

    <label for="contrasena">Contraseña:</label>
    <input type="password" id="contrasena" name="contrasena" required>

    <label for="confirmar_contrasena">Confirmar Contraseña:</label>
    <input type="password" id="confirmar_contrasena" name="confirmar_contrasena" required>

    <button type="submit">Registrar Usuario</button>
  </form>

</body>
</html>
