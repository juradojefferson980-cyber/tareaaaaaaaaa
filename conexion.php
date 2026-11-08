<?php
// Conexión a la base de datos con mysqli
// Si usas XAMPP/Laragon normalmente el usuario es root y sin clave
$host = "localhost";
$user = "root";
$pass = "";
$bd   = "pwatarea4";

$conexion = new mysqli($host, $user, $pass, $bd);

if ($conexion->connect_error) {
    die("Error al conectar con la base de datos: " . $conexion->connect_error);
}
$conexion->set_charset("utf8mb4");

session_start();

// Funciones que uso en varias páginas
function estaLogueado() {
    return isset($_SESSION['id']);
}

function esAdmin() {
    return isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin';
}

function limpiar($texto) {
    return htmlspecialchars(trim($texto), ENT_QUOTES, 'UTF-8');
}
