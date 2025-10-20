<?php
// Verifica si el usuario es administrador; si no lo es, redirige al inicio
if (!is_admin()) {
    header("Location: index.php?page=home");
    exit;
}

// Se recoge la acción del formulario (backup, restaurar o reiniciar)
$accion = $_POST['accion'] ?? null;
$mensaje = '';

// Conexión MySQL para todas las operaciones
$host = 'localhost';
$dbname = 'sufianembark2425';
$user = 'sufianembark2425';
$pass = 'ueNg8ccuuLDCKUWc';

// Intenta conectar a la base de datos usando PDO
try {
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error conectando a la base de datos: " . $e->getMessage());
}


// --- BACKUP DE LA BBDD ---
if ($accion === 'backup') {
    try {
        // Encabezado del fichero de backup
        $sql_dump = "-- Backup generado desde PHP\n";
        $sql_dump .= "-- Fecha: " . date("Y-m-d H:i:s") . "\n\n";

        // Obtiene la lista de tablas
        $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

        foreach ($tables as $table) {
            // Añade DROP TABLE y CREATE TABLE
            $create = $db->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_ASSOC);
            $sql_dump .= "DROP TABLE IF EXISTS `$table`;\n";
            $sql_dump .= $create['Create Table'] . ";\n\n";

            // Añade los INSERT con los datos de cada tabla
            $rows = $db->query("SELECT * FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as $row) {
                $columns = array_map(function ($col) {
                    return "`$col`";
                }, array_keys($row));

                $values = array_map(function ($value) use ($db) {
                    return $db->quote($value);
                }, array_values($row));

                $sql_dump .= "INSERT INTO `$table` (" . implode(", ", $columns) . ") VALUES (" . implode(", ", $values) . ");\n";
            }

            $sql_dump .= "\n";
        }

        // Envía el contenido como archivo SQL descargable
        header('Content-Type: application/sql');
        header('Content-Disposition: attachment; filename="backup_' . date('Ymd_His') . '.sql"');
        echo $sql_dump;
        exit;

    } catch (PDOException $e) {
        $mensaje = "❌ Error al generar el backup: " . $e->getMessage();
    }
}



// --- RESTAURACIÓN DE LA BBDD ---
if ($accion === 'restaurar' && isset($_FILES['sql_file'])) {

    $archivo = $_FILES['sql_file']['tmp_name'];
    $tipo = $_FILES['sql_file']['type'];

    // Validaciones del archivo
    if (!is_uploaded_file($archivo)) {
        $mensaje = "❌ Error al subir el archivo.";
    } elseif (!in_array($tipo, ['application/sql', 'text/plain', 'application/octet-stream'])) {
        $mensaje = "❌ El archivo no parece ser un archivo SQL válido.";
    } else {
        try {
            session_write_close(); // Cierra sesión para evitar bloqueos durante la restauración

            // Relee el contenido del archivo SQL
            $sql = file_get_contents($archivo);
            $db->exec($sql); // Ejecuta todo el contenido SQL

            $mensaje = "✅ Restauración completada correctamente.";

            // Reasigna contraseñas por defecto (cifradas)
            try {
                $usuarios_a_actualizar = [
                    ['email' => 'admin@void.ugr.es', 'clave' => 'admin'],
                    ['email' => 'javier@void.ugr.es', 'clave' => 'javier'],
                    ['email' => 'pedro@void.ugr.es', 'clave' => 'pedro'],
                    ['email' => 'anita@void.ugr.es', 'clave' => 'anita'],
                    ['email' => 'maria@void.ugr.es', 'clave' => 'maria'],
                ];

                $stmt = $db->prepare("UPDATE usuarios SET clave = :clave WHERE email = :email");

                foreach ($usuarios_a_actualizar as $u) {
                    $stmt->execute([
                        ':clave' => password_hash($u['clave'], PASSWORD_DEFAULT),
                        ':email' => $u['email']
                    ]);
                }
            } catch (PDOException $e) {
                error_log("Error actualizando claves tras restauración: " . $e->getMessage());
            }
            limpiar_directorio('uploads/fotos_usuarios');
            limpiar_directorio('uploads/fotos_salas');
            limpiar_directorio('uploads/foto_logo');

            // Limpia sesión y redirige a login
            session_unset();
            session_destroy();
            setcookie(session_name(), '', time() - 3600, '/');
            header("Location: index.php?page=login");
            exit;

        } catch (PDOException $e) {
            $mensaje = "❌ Error al ejecutar el script SQL: " . $e->getMessage();
        }
    }
}


// --- REINICIO DE LA BBDD ---
if ($accion === 'reiniciar') {
    // Tablas a vaciar
    $tablas = ['reservas', 'comentarios', 'fotos_sala', 'log', 'config', 'salas', 'usuarios'];
    foreach ($tablas as $t) {
        $db->exec("DELETE FROM $t"); // Borra los datos
        $db->exec("ALTER TABLE $t AUTO_INCREMENT = 1"); // Reinicia los autoincrementales
    }

    $mensaje = "⚠️ Se reinició la base de datos (solo datos, tablas conservadas). 
                Se ha creado un nuevo ususario administrador.";

    // Borra configuración y archivos
    $db->exec("DELETE FROM config");

    limpiar_directorio('uploads/fotos_usuarios');
    limpiar_directorio('uploads/fotos_salas');
    limpiar_directorio('uploads/foto_logo');

    // Finaliza la sesión y redirige a login
    session_unset();
    session_destroy();
    header("Location: index.php?page=login");
    exit;

}
?>

<!-- INTERFAZ HTML -->

<h2 class="seccion-titulo">Administración de Base de Datos</h2>

<!-- Muestra mensaje si existe -->
<?php if ($mensaje): ?>
    <p><strong><?= htmlspecialchars($mensaje) ?></strong></p>
<?php endif; ?>

<!-- Formulario para hacer copia de seguridad -->
<h3>📥 Descargar Backup</h3>
<form method="post" novalidate>
    <input type="hidden" name="accion" value="backup">
    <button type="submit" onclick="return confirm('¿Seguro que deseas descargar una copia de seguridad?')">Generar Backup</button>
</form>

<!-- Formulario para restaurar desde .sql -->
<h3>♻️ Restaurar desde archivo .sql</h3>
<form method="post" enctype="multipart/form-data" novalidate>
    <input type="hidden" name="accion" value="restaurar">
    <input type="file" name="sql_file" accept=".sql" required>
    <button type="submit" onclick="return confirm('¿Seguro que deseas restaurar la base de datos desde este archivo?')">Restaurar</button>
</form>

<!-- Formulario para reiniciar la base de datos -->
<h3>🧨 Reiniciar Base de Datos</h3>
<form method="post" novalidate>
    <input type="hidden" name="accion" value="reiniciar">
    <button type="submit" onclick="return confirm('Esto eliminará todos los datos. ¿Estás seguro?')">Reiniciar</button>
</form>
