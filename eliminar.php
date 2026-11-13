
<?php

require_once "auth.php";

// Solamente puede eliminar un administrador
requerir_rol("admin");

require_once "config/conexion.php";

// Comprobar que se envió mediante POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    exit("Método no permitido.");
}

// Comprobar el token de seguridad
$token = $_POST["csrf_token"] ?? "";

if (
    !is_string($token) ||
    !isset($_SESSION["csrf_token"]) ||
    !hash_equals($_SESSION["csrf_token"], $token)
) {
    http_response_code(403);
    exit("Solicitud no autorizada.");
}

// Obtener el ID del vehículo
$id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);

if ($id === false || $id === null || $id < 1) {
    exit("ID de vehículo inválido.");
}

// Eliminar el vehículo
$sql = "DELETE FROM vehiculos WHERE id = :id";

$stmt = $conexion->prepare($sql);

$stmt->execute([
    ":id" => $id
]);

// Volver al panel
header("Location: panel.php");
exit;

?>
