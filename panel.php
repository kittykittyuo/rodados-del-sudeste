<?php

require_once "auth.php";

// Solamente los administradores pueden entrar
requerir_rol("admin");

require_once "config/conexion.php";

// Crear token de seguridad
if (!isset($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

// Consultar los vehículos
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

    <title>Panel de administración</title>

    <style>

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f2f2f2;
            color: #222;
        }

        header {
            background-color: #172536;
            color: white;
            padding: 20px 35px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        header a {
            color: white;
            text-decoration: none;
            margin-left: 15px;
        }

        main {
            max-width: 1100px;
            margin: 30px auto;
            padding: 20px;
        }

        .agregar {
            display: inline-block;
            padding: 12px 18px;
            margin-bottom: 20px;
            background-color: #168348;
            color: white;
            text-decoration: none;
            border-radius: 6px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
        }

        th, td {
            padding: 14px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        th {
            background-color: #172536;
            color: white;
        }

        .editar {
            background-color: #2474b5;
            color: white;
            padding: 8px 12px;
            text-decoration: none;
            border-radius: 5px;
        }

        .eliminar {
            background-color: #c0392b;
            color: white;
            padding: 8px 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .mensaje-vacio {
            background-color: white;
            padding: 25px;
            text-align: center;
        }

        @media (max-width: 650px) {
            main {
                overflow-x: auto;
            }
        }

    </style>

</head>

<body>

    <header>

        <h1>Panel de administración</h1>

        <nav>
            <a href="index.php">Ver catálogo</a>
            <a href="logout.php">Cerrar sesión</a>
        </nav>

    </header>

    <main>

        <h2>Administrar vehículos</h2>

        <a class="agregar" href="vehiculo_form.php">
            Agregar vehículo
        </a>

        <?php if (count($vehiculos) > 0): ?>

            <table>

                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Marca</th>
                        <th>Modelo</th>
                        <th>Año</th>
                        <th>Precio</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody>

                    <?php foreach ($vehiculos as $vehiculo): ?>

                        <tr>

                            <td><?= (int)$vehiculo["id"] ?></td>

                            <td>
                                <?= htmlspecialchars($vehiculo["marca"], ENT_QUOTES, 'UTF-8') ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($vehiculo["modelo"], ENT_QUOTES, 'UTF-8') ?>
                            </td>

                            <td><?= (int)$vehiculo["anio"] ?></td>

                            <td>
                                $<?= number_format((float)$vehiculo["precio"], 0, ',', '.') ?>
                            </td>

                            <td>

                                <a
                                    class="editar"
                                    href="vehiculo_form.php?id=<?= (int)$vehiculo["id"] ?>"
                                >
                                    Editar
                                </a>

                                <form
                                    action="eliminar.php"
                                    method="POST"
                                    style="display:inline;"
                                    onsubmit="return confirm('¿Estás seguro de eliminar este vehículo?');"
                                >

                                    <input
                                        type="hidden"
                                        name="id"
                                        value="<?= (int)$vehiculo["id"] ?>"
                                    >

                                    <input
                                        type="hidden"
                                        name="csrf_token"
                                        value="<?= htmlspecialchars($_SESSION["csrf_token"], ENT_QUOTES, 'UTF-8') ?>"
                                    >

                                    <button class="eliminar" type="submit">
                                        Eliminar
                                    </button>

                                </form>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

        <?php else: ?>

            <div class="mensaje-vacio">
                <h3>No hay vehículos registrados.</h3>
                <p>Podés agregar el primero utilizando el botón de arriba.</p>
            </div>

        <?php endif; ?>

    </main>

</body>
</html>
