<?php
// Muestra errores (para desarrollo)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Incluye conexión a la BBDD y funciones auxiliares
require_once "includes/db.php";
require_once "includes/functions.php";


// LOGOUT: cierra la sesión si se pasa ?logout=1 por URL
if (isset($_GET['logout'])) {
    session_unset();      // Limpia variables de sesión
    session_destroy();    // Destruye la sesión
    header("Location: index.php?page=home");
    exit;
}

// Carga cabecera (HTML inicial y menú)
require_once "includes/header.php";
// Contenedor principal
echo '<div class="main-wrapper">';

// Página actual solicitada por el usuario
$page = $_GET['page'] ?? 'home';

// Define las páginas accesibles según tipo de usuario
$pages_public = ['home', 'login', 'register', 'salas', 'reservas'];
$pages_registered = ['profile', 'reservas', 'mis_reservas'];
$pages_admin = ['usuarios_admin', 'log', 'bbdd', 'salas_admin', 'configuracion'];


$filepath = null; // Ruta del archivo PHP a incluir

// Enrutado
switch ($page) {
    case 'home':
    case 'salas':
    case 'reservas':
        $filepath = "pages/$page.php";
        break;

    case 'login':
    case 'register':
        // Si ya hay sesión iniciada, no se permite ver login/register
        if (is_logged_in()) {
            header("Location: index.php?page=home");
            exit;
        }
        $filepath = "pages/$page.php";
        break;

    case 'profile':
    case 'mis_reservas':
        // Solo para usuarios logueados
        if (!is_logged_in()) {
            $filepath = "pages/error.php";
        } else {
            $filepath = "pages/$page.php";
        }
        break;

    case 'usuarios_admin':
    case 'log':
    case 'bbdd':
    case 'configuracion':
    case 'salas_admin':
        // Solo para administradores
        if (!is_admin()) {
            $filepath = "pages/error.php";
        } else {
            $filepath = "pages/$page.php";
        }
        break;

    default:
    // Página no permitida
        $filepath = "pages/error.php";
        break;
}

// Incluye la página solicitada si existe, si no, muestra error
if (file_exists($filepath)) {
    include $filepath;
} else {
    include "pages/error.php";
}

// Cierre del contenedor principal
echo '</div>';

// Carga pie de página
require_once "includes/footer.php";