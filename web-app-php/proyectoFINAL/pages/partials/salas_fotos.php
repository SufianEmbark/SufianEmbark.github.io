<?php
// Obtiene el ID de la sala desde la URL
$id = intval($_GET['id'] ?? 0);

// --- CARGAR SALA ---
$stmt = $db->prepare("SELECT * FROM salas WHERE id = :id");
$stmt->execute([':id' => $id]);
$sala = $stmt->fetch(PDO::FETCH_ASSOC);

// Si no se encuentra la sala, mostrar aviso y salir
if (!$sala) {
    echo "<p>⚠️ Sala no encontrada.</p>";
    return;
}

// --- SUBIDA DE FOTOS ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['fotos'])) {
    foreach ($_FILES['fotos']['tmp_name'] as $i => $tmp) {
        if ($_FILES['fotos']['error'][$i] === UPLOAD_ERR_OK) {
            $nombre = $_FILES['fotos']['name'][$i];
            $ext = strtolower(pathinfo($nombre, PATHINFO_EXTENSION));

            // Solo se permiten imágenes con extensiones válidas
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                $nuevo_nombre = uniqid("sala_{$id}_") . '.' . $ext;
                move_uploaded_file($tmp, "uploads/fotos_salas/$nuevo_nombre");

                 // Guarda la ruta de la foto en la base de datos
                $stmt = $db->prepare("INSERT INTO fotos_sala (id_sala, archivo) VALUES (:id_sala, :archivo)");
                $stmt->execute([':id_sala' => $id, ':archivo' => $nuevo_nombre]);

                // Registra acción en el log
                $log = $db->prepare("INSERT INTO log (descripcion, fecha) VALUES (:desc, NOW())");
                $log->execute([':desc' => "Foto subida a sala ID: $id"]);

            }
        }
    }
    // Redirige para evitar reenvío del formulario
    header("Location: index.php?page=salas_admin&action=fotos&id=$id");
    exit;
}

// --- ELIMINACIÓN DE FOTO ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_foto'])) {
    $foto_id = intval($_POST['delete_foto']);

    // Busca el archivo de la foto
    $stmt = $db->prepare("SELECT archivo FROM fotos_sala WHERE id = :id AND id_sala = :id_sala");
    $stmt->execute([':id' => $foto_id, ':id_sala' => $id]);
    $foto = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($foto) {
        // Intenta eliminar el archivo físicamente
        @unlink("uploads/fotos_salas/" . $foto['archivo']);

        // Elimina la entrada en la tabla
        $stmt = $db->prepare("DELETE FROM fotos_sala WHERE id = :id");
        $stmt->execute([':id' => $foto_id]);

        // Registra acción en el log
        $log = $db->prepare("INSERT INTO log (descripcion, fecha) VALUES (:desc, NOW())");
        $log->execute([':desc' => "Foto eliminada en sala ID: $id"]);


    }

    // Redirige para evitar reenvío
    header("Location: index.php?page=salas_admin&action=fotos&id=$id");
    exit;
}

// --- CARGAR TODAS LAS FOTOS DE LA SALA ---
$stmt = $db->prepare("SELECT * FROM fotos_sala WHERE id_sala = :id");
$stmt->execute([':id' => $id]);
$fotos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- INTERFAZ: MOSTRAR FOTOS EXISTENTES -->

<h2 class="seccion-titulo">Gestionar Fotos - <?= htmlspecialchars($sala['nombre']) ?></h2>

<?php if (count($fotos) > 0): ?>
    <div style="display:flex; flex-wrap: wrap; gap: 15px;">
        <?php foreach ($fotos as $foto): ?>
            <?php
                $archivo = basename($foto['archivo']);

                // Rutas web
                $ruta_defecto_web = 'uploads/fotos_salas_por_defecto/' . $archivo;
                $ruta_uploads_web = 'uploads/fotos_salas/' . $archivo;

                // Rutas físicas
                $ruta_defecto_fs = __DIR__ . '/../../uploads/fotos_salas_por_defecto/' . $archivo;
                $ruta_uploads_fs = __DIR__ . '/../../uploads/fotos_salas/' . $archivo;

                // Determina ruta real según existencia
                if (file_exists($ruta_defecto_fs)) {
                    $ruta_img = $ruta_defecto_web;
                } else {
                    $ruta_img = $ruta_uploads_web;
                }
            ?>
            <div style="text-align:center;">
                <img src="<?= htmlspecialchars($ruta_img) ?>" width="150" style="border-radius:8px;">
                <!-- Botón para eliminar la foto -->
                <form method="post" style="margin-top:5px;" novalidate>
                    <input type="hidden" name="delete_foto" value="<?= $foto['id'] ?>">
                    <button type="submit" onclick="return confirm('¿Eliminar esta foto?')">❌ Eliminar</button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <p>No hay fotos subidas para esta sala.</p>
<?php endif; ?>

<hr>

<!-- INTERFAZ: FORMULARIO PARA SUBIR NUEVAS FOTOS -->
 
<h3 class="seccion-titulo">Subir nuevas fotos</h3>
<div class="formulario-centrado">
    <form method="post" enctype="multipart/form-data" novalidate>
        <input type="file" name="fotos[]" multiple accept="image/*" required>
        <button type="submit">Subir fotos</button>
    </form>
</div>
