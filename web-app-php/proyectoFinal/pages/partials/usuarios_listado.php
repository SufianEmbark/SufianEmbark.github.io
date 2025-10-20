<?php
// Obtener todos los usuarios ordenados por nombre
$stmt = $db->query("SELECT id, nombre, apellidos, dni, email, rol FROM usuarios ORDER BY nombre");
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Título y botón para añadir nuevo usuario -->
<h2 class="seccion-titulo">👥 Usuarios del Sistema</h2>
<p><a href="index.php?page=usuarios_admin&action=add" class="btn btn-add">➕ Añadir Usuario</a></p>

<!-- Tabla con listado de usuarios -->
<table class="tabla-usuarios">
    <thead>
        <tr>
            <th>Nombre</th>
            <th>DNI</th>
            <th>Email</th>
            <th>Rol</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <!-- Mostrar cada usuario en una fila -->
        <?php foreach ($usuarios as $u): ?>
        <tr>
            <td><?= htmlspecialchars("{$u['nombre']} {$u['apellidos']}") ?></td> 
            <td><?= htmlspecialchars($u['dni']) ?></td>                             
            <td><?= htmlspecialchars($u['email']) ?></td>
            <td><?= htmlspecialchars($u['rol']) ?></td>
            <td class="acciones">
                <!-- Enlaces para editar y borrar usuario -->
                <a href="index.php?page=usuarios_admin&action=edit&id=<?= $u['id'] ?>" class="accion editar">✏️ Editar</a>
                <a href="index.php?page=usuarios_admin&action=delete&id=<?= $u['id'] ?>" class="accion borrar" onclick="return confirm('¿Eliminar este usuario?')">🗑️ Borrar</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
