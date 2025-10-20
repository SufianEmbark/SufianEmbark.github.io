<?php
// Verifica que el usuario actual sea administrador
if (!is_admin()) {
    header("Location: index.php?page=home");// Redirige a inicio si no lo es
    exit;
}

// Determina la acción a realizar, por defecto muestra el listado
$action = $_GET['action'] ?? 'list';
$id = intval($_GET['id'] ?? 0); // ID del usuario (si aplica)

// Control de flujo según la acción
switch ($action) {
    case 'add':
        // Mostrar formulario para añadir nuevo usuario
        include 'partials/usuarios_add.php';
        break;
    case 'edit':
        // Mostrar formulario para editar usuario existente
        include 'partials/usuarios_edit.php';
        break;
    case 'delete':
        // Mostrar confirmación y eliminar usuario
        include 'partials/usuarios_delete.php';
        break;
    default:
    // Mostrar listado de todos los usuarios (por defecto)
        include 'partials/usuarios_listado.php';
        break;
}
?>
