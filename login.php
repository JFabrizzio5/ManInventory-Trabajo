<?php
include('conexion.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["loginEmail"];
    $password = $_POST["loginPassword"];

    $stmt = $conn->prepare("SELECT id, contrasena FROM usuarios WHERE correo = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($user_id, $hashed_password);
    $stmt->fetch();
    $stmt->close();

    if (password_verify($password, $hashed_password)) {
        // Inicio de sesión exitoso
        session_start();
        $_SESSION['user_id'] = $user_id;
        header("Location: Table.php");
        exit();
    } else {
        // Error en el inicio de sesión
        // Manejar el error según sea necesario
        echo "Credenciales incorrectas porfavor vuelve a iniciar session y verifica el correo y contraseña.";
    }
}

$conn->close();
?>
