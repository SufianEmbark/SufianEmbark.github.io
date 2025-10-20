<?php
// Solo administradores pueden acceder
if (!is_admin()) {
    header("Location: index.php?page=home");
    exit;
}

// Obtener el ID del usuario a editar desde la URL
$id = intval($_GET['id'] ?? 0);

// Consultar datos actuales del usuario en la base de datos
$stmt = $db->prepare("SELECT * FROM usuarios WHERE id = :id");
$stmt->execute([':id' => $id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

// Si no existe el usuario, mostrar mensaje y salir
if (!$usuario) {
    echo "<p>Usuario no encontrado.</p>";
    return;
}

$errores = [];

// Procesar formulario al enviarse vía POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $apellidos = trim($_POST['apellidos'] ?? '');
    $dni = trim($_POST['dni'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $rol = $_POST['rol'] ?? 'Usuario';
    $clave = $_POST['clave'] ?? '';

    // Validaciones básicas de campos obligatorios y formatos
    if (!$nombre || !$apellidos || !$dni || !$email) {
        $errores[] = "Todos los campos son obligatorios (excepto clave y foto).";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores[] = "Email no válido.";
    }
    if (!validar_dni($dni)) {
        $errores[] = "DNI no válido (letra incorrecta).";
    }
    if (!in_array($rol, ['Administrador', 'Usuario', 'Cliente'])) {
        $errores[] = "Rol no válido.";
    }

    // Comprobar que no exista otro usuario con el mismo email
    $stmt = $db->prepare("SELECT COUNT(*) FROM usuarios WHERE email = :email AND id != :id");
    $stmt->execute([':email' => $email, ':id' => $id]);
    if ($stmt->fetchColumn() > 0) {
        $errores[] = "El email ya está registrado por otro usuario.";
    }

    // Gestión de subida de foto nueva (opcional)
    $foto_nombre = $usuario['foto'];
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
            $foto_nombre = uniqid("foto_") . "." . $ext;
            move_uploaded_file($_FILES['foto']['tmp_name'], "uploads/fotos_usuarios/" . $foto_nombre);
        } else {
            $errores[] = "Formato de foto no válido.";
        }
    }

    // Si no hay errores, actualizar datos en la base de datos
    if (empty($errores)) {
        $query = "UPDATE usuarios SET nombre = :n, apellidos = :a, dni = :d, email = :e, foto = :f, rol = :r";
        $params = [
            ':n' => $nombre,
            ':a' => $apellidos,
            ':d' => $dni,
            ':e' => $email,
            ':f' => $foto_nombre,
            ':r' => $rol,
            ':id' => $id
        ];

        // Si se cambia la clave, validar longitud y hash
        if (!empty($clave)) {
            if (strlen($clave) < 4) {
                $errores[] = "La nueva clave debe tener al menos 4 caracteres.";
            } else {
                $query .= ", clave = :c";
                $params[':c'] = password_hash($clave, PASSWORD_DEFAULT);
            }
        }

        // Si sigue sin errores, ejecutar actualización
        if (empty($errores)) {
            $query .= " WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->execute($params);

            // Si el usuario editado es el actual, refrescar datos en sesión
            if ($id === $_SESSION['usuario']['id']) {
                $stmt = $db->prepare("SELECT * FROM usuarios WHERE id = :id");
                $stmt->execute([':id' => $id]);
                $_SESSION['usuario'] = $stmt->fetch(PDO::FETCH_ASSOC);
            }

            // Redirigir a la lista de usuarios
            header("Location: index.php?page=usuarios_admin");
            exit;
        }
    }
}
?>

<h2 class="seccion-titulo">Editar Usuario</h2>

<?php if (!empty($errores)): ?>
    <ul class="errores">
        <?php foreach ($errores as $e): ?>
            <li>❌ <?= htmlspecialchars($e) ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<div class="formulario-centrado">
    <form method="post" enctype="multipart/form-data" novalidate>
        <label>Nombre:</label>
        <input type="text" name="nombre" value="<?= htmlspecialchars($usuario['nombre']) ?>" required>

        <label>Apellidos:</label>
        <input type="text" name="apellidos" value="<?= htmlspecialchars($usuario['apellidos']) ?>" required>

        <label>DNI:</label>
        <input type="text" name="dni" value="<?= htmlspecialchars($usuario['dni']) ?>" required>

        <label>Email:</label>
        <input type="email" name="email" value="<?= htmlspecialchars($usuario['email']) ?>" required>

        <label>Clave (dejar en blanco para no cambiar):</label>
        <input type="password" name="clave">

        <label>Foto nueva (opcional):</label>
        <input type="file" name="foto" accept="image/*">

        <label>Rol:</label>
        <select name="rol">
            <option value="Usuario" <?= $usuario['rol'] === 'Usuario' ? 'selected' : '' ?>>Usuario</option>
            <option value="Cliente" <?= $usuario['rol'] === 'Cliente' ? 'selected' : '' ?>>Cliente</option>
            <option value="Administrador" <?= $usuario['rol'] === 'Administrador' ? 'selected' : '' ?>>Administrador</option>
        </select>

        <button type="submit">Guardar Cambios</button>
    </form>
</div>