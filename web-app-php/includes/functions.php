<?php
function is_admin() {
    return isset($_SESSION['user']) && $_SESSION['user']['rol'] === 'Administrador';
}

function is_logged_in() {
    return isset($_SESSION['user']);
}

function current_user() {
    return $_SESSION['user'] ?? null;
}