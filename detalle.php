<?php

require_once "auth.php";
requerir_autenticacion();

require_once "config/conexion.php";

// Obtener el ID enviado por la URL
$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

// Comprobar que el ID sea válido
if ($id === false || $id === null || $id < 1) {
    http_response_code(400);
    exit("ID de vehículo inválido.");
}

// Buscar el vehículo en la base de datos
$sql = "SELECT * FROM vehiculos WHERE id = :id";

$stmt = $conexion->prepare($sql);

$stmt->execute([
    ":id" => $id
]);

$vehiculo = $stmt->fetch();

// Comprobar si existe el vehículo
if (!$vehiculo) {
    http_response_code(404);
    exit("El vehículo no existe.");
}

// Validar la URL de la imagen
$imagen = trim($vehiculo["imagen"] ?? "");

$esUrlValida = filter_var($imagen, FILTER_VALIDATE_URL)
    && in_array(
        strtolower(parse_url($imagen, PHP_URL_SCHEME) ?? ""),
        ["http", "https"],
        true
    );

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Detalle del vehículo</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 30px;
            font-family: Arial, sans-serif;
            background-color: #f2f2f2;
            color: #222;
        }

        .contenedor {
            max-width: 850px;
            margin: 30px auto;
            background-color: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 3px 12px rgba(0,0,0,0.1);
        }

        .contenedor img {
            width: 100%;
            height: 400px;
            object-fit: cover;
        }

        .sin-imagen {
            height: 250px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #ddd;
            color: #555;
        }

        .informacion {
            padding: 30px;
        }

        h1 {
            margin-top: 0;
            color: #172536;
        }

        .precio {
            color: #168348;
            font-size: 26px;
            font-weight: bold;
        }

        .descripcion {
            line-height: 1.6;
            white-space: normal;
        }

        .botones {
            margin-top: 25px;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .botones a {
            padding: 12px 20px;
            background-color: #172536;
            color: white;
            text-decoration: none;
            border-radius: 6px;
        }

        .botones a:hover {
            background-color: #29435f;
        }

        @media (max-width: 600px) {
            body {
                padding: 15px;
            }

            .contenedor img {
                height: 250px;
            }

            .informacion {
                padding: 20px;
            }
        }

    </style>

</head>

<body>

    <div class="contenedor">

        <?php if ($esUrlValida): ?>

            <img
                src="<?= htmlspecialchars($imagen, ENT_QUOTES, 'UTF-8') ?>"
                alt="<?= htmlspecialchars($vehiculo["marca"] . " " . $vehiculo["modelo"], ENT_QUOTES, 'UTF-8') ?>"
            >

        <?php else: ?>

            <div class="sin-imagen">
                Sin imagen disponible
            </div>

        <?php endif; ?>

        <div class="informacion">

            <h1>
                <?= htmlspecialchars($vehiculo["marca"], ENT_QUOTES, 'UTF-8') ?>
                <?= htmlspecialchars($vehiculo["modelo"], ENT_QUOTES, 'UTF-8') ?>
            </h1>

            <p>
                <strong>Año:</strong>
                <?= (int)$vehiculo["anio"] ?>
            </p>

            <p class="precio">
                $<?= number_format((float)$vehiculo["precio"], 0, ',', '.') ?>
            </p>

            <h3>Descripción</h3>

            <p class="descripcion">
                <?= nl2br(htmlspecialchars($vehiculo["descripcion"] ?? "", ENT_QUOTES, 'UTF-8')) ?>
            </p>

            <div class="botones">

                <a href="index.php">Volver al catálogo</a>

                <?php if ($_SESSION["rol"] === "admin"): ?>
                    <a href="panel.php">Panel de administración</a>
                <?php endif; ?>

            </div>

        </div>

    </div>

</body>

</html>
