<?php
// Solo los administradores pueden borrar usuarios
if (!is_admin()) {
    header("Location: index.php?page=home");
    exit;
}

// Obtener el ID del usuario a eliminar desde la URL
$id = intval($_GET['id'] ?? 0);

// Prevenir que el administrador se elimine a sí mismo
if ($id === current_user()['id']) {
    echo '<p>No puedes borrar tu propio usuario.</p>';
    return;
}

// Verificar si el usuario existe en la base de datos
$stmt = $db->prepare("SELECT * FROM usuarios WHERE id = :id");
$stmt->execute([':id' => $id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

// Si no se encuentra el usuario, mostrar mensaje y salir
if (!$usuario) {
    echo '<p>Usuario no encontrado.</p>';
    return;
}

// Si el usuario tiene una foto subida, eliminarla del sistema de archivos
if (!empty($usuario['foto']) && file_exists("uploads/fotos_usuarios/" . $usuario['foto'])) {
    unlink("uploads/fotos_usuarios/" . $usuario['foto']);
}

// Eliminar el usuario de la base de datos
$stmt = $db->prepare("DELETE FROM usuarios WHERE id = :id");
$stmt->execute([':id' => $id]);

// Redirigir de vuelta al listado de usuarios
header("Location: index.php?page=usuarios_admin");
exit;