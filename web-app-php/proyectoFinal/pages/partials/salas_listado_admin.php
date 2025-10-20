<?php

// Consulta todas las salas ordenadas por nombre
$stmt = $db->query("SELECT * FROM salas ORDER BY nombre");
$salas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Para cada sala, obtiene sus fotos asociadas
foreach ($salas as &$sala) {
    $stmt_fotos = $db->prepare("SELECT archivo FROM fotos_sala WHERE id_sala = :id");
    $stmt_fotos->execute([':id' => $sala['id']]);
    $sala['fotos'] = $stmt_fotos->fetchAll(PDO::FETCH_COLUMN);
}
unset($sala); // Libera referencia
?>

<!-- INTERFAZ: LISTADO DE SALAS PARA ADMINISTRACIÓN -->
<h2 class="seccion-titulo">Listado de Salas (Administración)</h2>

<!-- Botón para añadir nueva sala -->
<p><a href="index.php?page=salas_admin&action=add" class="btn btn-principal">+ Añadir nueva sala</a></p>

<?php foreach ($salas as $sala): ?>
    <div class="sala-box">
        <h3><?= htmlspecialchars($sala['nombre']) ?></h3>
        <p><strong>Ubicación:</strong> <?= htmlspecialchars($sala['ubicacion']) ?></p>
        <p><strong>Capacidad:</strong> <?= $sala['capacidad'] ?> personas</p>
        <p><?= nl2br(htmlspecialchars($sala['descripcion'])) ?></p>

            <!-- Si tiene fotos, mostrarlas -->
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
                            $ruta_defecto_fs = __DIR__ . '/../../uploads/fotos_salas_por_defecto/' . $foto;
                            $ruta_uploads_fs = __DIR__ . '/../../uploads/fotos_salas/' . $foto;

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
                        <!-- Imagen -->
                        <img src="<?= htmlspecialchars($ruta_final) ?>" alt="<?= htmlspecialchars($info_alt) ?>">
                        <!-- Aviso si la imagen no existe -->
                        <?php if (str_starts_with($info_alt, '❌')): ?>
                            <pre style="color:red; font-size: 0.9em"><?= nl2br(htmlspecialchars($info_alt)) ?></pre>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        <!-- Botones de acción del administrador -->
        <p class="acciones-admin">
            <a href="index.php?page=salas_admin&action=edit&id=<?= $sala['id'] ?>" class="btn btn-secundario">🖊️ Editar</a>
            <a href="index.php?page=salas_admin&action=fotos&id=<?= $sala['id'] ?>" class="btn btn-fotos">📸 Fotos</a>
            <a href="index.php?page=salas_admin&action=delete&id=<?= $sala['id'] ?>" class="btn btn-peligro" onclick="return confirm('Confirmar borrado de la sala')">❌ Borrar</a>
        </p>

    </div>
<?php endforeach; ?>
