<?php
// Si se ha solicitado el cierre de sesión vía URL (?logout)
if (isset($_GET['logout'])) {
    // Destruye completamente la sesión del usuario (logout)
    session_destroy();
    // Redirige al usuario a la página principal tras cerrar sesión
    header("Location: index.php?page=home");
    exit;
}