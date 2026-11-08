<?php
// Este archivo recibe las peticiones que hace JavaScript (fetch) y responde en JSON
// Permisos:
//   admin   -> agrega (a cualquier usuario), marca y elimina cualquier tarea
//   usuario -> agrega y marca solo SUS tareas, no puede eliminar
require_once "conexion.php";
header("Content-Type: application/json; charset=utf-8");

function responder($ok, $mensaje, $extra = []) {
    echo json_encode(array_merge(["ok" => $ok, "mensaje" => $mensaje], $extra));
    exit;
}

if (!estaLogueado()) {
    http_response_code(401);
    responder(false, "Sesión expirada, vuelve a iniciar sesión.");
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    responder(false, "Método no permitido.");
}

$accion = $_POST['accion'] ?? '';
$miId   = (int) $_SESSION['id'];

switch ($accion) {

    case 'agregar':
        $titulo      = trim($_POST['titulo'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $prioridad   = $_POST['prioridad'] ?? 'media';

        if ($titulo === '') {
            responder(false, "El título no puede estar vacío.");
        }
        if (mb_strlen($titulo) > 150) {
            responder(false, "El título es muy largo (máximo 150).");
        }
        if (!in_array($prioridad, ['baja', 'media', 'alta'])) {
            $prioridad = 'media';
        }

        // el admin puede asignar la tarea a otro usuario
        $dueno = $miId;
        if (esAdmin() && !empty($_POST['usuario_id'])) {
            $dueno = (int) $_POST['usuario_id'];
        }

        $stmt = $conexion->prepare("INSERT INTO tareas (titulo, descripcion, prioridad, usuario_id) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $titulo, $descripcion, $prioridad, $dueno);
        if ($stmt->execute()) {
            responder(true, "Tarea agregada.");
        }
        responder(false, "No se pudo guardar la tarea.");
        break;

    case 'marcar':
        $id = (int) ($_POST['id'] ?? 0);

        // busco la tarea para saber de quién es
        $stmt = $conexion->prepare("SELECT usuario_id, completada FROM tareas WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $t = $stmt->get_result()->fetch_assoc();

        if (!$t) {
            responder(false, "La tarea no existe.");
        }
        if (!esAdmin() && (int) $t['usuario_id'] !== $miId) {
            http_response_code(403);
            responder(false, "No tienes permiso para modificar esta tarea.");
        }

        $nuevo = $t['completada'] ? 0 : 1;
        $stmt = $conexion->prepare("UPDATE tareas SET completada = ? WHERE id = ?");
        $stmt->bind_param("ii", $nuevo, $id);
        $stmt->execute();
        responder(true, $nuevo ? "Tarea completada." : "Tarea marcada como pendiente.");
        break;

    case 'eliminar':
        if (!esAdmin()) {
            http_response_code(403);
            responder(false, "Solo el administrador puede eliminar tareas.");
        }
        $id = (int) ($_POST['id'] ?? 0);
        $stmt = $conexion->prepare("DELETE FROM tareas WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        if ($stmt->affected_rows > 0) {
            responder(true, "Tarea eliminada.");
        }
        responder(false, "La tarea no existe.");
        break;

    default:
        responder(false, "Acción desconocida.");
}
