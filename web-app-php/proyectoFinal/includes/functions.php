<?php
// Inicia la sesión si no está ya iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Incluye el archivo de conexión con la base de datos
require_once __DIR__ . '/db.php';


/**
 * Devuelve el usuario actualmente logueado o null si no hay sesión.
 */
function current_user() {
    return $_SESSION['usuario'] ?? null;
}

/**
 * Comprueba si hay un usuario identificado en la sesión.
 */
function is_logged_in() {
    return isset($_SESSION['usuario']);
}

/**
 * Comprueba si el usuario actual tiene rol de administrador.
 */
function is_admin() {
    return isset($_SESSION['usuario']) && $_SESSION['usuario']['rol'] === 'Administrador';
}


/**
 * Obtiene un valor de configuración del sistema desde la tabla `config`.
 * Útil para acceder, por ejemplo, al nombre del centro o al horario de apertura.
 */
function get_config($clave) {
    global $db;
    $stmt = $db->prepare("SELECT valor FROM config WHERE clave = :clave");
    $stmt->execute([':clave' => $clave]);
    return $stmt->fetchColumn() ?: '';
}

/**
 * Devuelve el número total de salas registradas en el sistema.
 * Se usa para mostrarlo en la zona lateral secundaria.
 */
function get_total_aulas() {
    global $db;
    $stmt = $db->query("SELECT COUNT(*) FROM salas");
    return $stmt->fetchColumn();
}

/**
 * Devuelve la capacidad total (suma de todos los puestos disponibles en todas las salas).
 * Se muestra también en la zona lateral.
 */
function get_capacidad_total() {
    global $db;
    $stmt = $db->query("SELECT SUM(capacidad) FROM salas");
    return $stmt->fetchColumn();
}

/**
 * Devuelve cuántas salas están reservadas hoy (fecha actual).
 * Solo cuenta cada sala una vez, aunque tenga varias reservas.
 */
function get_reservadas_hoy() {
    global $db;
    $hoy = date('Y-m-d');
    $stmt = $db->prepare("SELECT COUNT(DISTINCT id_sala) FROM reservas WHERE fecha = :fecha");
    $stmt->execute([':fecha' => $hoy]);
    return $stmt->fetchColumn();
}


/**
 * Valida que un DNI español tiene el formato correcto y su letra asociada.
 * Usado durante el registro de usuarios.
 */
function validar_dni($dni) {
    $letra = substr($dni, -1);
    $numeros = substr($dni, 0, 8);
    if (!is_numeric($numeros) || strlen($letra) !== 1) return false;

    $letras = 'TRWAGMYFPDXBNJZSQVHLCKE';
    $letra_correcta = $letras[intval($numeros) % 23];

    return strtoupper($letra) === $letra_correcta;
}



/**
 * Elimina todos los archivos dentro de un directorio específico.
 * Se puede usar, por ejemplo, para limpiar imágenes temporales o backups antiguos.
 */
function limpiar_directorio($ruta) {
    foreach (glob($ruta . '/*') as $archivo) {
        if (is_file($archivo)) {
            unlink($archivo);
        }
    }
}



/**
 * Convierte una fecha en formato ISO (YYYY-MM-DD) a un formato más legible en español.
 * Por ejemplo: "lunes, 03 de junio de 2025"
 */
function fecha_espanol($fecha_iso) {
    $dias = ['domingo', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'];
    $meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio',
              'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];

    $timestamp = strtotime($fecha_iso);
    $dia_semana = $dias[date('w', $timestamp)];
    $dia = date('d', $timestamp);
    $mes = $meses[date('n', $timestamp) - 1];
    $anio = date('Y', $timestamp);

    return "$dia_semana, $dia de $mes de $anio";
}


/**
 * Guarda o actualiza un valor de configuración en la tabla `config`.
 * REPLACE INTO hace un INSERT si no existe o un UPDATE si ya existe esa clave.
 */
function set_config($clave, $valor) {
    global $db;
    $stmt = $db->prepare("REPLACE INTO config (clave, valor) VALUES (:clave, :valor)");
    $stmt->execute([':clave' => $clave, ':valor' => $valor]);
}
