<?php
require_once "conexion.php";

$error = "";
$ok = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre  = trim($_POST['nombre'] ?? '');
    $usuario = trim($_POST['usuario'] ?? '');
    $clave   = $_POST['clave'] ?? '';
    $clave2  = $_POST['clave2'] ?? '';

    if ($nombre === '' || $usuario === '' || $clave === '') {
        $error = "Todos los campos son obligatorios.";
    } elseif (!preg_match('/^[A-Za-z0-9_]{3,30}$/', $usuario)) {
        $error = "El usuario solo puede tener letras, números y guion bajo (mínimo 3).";
    } elseif (strlen($clave) < 6) {
        $error = "La contraseña debe tener al menos 6 caracteres.";
    } elseif ($clave !== $clave2) {
        $error = "Las contraseñas no coinciden.";
    } else {
        // ver si el usuario ya existe
        $stmt = $conexion->prepare("SELECT id FROM usuarios WHERE usuario = ?");
        $stmt->bind_param("s", $usuario);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "Ese usuario ya existe, elige otro.";
        } else {
            $hash = password_hash($clave, PASSWORD_DEFAULT);
            // todos los que se registran quedan con rol "usuario"
            $stmt2 = $conexion->prepare("INSERT INTO usuarios (nombre, usuario, clave, rol) VALUES (?, ?, ?, 'usuario')");
            $stmt2->bind_param("sss", $nombre, $usuario, $hash);
            if ($stmt2->execute()) {
                $ok = "Cuenta creada. Ya puedes iniciar sesión.";
            } else {
                $error = "No se pudo crear la cuenta.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Lista de Tareas</title>
    <link rel="stylesheet" href="css/estilo.css">
</head>
<body class="fondo-login">
    <div class="caja-login">
        <h1>Crear cuenta</h1>

        <?php if ($error): ?>
            <div class="alerta"><?= limpiar($error) ?></div>
        <?php endif; ?>
        <?php if ($ok): ?>
            <div class="alerta exito"><?= limpiar($ok) ?></div>
        <?php endif; ?>

        <form method="post" action="registro.php">
            <label for="nombre">Nombre completo</label>
            <input type="text" id="nombre" name="nombre" required>

            <label for="usuario">Usuario</label>
            <input type="text" id="usuario" name="usuario" required>

            <label for="clave">Contraseña</label>
            <input type="password" id="clave" name="clave" required>

            <label for="clave2">Repetir contraseña</label>
            <input type="password" id="clave2" name="clave2" required>

            <button type="submit" class="btn btn-azul">Registrarme</button>
        </form>

        <p class="pie">¿Ya tienes cuenta? <a href="login.php">Inicia sesión</a></p>
    </div>
</body>
</html>
