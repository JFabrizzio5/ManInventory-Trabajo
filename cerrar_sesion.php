<?php
session_start();
session_unset();
session_destroy();

// Redirigir a la página de inicio de sesión después de cerrar sesión
header("Location: login.php");
exit();
?>
