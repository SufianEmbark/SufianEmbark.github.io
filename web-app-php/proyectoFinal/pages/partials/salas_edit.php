<?php
// Obtiene el ID de la sala desde la URL (GET)
$id = intval($_GET['id'] ?? 0);

// Consulta la sala en la base de datos
$stmt = $db->prepare("SELECT * FROM salas WHERE id = :id");
$stmt->execute([':id' => $id]);
$sala = $stmt->fetch(PDO::FETCH_ASSOC);

// Si no se encuentra la sala, muestra mensaje y termina
if (!$sala) {
    echo "<p>Sala no encontrada.</p>";
    return;
}

// Si el formulario fue enviado por POST, procesar los datos
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recoger y limpiar datos del formulario
    $nombre = trim($_POST['nombre'] ?? '');
    $ubicacion = trim($_POST['ubicacion'] ?? '');
    $capacidad = intval($_POST['capacidad'] ?? 0);
    $descripcion = trim($_POST['descripcion'] ?? '');
    $reservable = isset($_POST['reservable']) ? 1 : 0;

    $MAX_CAPACIDAD = 65535; //  limite de capacidad

    // Validación básica
    if ($nombre && $ubicacion && $capacidad > 0) {
        if ($capacidad > $MAX_CAPACIDAD) {
            echo "<p>Error: no se puede poner un número tan grande. Máximo permitido: $MAX_CAPACIDAD.</p>";
        } else {
            try {
                // Actualiza la sala en la base de datos
                $stmt = $db->prepare("UPDATE salas 
                                    SET nombre = :n, ubicacion = :u, capacidad = :c, descripcion = :d, reservable = :r 
                                    WHERE id = :id");
                $stmt->execute([
                    ':n' => $nombre,
                    ':u' => $ubicacion,
                    ':c' => $capacidad,
                    ':d' => $descripcion,
                    ':r' => $reservable,
                    ':id' => $id
                ]);
                // Log de la acción
                $log = $db->prepare("INSERT INTO log (descripcion, fecha) VALUES (:desc, NOW())");
                $log->execute([':desc' => "Sala editada: " . $nombre]);

                header("Location: index.php?page=salas_admin");
                exit;
            } catch (PDOException $e) {
                echo "<p>Error al actualizar la sala: " . htmlspecialchars($e->getMessage()) . "</p>";
            }
        }
    } else {
        echo "<p>Error: todos los campos son obligatorios.</p>";
    }
}
?>

<!-- FORMULARIO HTML PARA EDITAR SALA -->
 
<h2 class="seccion-titulo">Editar Sala</h2>
<div class="formulario-centrado">
    <form method="post" novalidate>
        <label>Nombre:</label>
        <input type="text" name="nombre" value="<?= htmlspecialchars($sala['nombre']) ?>" required>

        <label>Ubicación:</label>
        <input type="text" name="ubicacion" value="<?= htmlspecialchars($sala['ubicacion']) ?>" required>

        <label>Capacidad:</label>
        <input type="number" name="capacidad" value="<?= $sala['capacidad'] ?>" min="1" required>

        <label>Descripción:</label>
        <textarea name="descripcion" rows="4"><?= htmlspecialchars($sala['descripcion']) ?></textarea>

        <label>Reservable:</label>
        <input type="checkbox" name="reservable" <?= $sala['reservable'] ? 'checked' : '' ?>>

        <button type="submit">Actualizar</button>
    </form>
</div>
