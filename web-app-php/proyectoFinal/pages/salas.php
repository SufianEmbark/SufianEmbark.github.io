<?php
$usuario = current_user(); // Usuario actual
$is_admin = is_admin(); // es administrador?

// --- PROCESAMIENTO DE NUEVO COMENTARIO ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && is_logged_in()) {
    $texto = trim($_POST['comentario'] ?? '');
    $id_sala = intval($_POST['id_sala'] ?? 0);
    $id_usuario = $usuario['id'];

    if ($texto && $id_sala > 0) {
        // Inserta el comentario
        $stmt = $db->prepare("INSERT INTO comentarios (id_sala, id_usuario, comentario, fecha) VALUES (:sala, :usuario, :texto, NOW())");
        $stmt->execute([
            ':sala' => $id_sala,
            ':usuario' => $id_usuario,
            ':texto' => $texto
        ]);

        // Registra en el log
        $log = $db->prepare("INSERT INTO log (descripcion, fecha) VALUES (:desc, NOW())");
        $log->execute([':desc' => "Comentario en sala $id_sala por usuario " . $usuario['email']]);

        header("Location: index.php?page=salas");
        exit;
    }
}

// --- CARGAR LISTADO DE SALAS ---
$stmt = $db->query("SELECT * FROM salas ORDER BY nombre");
$salas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Añade las fotos a cada sala
foreach ($salas as &$sala) {
    $stmt_fotos = $db->prepare("SELECT archivo FROM fotos_sala WHERE id_sala = :id");
    $stmt_fotos->execute([':id' => $sala['id']]);
    $sala['fotos'] = $stmt_fotos->fetchAll(PDO::FETCH_COLUMN);
}

unset($sala); // Rompe la referencia

// --- COMENTARIOS agrupados por sala ---
$comentarios_stmt = $db->query("SELECT c.*, u.nombre, u.foto 
    FROM comentarios c 
    JOIN usuarios u ON c.id_usuario = u.id
    ORDER BY c.fecha DESC");
$comentarios = [];
foreach ($comentarios_stmt as $c) {
    $comentarios[$c['id_sala']][] = $c;
}
?>


<!-- INTERFAZ HTML PARA MOSTRAR SALAS -->

<div class="salas-container">
    <h2 class="seccion-titulo">Listado de Salas</h2>

    <?php foreach ($salas as $sala): ?>
        <div class="sala-box">
            <div class="sala-header">
                <h3><?= htmlspecialchars($sala['nombre']) ?></h3>

                <!-- Botones de edición y borrado si es admin -->
                <?php if ($is_admin): ?>
                    <div class="acciones-admin">
                        <a href="index.php?page=salas_admin&edit=<?= $sala['id'] ?>" class="btn-edit">✏️ Editar</a>
                        <a href="index.php?page=salas_admin&delete=<?= $sala['id'] ?>" class="btn-delete" onclick="return confirm('¿Borrar esta sala?');">🗑️ Borrar</a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Descripción de la sala -->
            <div class="sala-desc">
                <p><?= nl2br(htmlspecialchars($sala['descripcion'])) ?></p>
                <p><strong>Capacidad:</strong> <?= $sala['capacidad'] ?> personas</p>
                <p><strong>Ubicación:</strong> <?= htmlspecialchars($sala['ubicacion']) ?></p>
                <p class="estado-reservable">
                    <strong>Reservable:</strong>
                    <?php if ($sala['reservable']): ?>
                        <span class="reservable-si">Sí</span>
                    <?php else: ?>
                        <span class="reservable-no">No</span>
                    <?php endif; ?>
                </p>
            </div>

            

            <!-- Galería de fotos de la sala -->
            <?php if (!empty($sala['fotos'])): ?> 
                <div class="sala-fotos">
                    <?php foreach ($sala['fotos'] as $foto_crudo): ?>
                        <?php
                            // Asegura que solo sea el nombre del archivo
                            $foto = basename($foto_crudo);

                            // Rutas web (para el navegador)
                            $ruta_defecto_web = 'uploads/fotos_salas_por_defecto/' . $foto;
                            $ruta_uploads_web = 'uploads/fotos_salas/' . $foto;

                            // Rutas del sistema de archivos (para comprobar existencia)
                            $ruta_defecto_fs = __DIR__ . '/../uploads/fotos_salas_por_defecto/' . $foto;
                            $ruta_uploads_fs = __DIR__ . '/../uploads/fotos_salas/' . $foto;

                            // Decide qué ruta usar y prepara alt
                            if (file_exists($ruta_defecto_fs)) {
                                $ruta_final = $ruta_defecto_web;
                                $info_alt = "OK: Encontrada en $ruta_defecto_fs";
                            } elseif (file_exists($ruta_uploads_fs)) {
                                $ruta_final = $ruta_uploads_web;
                                $info_alt = "OK: Encontrada en $ruta_uploads_fs";
                            } else {
                                $ruta_final = 'assets/images/no-image.png';
                                $info_alt = "❌ NO ENCONTRADA: \n- $ruta_defecto_fs\n- $ruta_uploads_fs";
                            }
                        ?>
                        <img src="<?= htmlspecialchars($ruta_final) ?>" alt="<?= htmlspecialchars($info_alt) ?>">
                        <?php if (str_starts_with($info_alt, '❌')): ?>
                            <pre style="color:red; font-size: 0.9em"><?= nl2br(htmlspecialchars($info_alt)) ?></pre>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>



            <!-- Formulario para dejar un comentario -->                
            <?php if (is_logged_in()): ?>
                <form action="index.php?page=salas" method="post" class="form-comentario" novalidate>
                    <input type="hidden" name="id_sala" value="<?= $sala['id'] ?>">
                    <label for="comentario_<?= $sala['id'] ?>"><strong>Comentar:</strong></label>
                    <textarea id="comentario_<?= $sala['id'] ?>" name="comentario" required></textarea>
                    <button type="submit">Enviar</button>
                </form>
            <?php endif; ?>

            <!-- Comentarios existentes -->
            <?php if (!empty($comentarios[$sala['id']])): ?>
                <div class="comentarios">
                    <h4>Comentarios</h4>
                    <?php foreach ($comentarios[$sala['id']] as $c): ?>
                        <div class="comentario">

                    <?php if ($c['foto']): ?>

                        <?php
                            $foto = $c['foto']; // Definimos la variable que se usa en las rutas

                            // Rutas del sistema para comprobar existencia
                            $ruta_uploads_fs = __DIR__ . '/../uploads/fotos_usuarios/' . $foto;

                            // Rutas web
                            $ruta_uploads_web = 'uploads/fotos_usuarios/' . $foto;
                            $ruta_defecto_web = 'uploads/fotos_usuarios_por_defecto/' . $foto;

                            // Verificamos si la imagen personalizada existe
                            if (file_exists($ruta_uploads_fs)) {
                                $ruta_final = $ruta_uploads_web;
                            } else {
                                $ruta_final = $ruta_defecto_web;
                            }
                        ?>

                        <img src="<?php echo htmlspecialchars($ruta_final); ?>" alt="Foto" class="comentario-foto">

                    <?php endif; ?>


                            <strong><?= htmlspecialchars($c['nombre']) ?>:</strong>
                            <p><?= nl2br(htmlspecialchars($c['comentario'])) ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>


