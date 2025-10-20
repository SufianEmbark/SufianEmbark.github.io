<?php
$errores = []; // Array para almacenar mensajes de error

// Comprueba si el formulario se ha enviado por POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recoge los valores enviados y los limpia
    $email = trim($_POST['email'] ?? '');
    $clave = $_POST['clave'] ?? '';

    // Validación del email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores[] = "Introduce un email válido.";
    }

    // Validación de la clave (mínimo 4 caracteres)
    if (strlen($clave) < 4) {
        $errores[] = "La clave debe tener al menos 4 caracteres.";
    }

    // Si no hay errores de validación
    if (empty($errores)) {
        // Busca el usuario por su email
        $stmt = $db->prepare("SELECT * FROM usuarios WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verifica la clave con password_verify()
        if ($usuario && password_verify($clave, $usuario['clave'])) {
            $_SESSION['usuario'] = $usuario;

            // Registra en el log el inicio de sesión correcto
            $log = $db->prepare("INSERT INTO log (descripcion, fecha) VALUES (:desc, NOW())");
            $log->execute([':desc' => "Inicio de sesión: " . $usuario['email']]);

            header("Location: index.php?page=home");// Redirige al home
            exit;
        }
        else {
            $errores[] = "Error al procesar."; // Clave incorrecta o usuario no encontrado
        }
    }
    else {
        $errores[] = "Email o clave incorrectos."; // Error general de validación

        // Registra en el log el intento de login fallido
        $log2 = $db->prepare("INSERT INTO log (descripcion, fecha) VALUES (:desc, NOW())");
        $log2->execute([':desc' => "Intento de login fallido: $email"]);
    }
}
?>


<!-- CONTENIDO HTML DEL LOGIN -->
<div class="login-container">
    <!-- Imagen decorativa de login -->
    <div class="login-image-wrapper">
        <img src="assets/images/login_image.png" alt="Imagen login" class="login-image">
    </div>

    <!-- Título del formulario -->
    <h2 class="login-title">Inicio de Sesión</h2>

    <!-- Muestra errores si los hay -->
    <?php if (!empty($errores)): ?>
        <ul class="login-errores">
            <?php foreach ($errores as $e): ?>
                <li>❌ <?= htmlspecialchars($e) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <!-- Formulario de login -->
    <form action="index.php?page=login" method="post" class="login-form" novalidate>
        <label for="email">Email</label>
        <!-- Campo de email con valor persistente tras fallo -->
        <input type="email" id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>

        <label for="clave">Clave</label>
        <input type="password" id="clave" name="clave" required>

        <button type="submit" class="login-btn">Entrar</button>
    </form>
</div>
