<?php
$servername = "localhost";
$username = "root2";
$password = "n0m3l0";
$database = "Gestor";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $database);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}
?>
