<?php
// Incluye funciones auxiliares necesarias
require_once __DIR__ . '/../includes/functions.php';

// Solo accesible por administradores
if (!is_admin()) {
    header("Location: index.php?page=error");
    exit;
}

$errores = []; // Array para recoger errores de validación o subida
$exito = isset($_GET['ok']); // Indica si se ha actualizado con éxito la configuración

// --- Procesamiento del formulario al enviar ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recoge y limpia los datos enviados
    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $hora_apertura = $_POST['hora_apertura'] ?? '';
    $hora_cierre = $_POST['hora_cierre'] ?? '';

    $hay_cambios = false; // Para saber si se cambió algo

    if ($nombre !== '') {
        set_config('nombre_centro', $nombre);
        $hay_cambios = true;
    }

    if ($descripcion !== '') {
        set_config('descripcion_centro', $descripcion);
        $hay_cambios = true;
    }

    if ($hora_apertura !== '') {
        set_config('hora_apertura', $hora_apertura);
        $hay_cambios = true;
    }

    if ($hora_cierre !== '') {
        set_config('hora_cierre', $hora_cierre);
        $hay_cambios = true;
    }

    // Procesamiento del logo (si se ha subido uno)
    if (!empty($_FILES['logo']['name'])) {
        $directorio_destino = __DIR__ . '/../uploads/foto_logo/';
        if (!is_dir($directorio_destino)) {
            mkdir($directorio_destino, 0777, true);
        }

        $ruta_destino = $directorio_destino . 'logo.png';

        if (!move_uploaded_file($_FILES['logo']['tmp_name'], $ruta_destino)) {
            $errores[] = "Error al subir el logotipo.";
        } else {
            $hay_cambios = true;
        }
    }

    // Solo redirigir si hubo algún cambio
    if ($hay_cambios && empty($errores)) {
        header("Location: index.php?page=configuracion&ok=1");
        exit;
    } elseif (!$hay_cambios) {
        $errores[] = "No se ha modificado ningún campo.";
    }
}

// --- Obtención de valores actuales de configuración ---
$nombre_actual = get_config('nombre_centro');
$descripcion_actual = get_config('descripcion_centro');
$hora_apertura_actual = get_config('hora_apertura') ?: '08:00';
$hora_cierre_actual = get_config('hora_cierre') ?: '20:00';
?>

<!-- TÍTULO DE LA SECCIÓN -->
<h2 class="seccion-titulo">Configuración del Centro</h2>

<!-- Mensaje de éxito -->
<?php if ($exito): ?>
    <p class="mensaje-exito">✅ Configuración actualizada correctamente.</p>
<?php endif; ?>

<!-- Mensajes de error -->
<?php if ($errores): ?>
    <ul class="registro-errores">
        <?php foreach ($errores as $e): ?>
            <li>❌ <?= htmlspecialchars($e) ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<!-- FORMULARIO DE CONFIGURACIÓN -->
<div class="formulario-centrado">
    <form method="post" enctype="multipart/form-data" class="registro-form" style="max-width: 600px;" novalidate>
        <label>Nombre del centro:</label>
        <input type="text" name="nombre" required value="<?= htmlspecialchars($nombre_actual) ?>">

        <label>Descripción:</label>
        <textarea name="descripcion" required rows="4"><?= htmlspecialchars($descripcion_actual) ?></textarea>

        <label>Logotipo (png, opcional):</label>
        <input type="file" name="logo" accept="image/png">

        <label>Hora de apertura:</label>
        <input type="time" name="hora_apertura" required value="<?= $hora_apertura_actual ?>">

        <label>Hora de cierre:</label>
        <input type="time" name="hora_cierre" required value="<?= $hora_cierre_actual ?>">

        <button type="submit" class="registro-btn">Guardar configuración</button>
    </form>
</div>