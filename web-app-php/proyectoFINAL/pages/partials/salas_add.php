<?php
// Procesa el formulario cuando se envía por POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recoge y limpia los datos del formulario
    $nombre = trim($_POST['nombre'] ?? '');
    $ubicacion = trim($_POST['ubicacion'] ?? '');
    $capacidad = intval($_POST['capacidad'] ?? 0);
    $descripcion = trim($_POST['descripcion'] ?? '');
    $reservable = isset($_POST['reservable']) ? 1 : 0; // Checkbox

    $MAX_CAPACIDAD = 65535; // Limite de capacidad (evitar errores en la base de datos)

    // Validación básica
    if ($nombre && $ubicacion && $capacidad > 0) {
        if ($capacidad > $MAX_CAPACIDAD) {
            echo "<p>Error: no se puede poner un número tan grande. Máximo permitido: $MAX_CAPACIDAD.</p>";
        } else {
            try {
                // Inserta nueva sala en la base de datos
                $stmt = $db->prepare("INSERT INTO salas (nombre, ubicacion, capacidad, descripcion, reservable)
                                     VALUES (:n, :u, :c, :d, :r)");
                $stmt->execute([
                    ':n' => $nombre,
                    ':u' => $ubicacion,
                    ':c' => $capacidad,
                    ':d' => $descripcion,
                    ':r' => $reservable
                ]);

                // Registra la acción en la tabla de logs
                $log = $db->prepare("INSERT INTO log (descripcion, fecha) VALUES (:desc, NOW())");
                $log->execute([':desc' => "Sala creada: " . $nombre]);

                // Redirige al listado de salas del administrador
                header("Location: index.php?page=salas_admin");
                exit;

            } catch (PDOException $e) {
                echo "<p>Error al guardar la sala: " . htmlspecialchars($e->getMessage()) . "</p>";
            }
        }
    } else {
        echo "<p>Error: todos los campos son obligatorios.</p>";
    }
}
?>

<!-- FORMULARIO HTML PARA CREAR UNA NUEVA SALA -->
<h2 class="seccion-titulo">Añadir Sala</h2>
<div class="formulario-centrado">
    <form method="post" novalidate>
        <label>Nombre:</label>
        <input type="text" name="nombre" required>

        <label>Ubicación:</label>
        <input type="text" name="ubicacion" required>

        <label>Capacidad:</label>
        <input type="number" name="capacidad" min="1" required>

        <label>Descripción:</label>
        <textarea name="descripcion" rows="4"></textarea>

        <label>Reservable:</label>
        <input type="checkbox" name="reservable" checked>

        <button type="submit">Guardar</button>
    </form>
</div>