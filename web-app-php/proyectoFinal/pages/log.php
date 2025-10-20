<?php
// Solo permite el acceso a usuarios administradores
if (!is_admin()) {
    header("Location: index.php?page=home");
    exit;
}

// Consulta SQL para obtener todos los registros del log ordenados por fecha descendente
$stmt = $db->query("SELECT * FROM log ORDER BY fecha DESC");
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC); // Guarda los resultados en un array asociativo
?>

<!-- Título de la sección del log del sistema -->
<h2 class="seccion-titulo">📋 Registro de Actividades</h2>

<!-- Si no hay registros, muestra un mensaje -->
<?php if (count($logs) === 0): ?>
    <p>No hay registros aún.</p>

<!-- Si hay registros, se muestran en una tabla -->
<?php else: ?>
    <div class="tabla-contenedor">
        <table class="tabla-log">
            <thead>
                <tr>
                    <th>📅 Fecha</th>
                    <th>📝 Descripción</th>
                </tr>
            </thead>
            <tbody>
                <!-- Recorre los registros y muestra cada uno en una fila -->
                <?php foreach ($logs as $log): ?>
                    <tr>
                        <!-- Se usa htmlspecialchars para evitar inyección HTML -->
                        <td><?= htmlspecialchars($log['fecha']) ?></td>
                        <td><?= htmlspecialchars($log['descripcion']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

