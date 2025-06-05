<?php
/**
 * ------------------------------------------------------------------------
 * logout.php – Cerrar sesión del usuario
 * ------------------------------------------------------------------------
 *
 * 1. Arranca la sesión actual (session_start()).
 * 2. Elimina todas las variables de sesión con session_unset().
 * 3. Destruye la sesión en el servidor mediante session_destroy().
 * 4. Redirige al usuario a la página de login.
 *
 * Uso
 * ----
 * Se llama normalmente desde un botón “Cerrar sesión” presente en la UI
 * (método POST o GET). Tras ejecutarse, la sesión queda invalidada y el
 * usuario debe volver a autenticarse.
 *
 * Requisitos
 * ----------
 * -  PHP 7.0 o superior.
 *
 * @author   Savesa Team
 * @license  MIT
 * @version  1.0
 */

session_start();          // Inicia/retoma la sesión para poder manipularla
session_unset();          // Elimina todas las variables de la superglobal $_SESSION
session_destroy();        // Borra la sesión (archivo / cookie de sesión)

// Redirige inmediatamente al formulario de login
header("Location: login.php");
exit();                   // Asegura que no se envíe más contenido
?>
