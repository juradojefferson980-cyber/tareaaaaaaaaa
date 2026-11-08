<?php
require_once "conexion.php";

if (estaLogueado()) {
    header("Location: tareas.php");
    exit;
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario'] ?? '');
    $clave   = $_POST['clave'] ?? '';

    if ($usuario === '' || $clave === '') {
        $error = "Llena todos los campos.";
    } else {
        $stmt = $conexion->prepare("SELECT id, nombre, clave, rol FROM usuarios WHERE usuario = ?");
        $stmt->bind_param("s", $usuario);
        $stmt->execute();
        $res = $stmt->get_result();
        $fila = $res->fetch_assoc();

        if ($fila && password_verify($clave, $fila['clave'])) {
            session_regenerate_id(true);
            $_SESSION['id']     = $fila['id'];
            $_SESSION['nombre'] = $fila['nombre'];
            $_SESSION['rol']    = $fila['rol'];
            header("Location: tareas.php");
            exit;
        } else {
            $error = "Usuario o contraseña incorrectos.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión - Lista de Tareas</title>
    <link rel="stylesheet" href="css/estilo.css">
</head>
<body class="fondo-login">
    <div class="caja-login">
        <h1>📝 Lista de Tareas</h1>
        <p class="sub">Inicia sesión para continuar</p>

        <?php if ($error): ?>
            <div class="alerta"><?= limpiar($error) ?></div>
        <?php endif; ?>

        <form method="post" action="login.php">
            <label for="usuario">Usuario</label>
            <input type="text" id="usuario" name="usuario" required autofocus>

            <label for="clave">Contraseña</label>
            <input type="password" id="clave" name="clave" required>

            <button type="submit" class="btn btn-azul">Entrar</button>
        </form>

        <p class="pie">¿No tienes cuenta? <a href="registro.php">Regístrate</a></p>
        <p class="pie"><small>Prueba: admin / 123456 &nbsp;o&nbsp; juan / 123456</small></p>
        <p class="pie"><a href="../index.php">← Volver al menú</a></p>
    </div>
</body>
</html>
