
<?php

require_once "auth.php";

// Solamente los administradores pueden entrar
requerir_rol("admin");

require_once "config/conexion.php";

// Crear token de seguridad
if (!isset($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

$mensaje = "";
$errores = [];

// Comprobar si recibimos un ID
$id = null;

if (isset($_GET["id"])) {

    $id = filter_var($_GET["id"], FILTER_VALIDATE_INT);

    if ($id === false || $id < 1) {
        http_response_code(400);
        exit("ID inválido.");
    }
}

// Valores iniciales
$vehiculo = [
    "marca" => "",
    "modelo" => "",
    "anio" => "",
    "precio" => "",
    "descripcion" => "",
    "imagen" => ""
];

// Si estamos editando, buscar el vehículo
if ($id !== null) {

    $sql = "SELECT * FROM vehiculos WHERE id = :id";

    $stmt = $conexion->prepare($sql);

    $stmt->execute([
        ":id" => $id
    ]);

    $vehiculoEncontrado = $stmt->fetch();

    if (!$vehiculoEncontrado) {
        http_response_code(404);
        exit("El vehículo no existe.");
    }

    $vehiculo = $vehiculoEncontrado;
}

// Procesar el formulario
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Verificar token de seguridad
    $token = $_POST["csrf_token"] ?? "";

    if (
        !is_string($token) ||
        !hash_equals($_SESSION["csrf_token"], $token)
    ) {
        http_response_code(403);
        exit("Solicitud no autorizada.");
    }

    // Recibir los datos
    $marca = isset($_POST["marca"]) && is_string($_POST["marca"])
        ? trim($_POST["marca"]) : "";

    $modelo = isset($_POST["modelo"]) && is_string($_POST["modelo"])
        ? trim($_POST["modelo"]) : "";

    $anio = isset($_POST["anio"]) && is_string($_POST["anio"])
        ? trim($_POST["anio"]) : "";

    $precio = isset($_POST["precio"]) && is_string($_POST["precio"])
        ? trim($_POST["precio"]) : "";

    $descripcion = isset($_POST["descripcion"]) && is_string($_POST["descripcion"])
        ? trim($_POST["descripcion"]) : "";

    $imagen = isset($_POST["imagen"]) && is_string($_POST["imagen"])
        ? trim($_POST["imagen"]) : "";

    // Mantener los datos en el formulario
    $vehiculo = [
        "marca" => $marca,
        "modelo" => $modelo,
        "anio" => $anio,
        "precio" => $precio,
        "descripcion" => $descripcion,
        "imagen" => $imagen
    ];

    // Validaciones
    if ($marca === "" || $modelo === "" || $anio === "" ||
        $precio === "" || $descripcion === "") {

        $errores[] = "Completá todos los campos obligatorios.";
    }

    if (strlen($marca) > 100 || strlen($modelo) > 100) {
        $errores[] = "La marca y el modelo no pueden superar los 100 caracteres.";
    }

    if (strlen($descripcion) > 255) {
        $errores[] = "La descripción no puede superar los 255 caracteres.";
    }

    if (strlen($imagen) > 255) {
        $errores[] = "La URL de la imagen es demasiado larga.";
    }

    // Validar año
    $anioValidado = filter_var($anio, FILTER_VALIDATE_INT);

    if ($anioValidado === false || $anioValidado < 1886 || $anioValidado > 2100) {
        $errores[] = "Ingresá un año válido.";
    }

    // Validar precio
    $precioValidado = filter_var($precio, FILTER_VALIDATE_INT);

    if ($precioValidado === false || $precioValidado < 1 || $precioValidado > 2147483647) {
        $errores[] = "Ingresá un precio entero válido.";
    }

    // Validar URL de imagen
    if ($imagen !== "") {

        $urlValida = filter_var($imagen, FILTER_VALIDATE_URL);

        $protocolo = strtolower(parse_url($imagen, PHP_URL_SCHEME) ?? "");

        if (!$urlValida || !in_array($protocolo, ["http", "https"], true)) {
            $errores[] = "Ingresá una URL de imagen válida (HTTP o HTTPS).";
        }
    }

    // Si no hay errores, guardar
    if (count($errores) === 0) {

        try {

            if ($id !== null) {

                // Modificar vehículo existente
                $sql = "UPDATE vehiculos SET
                        marca = :marca,
                        modelo = :modelo,
                        anio = :anio,
                        precio = :precio,
                        descripcion = :descripcion,
                        imagen = :imagen
                        WHERE id = :id";

                $stmt = $conexion->prepare($sql);

                $stmt->execute([
                    ":marca" => $marca,
                    ":modelo" => $modelo,
                    ":anio" => $anioValidado,
                    ":precio" => $precioValidado,
                    ":descripcion" => $descripcion,
                    ":imagen" => $imagen,
                    ":id" => $id
                ]);

            } else {

                // Agregar vehículo nuevo
                $sql = "INSERT INTO vehiculos
                        (marca, modelo, anio, precio, descripcion, imagen)
                        VALUES
                        (:marca, :modelo, :anio, :precio, :descripcion, :imagen)";

                $stmt = $conexion->prepare($sql);

                $stmt->execute([
                    ":marca" => $marca,
                    ":modelo" => $modelo,
                    ":anio" => $anioValidado,
                    ":precio" => $precioValidado,
                    ":descripcion" => $descripcion,
                    ":imagen" => $imagen
                ]);
            }

            // Volver al panel
            header("Location: panel.php");
            exit;

        } catch (PDOException $e) {

            $mensaje = "Ocurrió un error al guardar el vehículo.";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Formulario de vehículos</title>

    <style>

        body {
            margin: 0;
            padding: 30px;
            font-family: Arial, sans-serif;
            background-color: #f2f2f2;
            color: #222;
        }

        .contenedor {
            max-width: 600px;
            margin: 30px auto;
            padding: 30px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }

        h1 {
            color: #172536;
        }

        label {
            display: block;
            margin-top: 18px;
            font-weight: bold;
        }

        input, textarea {
            width: 100%;
            padding: 11px;
            margin-top: 7px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        textarea {
            resize: vertical;
            min-height: 100px;
        }

        button {
            margin-top: 25px;
            padding: 12px 20px;
            background-color: #168348;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background-color: #116b39;
        }

        .volver {
            display: inline-block;
            margin-top: 20px;
            color: #172536;
            text-decoration: none;
        }

        .error {
            padding: 10px;
            margin-bottom: 10px;
            background-color: #ffe1e1;
            color: #a00000;
            border-radius: 5px;
        }

    </style>

</head>

<body>

    <div class="contenedor">

        <h1>
            <?= $id !== null ? "Editar vehículo" : "Agregar vehículo" ?>
        </h1>

        <?php if ($mensaje !== ""): ?>
            <p class="error">
                <?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?>
            </p>
        <?php endif; ?>

        <?php foreach ($errores as $error): ?>
            <p class="error">
                <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
            </p>
        <?php endforeach; ?>

        <form method="POST" action="vehiculo_form.php<?= $id !== null ? "?id=" . (int)$id : "" ?>">

            <input
                type="hidden"
                name="csrf_token"
                value="<?= htmlspecialchars($_SESSION["csrf_token"], ENT_QUOTES, 'UTF-8') ?>"
            >

            <label for="marca">Marca:</label>
            <input
                type="text"
                name="marca"
                id="marca"
                maxlength="100"
                value="<?= htmlspecialchars($vehiculo["marca"], ENT_QUOTES, 'UTF-8') ?>"
                required
            >

            <label for="modelo">Modelo:</label>
            <input
                type="text"
                name="modelo"
                id="modelo"
                maxlength="100"
                value="<?= htmlspecialchars($vehiculo["modelo"], ENT_QUOTES, 'UTF-8') ?>"
                required
            >

            <label for="anio">Año:</label>
            <input
                type="number"
                name="anio"
                id="anio"
                min="1886"
                max="2100"
                value="<?= htmlspecialchars((string)$vehiculo["anio"], ENT_QUOTES, 'UTF-8') ?>"
                required
            >

            <label for="precio">Precio:</label>
            <input
                type="number"
                name="precio"
                id="precio"
                min="1"
                max="2147483647"
                step="1"
                value="<?= htmlspecialchars((string)$vehiculo["precio"], ENT_QUOTES, 'UTF-8') ?>"
                required
            >

            <label for="descripcion">Descripción:</label>
            <textarea
                name="descripcion"
                id="descripcion"
                maxlength="255"
                required
            ><?= htmlspecialchars($vehiculo["descripcion"], ENT_QUOTES, 'UTF-8') ?></textarea>

            <label for="imagen">URL de la imagen (opcional):</label>
            <input
                type="url"
                name="imagen"
                id="imagen"
                maxlength="255"
                placeholder="https://ejemplo.com/auto.jpg"
                value="<?= htmlspecialchars($vehiculo["imagen"], ENT_QUOTES, 'UTF-8') ?>"
            >

            <button type="submit">
                <?= $id !== null ? "Guardar cambios" : "Agregar vehículo" ?>
            </button>

        </form>

        <a class="volver" href="panel.php">Volver al panel</a>

    </div>

</body>

</html>
