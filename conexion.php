<?php
/**
 * ------------------------------------------------------------------------
 * conexion.php – Conexión central a la base de datos MySQL
 * ------------------------------------------------------------------------
 *
 * Crea un objeto mysqli y lo expone en la variable global $conn
 * para ser reutilizado por los demás scripts de la aplicación.
 *
 * Si la conexión falla, detiene la ejecución con un mensaje de error.
 *
 * Uso típico
 * ----------
 * include('conexion.php');
 * // … usar $conn ↓ …
 * $result = $conn->query("SELECT * FROM tabla");
 *
 * Buenas prácticas
 * ----------------
 * • Extrae las credenciales a variables de entorno (.env) o
 *   archivos fuera del directorio público para mayor seguridad.  
 * • Habilita SSL/TLS si el servidor MySQL lo permite.  
 * • Maneja los errores con try/catch o un wrapper personalizado
 *   en entornos de producción; evita mostrar detalles sensibles.
 *
 * @author   Savesa Team
 * @license  MIT
 * @version  1.0
 */

/* --------------------------------------------------------------------- */
/* 1) PARÁMETROS DE CONEXIÓN                                             */
/* --------------------------------------------------------------------- */
$host     = "localhost"; // Servidor MySQL
$user     = "root";      // Usuario
$password = "";          // Contraseña
$dbname   = "savesa";    // Base de datos

/* --------------------------------------------------------------------- */
/* 2) INTENTAR CONECTAR                                                  */
/* --------------------------------------------------------------------- */
$conn = mysqli_connect($host, $user, $password, $dbname);

/* --------------------------------------------------------------------- */
/* 3) VERIFICAR RESULTADO                                                */
/* --------------------------------------------------------------------- */
if (!$conn) {
    /* Error crítico → interrumpe ejecución */
    die("❌ Error de conexión: " . mysqli_connect_error());
} else {
    /* Conexión exitosa (silencioso por defecto) */
    // echo "✅ Conectado correctamente a la base de datos";
}
?>
