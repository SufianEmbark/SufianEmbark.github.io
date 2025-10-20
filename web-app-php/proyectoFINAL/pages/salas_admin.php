<?php
// Verifica si el usuario es administrador
if (!is_admin()) {
    header("Location: index.php?page=home");
    exit;
}

// Determina la acción a realizar: por defecto muestra el listado
$action = $_GET['action'] ?? 'list'; // default = listado
$id = intval($_GET['id'] ?? 0);

// Controla qué acción se ejecuta
switch ($action) {
    case 'add':
        // Mostrar formulario para añadir sala
        include 'partials/salas_add.php';
        break;
    case 'edit':
        // Mostrar formulario para editar sala existente
        include 'partials/salas_edit.php';
        break;
    case 'fotos':
        // Gestionar fotos de una sala
        include 'partials/salas_fotos.php';
        break;
    case 'delete':
        // Eliminar sala si el ID es válido
        if ($id > 0) {
            $stmt = $db->prepare("DELETE FROM salas WHERE id = :id");
            $stmt->execute([':id' => $id]);

            // Registrar eliminación en el log
            $log = $db->prepare("INSERT INTO log (descripcion, fecha) VALUES (:desc, NOW())");
            $log->execute([':desc' => "Sala eliminada: ID $id"]);

            // Redirigir al listado tras borrar
            header("Location: index.php?page=salas_admin");
            exit;
        }
        break;
    default:
    // Mostrar listado de salas con acciones (por defecto)
        include 'partials/salas_listado_admin.php'; // listado de salas para admin
        break;
}
