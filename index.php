<?php

require_once "auth.php";
requerir_autenticacion();

require_once "config/conexion.php";

// Obtener todos los vehiculos
$sql = "SELECT * FROM vehiculos ORDER BY id DESC";

$stmt = $conexion->prepare($sql);
$stmt->execute();

$vehiculos = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Rodados del Sudeste</title>
    <link rel="stylesheet" href="style.css">

</head>

<body>

    <header>

        <h1>Rodados del Sudeste</h1>

        <nav>

            <span>
                Hola, <?= htmlspecialchars($_SESSION["email"], ENT_QUOTES, 'UTF-8') ?>
            </span>

            <?php if ($_SESSION["rol"] === "admin"): ?>
                <a href="panel.php">Administrar vehículos</a>
            <?php endif; ?>

            <a href="logout.php">Cerrar sesión</a>

        </nav>

    </header>

    <main>

        <h2>Vehículos disponibles</h2>

        <?php if (count($vehiculos) > 0): ?>

            <div class="vehiculos">

                <?php foreach ($vehiculos as $vehiculo): ?>

                    <?php
                    $imagen = trim($vehiculo["imagen"] ?? "");

                    $esUrlValida = filter_var($imagen, FILTER_VALIDATE_URL)
                        && in_array(
                            strtolower(parse_url($imagen, PHP_URL_SCHEME) ?? ""),
                            ["http", "https"],
                            true
                        );
                    ?>

                    <a class="tarjeta"
                       href="detalle.php?id=<?= (int)$vehiculo["id"] ?>">

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

                        <div class="contenido">

                            <h2>
                                <?= htmlspecialchars($vehiculo["marca"], ENT_QUOTES, 'UTF-8') ?>
                                <?= htmlspecialchars($vehiculo["modelo"], ENT_QUOTES, 'UTF-8') ?>
                            </h2>

                            <p>
                                Año: <?= (int)$vehiculo["anio"] ?>
                            </p>

                            <p class="precio">
                                $<?= number_format((float)$vehiculo["precio"], 0, ',', '.') ?>
                            </p>

                        </div>

                    </a>

                <?php endforeach; ?>

            </div>

        <?php else: ?>

            <div class="vacio">
                <h2>No hay vehículos disponibles.</h2>
                <p>Próximamente agregaremos nuevos vehículos.</p>
            </div>

        <?php endif; ?>

    </main>

</body>
</html>
