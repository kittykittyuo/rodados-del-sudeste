
<?php

require_once "config/conexion.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = $_POST["email"];
    $password = $_POST["password"];

    $sql = "SELECT * FROM usuarios WHERE email = :email";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([":email" => $email]);

    $usuario = $stmt->fetch();

    if ($usuario && password_verify($password, $usuario["password"])) {

        session_start();

        $_SESSION["id"] = $usuario["id"];
        $_SESSION["email"] = $usuario["email"];
        $_SESSION["rol"] = $usuario["rol"];

        header("Location: index.php");
        exit;

    } else {
        $mensaje = "Email o contraseña incorrectos.";
    }
}


?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión</title>
</head>

<body>

    <div class="encabezado">

        <h1>Iniciar sesión</h1>

        <a href="registro.php">Registrarse</a>

    </div>

    <?php if ($mensaje != ""): ?>
        <p><?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <form method="POST">

        <label>Email:</label>
        <input type="email" name="email" placeholder="Completá tu email" required>

        <br><br>

        <label>Contraseña:</label>
        <input type="password" name="password" placeholder="Completá tu contraseña" required>

        <br><br>

        <button type="submit">Ingresar</button>

    </form>

</body>

</html>
