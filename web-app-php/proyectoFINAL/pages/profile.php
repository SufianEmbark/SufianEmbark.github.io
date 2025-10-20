<?php
// Solo usuarios autenticados pueden acceder a su perfil
if (!is_logged_in()) {
    header("Location: index.php?page=login");
    exit;
}

$usuario = current_user(); // Obtiene datos del usuario actual
$errores = [];             // Array para almacenar errores de validación
$exito = null;             // Mensaje de éxito tras actualización

// Al recargar la página perdemos el valor de exito, así que usamos la variable temporal
if (isset($_SESSION['mensaje_exito'])) {
    $exito = $_SESSION['mensaje_exito'];
    unset($_SESSION['mensaje_exito']);  // lo borramos para que no se muestre más
}

// --- PROCESAR FORMULARIO DE ACTUALIZACIÓN ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nuevo_email = trim($_POST['email'] ?? '');
    $nueva_clave = $_POST['clave'] ?? '';
    $foto = $_FILES['foto'] ?? null;

    // Validar nuevo email
    if ($nuevo_email !== $usuario['email']) {
        if (!filter_var($nuevo_email, FILTER_VALIDATE_EMAIL)) {
            $errores[] = "Email no válido.";
        } else {
            // Verificar que no esté en uso por otro usuario
            $stmt = $db->prepare("SELECT COUNT(*) FROM usuarios WHERE email = :email AND id != :id");
            $stmt->execute([':email' => $nuevo_email, ':id' => $usuario['id']]);
            if ($stmt->fetchColumn() > 0) {
                $errores[] = "Ese email ya está en uso.";
            }
        }
    }

    // Procesar nueva foto de perfil si se ha subido
    $nombre_foto = $usuario['foto']; // Foto actual
    if ($foto && $foto['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($foto['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
            $nombre_foto = uniqid('foto_') . '.' . $ext;
            move_uploaded_file($foto['tmp_name'], "uploads/fotos_usuarios/$nombre_foto");
        } else {
            $errores[] = "Formato de foto no permitido.";
        }
    }

    // Si no hay errores, actualizar en base de datos
    if (empty($errores)) {
        $campos = [
            'email' => $nuevo_email,
            'foto' => $nombre_foto,
        ];
        $sql = "UPDATE usuarios SET email = :email, foto = :foto";

        // Si el usuario introdujo una nueva clave válida
        if (!empty($nueva_clave)) {
            if (strlen($nueva_clave) < 4) {
                $errores[] = "La clave debe tener al menos 4 caracteres.";
            } else {
                $sql .= ", clave = :clave";
                $campos['clave'] = password_hash($nueva_clave, PASSWORD_DEFAULT);
            }
        }

        $sql .= " WHERE id = :id";
        $campos['id'] = $usuario['id'];

        // Ejecutar actualización si no hay errores
        if (empty($errores)) {
            $stmt = $db->prepare($sql);
            $stmt->execute($campos);

            // Recargar datos del usuario actualizado
            $stmt = $db->prepare("SELECT * FROM usuarios WHERE id = :id");
            $stmt->execute([':id' => $usuario['id']]);
            $_SESSION['usuario'] = $stmt->fetch(PDO::FETCH_ASSOC);

            // Registrar en el log
            $log = $db->prepare("INSERT INTO log (descripcion, fecha) VALUES (:desc, NOW())");
            $log->execute([':desc' => "Perfil actualizado: " . $usuario['email']]);

            // Redirigir con mensaje de éxito
            $_SESSION['mensaje_exito'] = "✅ Perfil actualizado correctamente.";
            header("Location: index.php?page=profile&actualizado=1");
        }
    }
}

// Se vuelve a obtener usuario por si se actualizó
$usuario = current_user();
?>

<!-- INTERFAZ HTML DEL PERFIL -->
<div class="perfil-container">
    <h2 class="perfil-title">Mi Perfil</h2>

    <!-- Mensaje de éxito si se ha actualizado -->
    <?php if ($exito): ?>
        <p class="mensaje-exito"><?= $exito ?></p>
    <?php endif; ?>

    <!-- Muestra errores si los hay -->
    <?php if (!empty($errores)): ?>
        <ul class="registro-errores">
            <?php foreach ($errores as $e): ?>
                <li>❌ <?= htmlspecialchars($e) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <!-- Información actual del usuario -->
    <div class="perfil-datos">

        <!-- Foto de perfil actual si existe (subida o default) -->
        <?php if (!empty($usuario['foto'])): ?>
            <?php if (file_exists("uploads/fotos_usuarios/" . $usuario['foto'])): ?>
                <img src="uploads/fotos_usuarios/<?= htmlspecialchars($usuario['foto']) ?>" alt="Foto de perfil" class="foto-perfil-grande">
            <?php else: ?>
                <img src="uploads/fotos_usuarios_por_defecto/<?= htmlspecialchars($usuario['foto']) ?>" alt="Foto de perfil" class="foto-perfil-grande">
            <?php endif; ?>
        <?php endif; ?>

        <p><strong>Nombre:</strong> <?= htmlspecialchars($usuario['nombre']) ?></p>
        <p><strong>Apellidos:</strong> <?= htmlspecialchars($usuario['apellidos']) ?></p>
        <p><strong>DNI:</strong> <?= htmlspecialchars($usuario['dni']) ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($usuario['email']) ?></p>
        <p><strong>Rol:</strong> <?= htmlspecialchars($usuario['rol']) ?></p>
    </div>

    <hr>

    <!-- Formulario para editar el perfil -->
    <form action="index.php?page=profile" method="post" enctype="multipart/form-data" class="registro-form" novalidate>
        <label for="email">Nuevo email</label>
        <input type="email" id="email" name="email" value="<?= htmlspecialchars($usuario['email']) ?>" required>

        <label for="clave">Nueva clave (dejar vacío si no deseas cambiarla)</label>
        <input type="password" id="clave" name="clave">

        <label for="foto">Nueva foto de perfil</label>
        <input type="file" id="foto" name="foto" accept="image/*">

        <button type="submit" class="registro-btn">Guardar cambios</button>
    </form>
</div>
