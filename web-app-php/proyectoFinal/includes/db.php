<?php
// Carga funciones auxiliares
require_once "includes/functions.php";
// Credenciales de conexión MySQL
$host = 'localhost';
$dbname = 'sufianembark2425';
$user = 'sufianembark2425';
$pass = 'ueNg8ccuuLDCKUWc';

try {
    // Conecta con la base de datos MySQL usando PDO
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Crear tablas si no existen
$db->exec("
        CREATE TABLE IF NOT EXISTS usuarios (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nombre VARCHAR(100) NOT NULL,
            apellidos VARCHAR(100) NOT NULL,
            dni VARCHAR(9) NOT NULL,
            email VARCHAR(255) NOT NULL UNIQUE,
            clave VARCHAR(255) NOT NULL,
            foto LONGBLOB,
            rol ENUM('Administrador', 'Usuario', 'Cliente') NOT NULL
        );

        CREATE TABLE IF NOT EXISTS salas (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nombre VARCHAR(100) NOT NULL,
            ubicacion VARCHAR(100) NOT NULL,
            capacidad INT NOT NULL,
            descripcion TEXT,
            reservable TINYINT(1) NOT NULL
        );

        CREATE TABLE IF NOT EXISTS fotos_sala (
            id INT AUTO_INCREMENT PRIMARY KEY,
            id_sala INT,
            foto LONGBLOB,
            FOREIGN KEY(id_sala) REFERENCES salas(id) ON DELETE CASCADE
        );

        CREATE TABLE IF NOT EXISTS reservas (
            id INT AUTO_INCREMENT PRIMARY KEY,
            id_usuario INT,
            id_sala INT,
            motivo TEXT,
            fecha DATE,
            hora_inicio TIME,
            hora_fin TIME,
            FOREIGN KEY(id_usuario) REFERENCES usuarios(id),
            FOREIGN KEY(id_sala) REFERENCES salas(id)
        );

        CREATE TABLE IF NOT EXISTS comentarios (
            id INT AUTO_INCREMENT PRIMARY KEY,
            id_usuario INT,
            id_sala INT,
            comentario TEXT,
            fecha DATE,
            FOREIGN KEY(id_usuario) REFERENCES usuarios(id),
            FOREIGN KEY(id_sala) REFERENCES salas(id)
        );

        CREATE TABLE IF NOT EXISTS log (
            id INT AUTO_INCREMENT PRIMARY KEY,
            fecha DATETIME,
            descripcion TEXT
        );

        CREATE TABLE IF NOT EXISTS config (
            clave VARCHAR(100) PRIMARY KEY,
            valor TEXT
        );
    ");

    // Si no hay ningún administrador, crea uno por defecto
    $stmt = $db->query("SELECT COUNT(*) FROM usuarios WHERE rol = 'Administrador'");
    if ($stmt->fetchColumn() == 0) {
        $stmt = $db->prepare("INSERT INTO usuarios (id, nombre, apellidos, dni, email, clave, foto, rol)
                            VALUES (:i, :n, :a, :d, :e, :c, NULL, 'Administrador')");
        $stmt->execute([
            ':i' => '0',
            ':n' => 'admin',
            ':a' => 'Principal',
            ':d' => '00000000T',
            ':e' => 'admin@void.ugr.es',
            ':c' => password_hash('admin', PASSWORD_DEFAULT)
        ]);
    }


} catch (PDOException $e) {
    die("Error al conectar o crear la base de datos: " . $e->getMessage());
}
?>
