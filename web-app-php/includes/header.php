<?php
$user = current_user();
?>
<header class="main-header">
    <div class="top-bar">
        <div class="logo">
            <img src="assets/images/logo.png" alt="Logotipo del centro">
        </div>
        <div class="title">
            <h1><?php echo get_config('nombre_centro'); ?></h1>
        </div>
        <div class="user-actions">
            <?php if (is_logged_in()): ?>
                <p><strong>👤 <?php echo htmlspecialchars($user['nombre']); ?></strong></p>
                <a href="index.php?logout=1">Logout</a>
            <?php else: ?>
                <a href="index.php?page=register">Registro</a> |
                <a href="index.php?page=login">Login</a>
            <?php endif; ?>
        </div>
    </div>

    <nav class="main-menu">
        <?php if (!is_logged_in()): ?>
            <!-- Menú Anónimo -->
            <a href="index.php?page=home">Inicio</a>
            <a href="index.php?page=salas">Salas</a>
            <a href="index.php?page=reservas">Reservas</a>
        <?php elseif (is_admin()): ?>
            <!-- Menú Administrador -->
            <a href="index.php?page=home">Inicio</a>
            <a href="index.php?page=salas">Salas</a>
            <a href="index.php?page=reservas">Reservas</a>
            <a href="index.php?page=salas_admin">Gestionar Salas</a>
            <a href="index.php?page=usuarios">Usuarios</a>
            <a href="index.php?page=log">Log</a>
            <a href="index.php?page=bbdd">BBDD</a>
        <?php else: ?>
            <!-- Menú Registrado -->
            <a href="index.php?page=home">Inicio</a>
            <a href="index.php?page=salas">Salas</a>
            <a href="index.php?page=reservas">Reservas</a>
            <a href="index.php?page=profile">Perfil</a>
        <?php endif; ?>
    </nav>

    <aside class="sidebar">
        <ul>
            <li>🏫 Aulas totales: <?php echo get_total_aulas(); ?></li>
            <li>👥 Capacidad total: <?php echo get_capacidad_total(); ?></li>
            <li>📅 Salas reservadas hoy: <?php echo get_reservadas_hoy(); ?></li>
        </ul>
    </aside>
</header>
