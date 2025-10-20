<?php
// Carga funciones necesarias
require_once __DIR__ . '/functions.php';
// Obtiene el usuario logueado (si lo hay)
$user = current_user();
// Carga configuración general del centro (con valores por defecto si no existe)
$nombre_centro = get_config('nombre_centro') ?: 'IES MIGUEL SERVET DE ZARAGOZA';
$hora_apertura = get_config('hora_apertura') ?: '08:00';
$hora_cierre = get_config('hora_cierre') ?: '20:00';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Aulas</title>
    <!-- Hoja de estilos -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="icon" href="favicon.ico" type="image/x-icon">

</head>
<body>
<header class="main-header">
    <div class="top-bar">

        <!-- Logotipo del centro -->
        <div class="logo">
            <a href="index.php?page=home">
                <?php if (file_exists(__DIR__ . '/../uploads/foto_logo/logo.png')): ?>
                    <img src="uploads/foto_logo/logo.png" alt="Logotipo del centro">
                <?php else: ?>
                    <img src="assets/images/logo.png" alt="Logotipo del centro">
                <?php endif; ?>
            </a>
        </div>

        <!-- Título con el nombre del centro -->
        <div class="title">
            <h1><?php echo htmlspecialchars($nombre_centro); ?></h1>
        </div>

        <!-- Área de acciones del usuario: login, perfil, logout, etc -->
        <div class="user-actions">
            <div class="bloque-login">
                <?php if (is_logged_in()): ?>
                    <!-- Usuario logueado: muestra perfil + logout -->
                    <div class="perfil-header">
                        <a href="index.php?page=profile" style="display: flex; align-items: center; text-decoration: none; color: inherit;">

                        <?php if (!empty($user['foto'])): ?>
                            <?php
                                $foto = $user['foto']; // Definimos la variable que se usa en las rutas

                                // Rutas del sistema para comprobar existencia
                                $ruta_uploads_fs = __DIR__ . '/../uploads/fotos_usuarios/' . $foto;
                                
                                // Rutas web
                                $ruta_uploads_web = 'uploads/fotos_usuarios/' . $foto;
                                $ruta_defecto_web = 'uploads/fotos_usuarios_por_defecto/' . $foto;

                                // Verificamos si la imagen personalizada existe
                                if (file_exists($ruta_uploads_fs)) {
                                    $ruta_final = $ruta_uploads_web;
                                } else {
                                    $ruta_final = $ruta_defecto_web;
                                }
                            ?>
                            <img src="<?php echo htmlspecialchars($ruta_final); ?>" alt="Foto perfil" class="foto-perfil">
                        <?php endif; ?>



                            <strong><?php echo htmlspecialchars($user['nombre']); ?></strong>
                        </a>
                        <span style="margin-left: 8px;">| <a href="index.php?logout=1">Logout</a></span>
                    </div>
                <?php else: ?>
                    <!-- Usuario anónimo: muestra enlaces de registro/login -->
                    <a href="index.php?page=register">Registro</a> |
                    <a href="index.php?page=login">Login</a>
                <?php endif; ?>
            </div>

            <!-- Muestra el horario del centro -->
            <div class="horario-apertura">
                🕒 <?= htmlspecialchars($hora_apertura) ?> - <?= htmlspecialchars($hora_cierre) ?>
            </div>
        </div>

    </div>

    <!-- Menú de navegación principal -->
    <nav class="main-menu">
        <a href="index.php?page=home">Inicio</a>
        <a href="index.php?page=salas">Salas</a>
        <a href="index.php?page=reservas">Estado reservas</a>

        <?php if (is_logged_in()): ?>
            <a href="index.php?page=mis_reservas">Mis reservas</a>
        <?php endif; ?>

        <?php if (is_admin()): ?>
            <a href="index.php?page=usuarios_admin">Usuarios</a>
            <a href="index.php?page=salas_admin">Salas (CRUD)</a>
            <a href="index.php?page=log">Log</a>
            <a href="index.php?page=bbdd">BBDD</a>
            <a href="index.php?page=configuracion">Configuracion</a>
        <?php endif; ?>
    </nav>

<!-- Usuario anónimo: muestra enlaces de registro/login -->
<aside class="sidebar">
    <ul>
        <li>
            <span class="icon">🏫</span>
            <span class="value"><?= get_total_aulas() ?></span>
            <span class="text"> Aulas totales</span>
        </li>
        <li>
            <span class="icon">👥</span>
            <span class="value"><?= get_capacidad_total() ?></span>
            <span class="text"> Capacidad total</span>
        </li>
        <li>
            <span class="icon">📅</span>
            <span class="value"><?= get_reservadas_hoy() ?></span>
            <span class="text"> Salas reservadas hoy</span>
        </li>
    </ul>
</aside>



</header>
