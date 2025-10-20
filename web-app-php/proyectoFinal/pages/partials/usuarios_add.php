<?php
// Solo accesible para administradores
if (!is_admin()) {
    header("Location: index.php?page=home");
    exit;
}

$errores = []; // Array para almacenar errores del formulario

// Si se ha enviado el formulario por POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recoge y limpia los datos del formulario
    $nombre = trim($_POST['nombre'] ?? '');
    $apellidos = trim($_POST['apellidos'] ?? '');
    $dni = trim($_POST['dni'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $clave = $_POST['clave'] ?? '';
    $rol = $_POST['rol'] ?? 'Usuario';

    // Validaciones
    if (!$nombre || !$apellidos || !$dni || !$email || !$clave) {
        $errores[] = "Todos los campos son obligatorios.";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores[] = "Email no válido.";
    }
    if (strlen($clave) < 4) {
        $errores[] = "La clave debe tener al menos 4 caracteres.";
    }
    if (!in_array($rol, ['Administrador', 'Usuario', 'Cliente'])) {
        $errores[] = "Rol no válido.";
    }

    // Comprobar si el email ya está registrado
    $stmt = $db->prepare("SELECT COUNT(*) FROM usuarios WHERE email = :email");
    $stmt->execute([':email' => $email]);
    if ($stmt->fetchColumn() > 0) {
        $errores[] = "El email ya está registrado.";
    }

    // Procesamiento de la foto (opcional)
    $foto_nombre = null;
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
            $foto_nombre = uniqid("foto_") . "." . $ext;
            move_uploaded_file($_FILES['foto']['tmp_name'], "uploads/fotos_usuarios/" . $foto_nombre);
        } else {
            $errores[] = "Formato de foto no válido.";
        }
    }

    // Si no hay errores, insertar el nuevo usuario
    if (empty($errores)) {
        $stmt = $db->prepare("INSERT INTO usuarios (nombre, apellidos, dni, email, clave, foto, rol)
                               VALUES (:n, :a, :d, :e, :c, :f, :r)");
        $stmt->execute([
            ':n' => $nombre,
            ':a' => $apellidos,
            ':d' => $dni,
            ':e' => $email,
            ':c' => password_hash($clave, PASSWORD_DEFAULT), // Clave cifrada
            ':f' => $foto_nombre,
            ':r' => $rol
        ]);
        // Redirige al listado de usuarios tras insertar
        header("Location: index.php?page=usuarios_admin");
        exit;
    }
}
?>

<!-- FORMULARIO: AÑADIR NUEVO USUARIO -->

<h2 class="seccion-titulo" >Añadir Nuevo Usuario</h2>

<!-- Mostrar errores si existen -->
<?php if (!empty($errores)): ?>
    <ul class="errores">
        <?php foreach ($errores as $e): ?>
            <li>❌ <?= htmlspecialchars($e) ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<!-- Formulario de alta de usuario -->
<div class="formulario-centrado">
    <form method="post" enctype="multipart/form-data" novalidate>
        <label>Nombre:</label>
        <input type="text" name="nombre" required>

        <label>Apellidos:</label>
        <input type="text" name="apellidos" required>

        <label>DNI:</label>
        <input type="text" name="dni" required>

        <label>Email:</label>
        <input type="email" name="email" required>

        <label>Clave:</label>
        <input type="password" name="clave" required>

        <label>Foto (opcional):</label>
        <input type="file" name="foto" accept="image/*">

        <label>Rol:</label>
        <select name="rol">
            <option value="Usuario">Usuario</option>
            <option value="Cliente">Cliente</option>
            <option value="Administrador">Administrador</option>
        </select>

        <button type="submit">Guardar Usuario</button>
    </form>
</div>