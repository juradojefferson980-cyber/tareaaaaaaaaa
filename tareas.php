<?php
require_once "conexion.php";

if (!estaLogueado()) {
    header("Location: login.php");
    exit;
}

$miId = (int) $_SESSION['id'];

// El admin ve todas las tareas, el usuario normal solo las suyas
if (esAdmin()) {
    $sql = "SELECT t.*, u.nombre AS dueno FROM tareas t
            INNER JOIN usuarios u ON u.id = t.usuario_id
            ORDER BY t.completada ASC, t.id DESC";
    $resultado = $conexion->query($sql);
    $listaUsuarios = $conexion->query("SELECT id, nombre FROM usuarios ORDER BY nombre");
} else {
    $stmt = $conexion->prepare("SELECT t.*, u.nombre AS dueno FROM tareas t
            INNER JOIN usuarios u ON u.id = t.usuario_id
            WHERE t.usuario_id = ?
            ORDER BY t.completada ASC, t.id DESC");
    $stmt->bind_param("i", $miId);
    $stmt->execute();
    $resultado = $stmt->get_result();
}

$tareas = [];
$pendientes = 0;
$hechas = 0;
while ($fila = $resultado->fetch_assoc()) {
    $tareas[] = $fila;
    if ($fila['completada']) { $hechas++; } else { $pendientes++; }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis tareas</title>
    <link rel="stylesheet" href="css/estilo.css">
</head>
<body>
    <header class="barra">
        <h1>📝 Lista de Tareas</h1>
        <div class="usuario-info">
            <span>
                <?= limpiar($_SESSION['nombre']) ?>
                <span class="etiqueta rol-<?= limpiar($_SESSION['rol']) ?>"><?= limpiar($_SESSION['rol']) ?></span>
            </span>
            <a href="../index.php" class="enlace-barra">Menú</a>
            <a href="logout.php" class="enlace-barra">Salir</a>
        </div>
    </header>

    <main class="contenedor">

        <section class="resumen">
            <div class="tarjeta"><strong id="cont-total"><?= count($tareas) ?></strong><span>Total</span></div>
            <div class="tarjeta"><strong id="cont-pend"><?= $pendientes ?></strong><span>Pendientes</span></div>
            <div class="tarjeta"><strong id="cont-hechas"><?= $hechas ?></strong><span>Completadas</span></div>
        </section>

        <div id="mensaje" class="mensaje oculto"></div>

        <section class="panel">
            <h2>Nueva tarea</h2>
            <form id="form-tarea">
                <input type="hidden" name="accion" value="agregar">

                <label for="titulo">Título</label>
                <input type="text" id="titulo" name="titulo" maxlength="150" required>

                <label for="descripcion">Descripción (opcional)</label>
                <textarea id="descripcion" name="descripcion" rows="2"></textarea>

                <div class="fila">
                    <div>
                        <label for="prioridad">Prioridad</label>
                        <select id="prioridad" name="prioridad">
                            <option value="baja">Baja</option>
                            <option value="media" selected>Media</option>
                            <option value="alta">Alta</option>
                        </select>
                    </div>

                    <?php if (esAdmin()): ?>
                    <div>
                        <label for="usuario_id">Asignar a</label>
                        <select id="usuario_id" name="usuario_id">
                            <?php while ($u = $listaUsuarios->fetch_assoc()): ?>
                                <option value="<?= $u['id'] ?>" <?= $u['id'] == $miId ? 'selected' : '' ?>>
                                    <?= limpiar($u['nombre']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn btn-azul">Agregar tarea</button>
            </form>
        </section>

        <section class="panel">
            <div class="encabezado-lista">
                <h2><?= esAdmin() ? 'Todas las tareas' : 'Mis tareas' ?></h2>
                <div class="filtros">
                    <button class="filtro activo" data-filtro="todas">Todas</button>
                    <button class="filtro" data-filtro="pendientes">Pendientes</button>
                    <button class="filtro" data-filtro="hechas">Completadas</button>
                </div>
            </div>

            <ul id="lista-tareas" class="lista">
                <?php foreach ($tareas as $t): ?>
                <li class="tarea prioridad-<?= $t['prioridad'] ?> <?= $t['completada'] ? 'hecha' : '' ?>"
                    data-id="<?= $t['id'] ?>" data-estado="<?= $t['completada'] ? 'hechas' : 'pendientes' ?>">

                    <input type="checkbox" class="chk" <?= $t['completada'] ? 'checked' : '' ?>
                           title="Marcar como completada">

                    <div class="contenido">
                        <span class="titulo"><?= limpiar($t['titulo']) ?></span>
                        <?php if ($t['descripcion']): ?>
                            <span class="desc"><?= limpiar($t['descripcion']) ?></span>
                        <?php endif; ?>
                        <span class="meta">
                            <span class="etiqueta pri-<?= $t['prioridad'] ?>"><?= $t['prioridad'] ?></span>
                            <?php if (esAdmin()): ?> · de <?= limpiar($t['dueno']) ?><?php endif; ?>
                            · <?= date("d/m/Y", strtotime($t['creado'])) ?>
                        </span>
                    </div>

                    <?php if (esAdmin()): ?>
                        <button class="btn-eliminar" title="Eliminar tarea">🗑</button>
                    <?php endif; ?>
                </li>
                <?php endforeach; ?>
            </ul>

            <p id="vacio" class="vacio <?= count($tareas) ? 'oculto' : '' ?>">No hay tareas todavía. ¡Agrega la primera!</p>

            <?php if (!esAdmin()): ?>
                <p class="nota">Nota: con el rol <b>usuario</b> puedes agregar y marcar tus tareas, pero solo un administrador puede eliminarlas.</p>
            <?php endif; ?>
        </section>
    </main>

    <script>
        // lo uso en el JS para saber si mostrar el botón de eliminar
        const ES_ADMIN = <?= esAdmin() ? 'true' : 'false' ?>;
    </script>
    <script src="js/app.js"></script>
</body>
</html>
