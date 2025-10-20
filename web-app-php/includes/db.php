<?php
// Ruta al archivo de base de datos SQLite
$db_path = __DIR__ . '/../bbdd/basedatos.sqlite';

try {
    $db = new PDO("sqlite:$db_path");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Crear tablas si no existen
    $db->exec("
        CREATE TABLE IF NOT EXISTS usuarios (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nombre TEXT NOT NULL,
            apellidos TEXT NOT NULL,
            dni TEXT NOT NULL,
            email TEXT NOT NULL UNIQUE,
            clave TEXT NOT NULL,
            foto BLOB,
            rol TEXT NOT NULL CHECK(rol IN ('Administrador', 'Usuario', 'Cliente'))
        );

        CREATE TABLE IF NOT EXISTS salas (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nombre TEXT NOT NULL,
            ubicacion TEXT NOT NULL,
            capacidad INTEGER NOT NULL,
            descripcion TEXT,
            reservable INTEGER NOT NULL CHECK(reservable IN (0,1))
        );

        CREATE TABLE IF NOT EXISTS fotos_sala (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            id_sala INTEGER,
            foto BLOB,
            FOREIGN KEY(id_sala) REFERENCES salas(id) ON DELETE CASCADE
        );

        CREATE TABLE IF NOT EXISTS reservas (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            id_usuario INTEGER,
            id_sala INTEGER,
            motivo TEXT,
            fecha TEXT,
            hora_inicio TEXT,
            hora_fin TEXT,
            FOREIGN KEY(id_usuario) REFERENCES usuarios(id),
            FOREIGN KEY(id_sala) REFERENCES salas(id)
        );

        CREATE TABLE IF NOT EXISTS comentarios (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            id_usuario INTEGER,
            id_sala INTEGER,
            comentario TEXT,
            fecha TEXT,
            FOREIGN KEY(id_usuario) REFERENCES usuarios(id),
            FOREIGN KEY(id_sala) REFERENCES salas(id)
        );

        CREATE TABLE IF NOT EXISTS log (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            fecha TEXT,
            descripcion TEXT
        );

        CREATE TABLE IF NOT EXISTS config (
            clave TEXT PRIMARY KEY,
            valor TEXT
        );
    ");

    // Verifica si ya hay un administrador
    $stmt = $db->query("SELECT COUNT(*) FROM usuarios WHERE rol = 'Administrador'");
    if ($stmt->fetchColumn() == 0) {
        $clave = password_hash("admin", PASSWORD_DEFAULT);
        $db->exec("INSERT INTO usuarios (nombre, apellidos, dni, email, clave, rol)
                   VALUES ('Admin', 'General', '00000000T', 'admin@void.ugr.es', '$clave', 'Administrador')");
    }

} catch (PDOException $e) {
    die("Error al conectar o crear la base de datos: " . $e->getMessage());
}
?>
