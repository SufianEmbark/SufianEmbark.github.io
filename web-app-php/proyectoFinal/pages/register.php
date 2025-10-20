<?php
$errores = []; // Array para almacenar errores de validación
$exito = false; // Flag para saber si el registro fue exitoso

// --- PROCESAR FORMULARIO ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recoge y limpia los datos del formulario
    $nombre = trim($_POST['nombre'] ?? '');
    $apellidos = trim($_POST['apellidos'] ?? '');
    $dni = strtoupper(trim($_POST['dni'] ?? ''));
    $email = trim($_POST['email'] ?? '');
    $clave = $_POST['clave'] ?? '';
    $foto = $_FILES['foto'] ?? null;

    // Validaciones
    if ($nombre === '') $errores[] = "El nombre es obligatorio.";
    if ($apellidos === '') $errores[] = "Los apellidos son obligatorios.";
    if (!validar_dni($dni)) $errores[] = "El DNI no es válido.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errores[] = "El email no es válido.";
    if (strlen($clave) < 4) $errores[] = "La clave debe tener al menos 4 caracteres.";

    // Comprobar si el email ya existe
    $stmt = $db->prepare("SELECT COUNT(*) FROM usuarios WHERE email = :email");
    $stmt->execute([':email' => $email]);
    if ($stmt->fetchColumn() > 0) {
        $errores[] = "Ya existe un usuario con ese email.";
    }

    // Subida de foto
    $foto_nombre = null;
    if ($foto && $foto['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($foto['name'], PATHINFO_EXTENSION);
        if (in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif'])) {
            $foto_nombre = uniqid('foto_') . '.' . $ext;
            move_uploaded_file($foto['tmp_name'], "uploads/fotos_usuarios/$foto_nombre");
        } else {
            $errores[] = "La foto debe ser JPG, PNG o GIF.";
        }
    }

    // Si todo OK, insertar en la BBDD
    if (empty($errores)) {
        $clave_hash = password_hash($clave, PASSWORD_DEFAULT);

        // Inserta nuevo usuario con rol "Cliente"
        $stmt = $db->prepare("INSERT INTO usuarios (nombre, apellidos, dni, email, clave, foto, rol) 
                              VALUES (:nombre, :apellidos, :dni, :email, :clave, :foto, 'Cliente')");
        $stmt->execute([
            ':nombre' => $nombre,
            ':apellidos' => $apellidos,
            ':dni' => $dni,
            ':email' => $email,
            ':clave' => $clave_hash,
            ':foto' => $foto_nombre
        ]);
        $exito = true;
    }

    // Registro de log del intento (éxito o fallo)
    $log = $db->prepare("INSERT INTO log (descripcion, fecha) VALUES (:desc, NOW())");
    $log->execute([':desc' => "Nuevo usuario registrado: " . $email]);
}
?>

<!-- FORMULARIO DE REGISTRO -->
<div class="registro-container">
    <h2 class="registro-title">Registro de Usuario</h2>

    <?php if ($exito): ?>
        <!-- Mensaje de éxito con enlace a login -->
        <p class="mensaje-exito">✅ Registro completado correctamente. <a href="index.php?page=login">Iniciar sesión</a></p>
    <?php else: ?>
        <?php if (!empty($errores)): ?>
             <!-- Lista de errores si los hay -->
            <ul class="registro-errores">
                <?php foreach ($errores as $e): ?>
                    <li>❌ <?= htmlspecialchars($e) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <!-- Formulario de alta de nuevo usuario -->
        <form action="index.php?page=register" method="post" enctype="multipart/form-data" class="registro-form" novalidate>
            <label for="nombre">Nombre</label>
            <input type="text" id="nombre" name="nombre" value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>" required>

            <label for="apellidos">Apellidos</label>
            <input type="text" id="apellidos" name="apellidos" value="<?= htmlspecialchars($_POST['apellidos'] ?? '') ?>" required>

            <label for="dni">DNI</label>
            <input type="text" id="dni" name="dni" value="<?= htmlspecialchars($_POST['dni'] ?? '') ?>" required>

            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>

            <label for="clave">Clave</label>
            <input type="password" id="clave" name="clave" required>

            <label for="foto">Fotografía</label>
            <input type="file" id="foto" name="foto" accept="image/*">

            <button type="submit" class="registro-btn">Registrarse</button>
        </form>
    <?php endif; ?>
</div>
