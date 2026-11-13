<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Comprueba si el usuario inició sesión
function esta_autenticado() {
    return isset(
        $_SESSION['id'],
        $_SESSION['email'],
        $_SESSION['rol']
    );
}

// Obliga al usuario a iniciar sesión
function requerir_autenticacion() {
    if (!esta_autenticado()) {
        header("Location: login.php");
        exit;
    }
}

// Comprueba si tiene el rol necesario
function requerir_rol($rol_esperado) {
    requerir_autenticacion();

    if ($_SESSION['rol'] !== $rol_esperado) {
        http_response_code(403);
        exit("403 - No tenés permisos para acceder.");
    }
}
