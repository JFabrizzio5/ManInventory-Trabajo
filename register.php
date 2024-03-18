<?php
include('conexion.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST["registerName"];
    $email = $_POST["registerEmail"];
    $password = password_hash($_POST["registerPassword"], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO usuarios (nombre, correo, contrasena) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $email, $password);

    if ($stmt->execute()) {
        // Registro exitoso
        header("Location: about.html");
        exit();
    } else {
        // Error en el registro
        // Manejar el error según sea necesario
        echo "Error al registrar el usuario.";
    }

    $stmt->close();
}

$conn->close();
?>
