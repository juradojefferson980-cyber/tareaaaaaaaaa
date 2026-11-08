<?php
// Página de inicio del ejercicio: si ya inició sesión va a las tareas, si no al login
require_once "conexion.php";

if (estaLogueado()) {
    header("Location: tareas.php");
} else {
    header("Location: login.php");
}
exit;
