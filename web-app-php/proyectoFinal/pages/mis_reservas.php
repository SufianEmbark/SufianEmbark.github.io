<?php
// Solo usuarios autenticados pueden acceder
if (!is_logged_in()) {
    header("Location: index.php?page=login");
    exit;
}

// Datos del usuario actual y si es administrador
$usuario = current_user();
$is_admin = is_admin();

// --- CANCELACIÓN DE RESERVAS ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancelar']) && isset($_POST['id_reserva'])) {
    $id_reserva = intval($_POST['id_reserva']);

    // Busca la reserva por ID
    $stmt = $db->prepare("SELECT * FROM reservas WHERE id = :id");
    $stmt->execute([':id' => $id_reserva]);
    $reserva = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verifica que la reserva exista y pertenezca al usuario o sea admin
    if ($reserva && ($reserva['id_usuario'] == $usuario['id'] || $is_admin)) {
        // Borra la reserva
        $stmt = $db->prepare("DELETE FROM reservas WHERE id = :id");
        $stmt->execute([':id' => $id_reserva]);

        // Log de cancelación
        $log = $db->prepare("INSERT INTO log (descripcion, fecha) VALUES (:desc, NOW())");
        $log->execute([':desc' => "Reserva cancelada: ID $id_reserva por usuario " . $usuario['email']]);

        // Redirige para recargar la página
        header("Location: index.php?page=mis_reservas");
        exit;
    }
}

// --- LIMPIAR FILTROS ---
if (isset($_GET['limpiar'])) {
    // Borra las cookies de filtro
    setcookie("filtro_motivo", "", time() - 3600);
    setcookie("filtro_fecha_inicio", "", time() - 3600);
    setcookie("filtro_fecha_fin", "", time() - 3600);
    setcookie("filtro_sala", "", time() - 3600);
    setcookie("filtro_usuario", "", time() - 3600);
    header("Location: index.php?page=mis_reservas");
    exit;
}

// --- GESTIÓN DE FILTROS ---
// Función para obtener el valor del filtro desde GET o cookies
function get_filtro($key) {
    if (isset($_GET[$key])) {
        setcookie("filtro_$key", $_GET[$key], time() + 3600);
        return $_GET[$key];
    } elseif (isset($_COOKIE["filtro_$key"])) {
        return $_COOKIE["filtro_$key"];
    }
    return '';
}

// Filtros disponibles
$filtro_motivo = get_filtro('motivo');
$filtro_fecha_inicio = get_filtro('fecha_inicio');
$filtro_fecha_fin = get_filtro('fecha_fin');
$filtro_sala = get_filtro('sala');
$filtro_usuario = $is_admin ? get_filtro('usuario') : $usuario['id'];

// Construcción de la cláusula WHERE
$filtros = [];
$params = [];

// Motivo
if (!empty($filtro_motivo)) {
    $filtros[] = "r.motivo LIKE :motivo";
    $params[':motivo'] = "%$filtro_motivo%";
}

// Fecha inicial
if (!empty($filtro_fecha_inicio)) {
    $filtros[] = "r.fecha >= :fecha_inicio";
    $params[':fecha_inicio'] = $filtro_fecha_inicio;
}

// Fecha final
if (!empty($filtro_fecha_fin)) {
    $filtros[] = "r.fecha <= :fecha_fin";
    $params[':fecha_fin'] = $filtro_fecha_fin;
}

// Sala
if (!empty($filtro_sala)) {
    $filtros[] = "s.nombre = :sala";
    $params[':sala'] = $filtro_sala;
}

// Usuario (solo para admin)
if (!empty($filtro_usuario)) {
    $filtros[] = "r.id_usuario = :usuario";
    $params[':usuario'] = $filtro_usuario;
}

$where_sql = $filtros ? "WHERE " . implode(" AND ", $filtros) : "";

// Orden de resultados
$orden = $_GET['orden'] ?? 'fecha';
switch ($orden) {
    case 'sala':
        $orden_sql = 's.nombre ASC, r.fecha DESC, r.hora_inicio ASC';
        break;
    case 'fecha':
    default:
        $orden_sql = 'r.fecha DESC, r.hora_inicio ASC';
        break;
}


// --- CONSULTA PRINCIPAL DE RESERVAS ---
$stmt = $db->prepare("SELECT r.*, s.nombre AS nombre_sala, s.ubicacion, u.nombre AS nombre_usuario
                      FROM reservas r
                      JOIN salas s ON r.id_sala = s.id
                      JOIN usuarios u ON r.id_usuario = u.id
                      $where_sql
                      ORDER BY $orden_sql");
$stmt->execute($params);
$reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- TÍTULO DE LA SECCIÓN -->
<h2 class="seccion-titulo">Mis Reservas</h2>

<!-- FORMULARIO DE FILTROS -->
<div class="mis_reservas_form">
    <form method="get" action="index.php" novalidate>
        <input type="hidden" name="page" value="mis_reservas">

        <label for="motivo">Motivo:</label>
        <input type="text" name="motivo" value="<?= htmlspecialchars($filtro_motivo) ?>">

        <label for="fecha_inicio">Desde:</label>
        <input type="date" name="fecha_inicio" value="<?= htmlspecialchars($filtro_fecha_inicio) ?>">

        <label for="fecha_fin">Hasta:</label>
        <input type="date" name="fecha_fin" value="<?= htmlspecialchars($filtro_fecha_fin) ?>">

        <label for="sala">Sala:</label>
        <select name="sala">
            <option value="">-- Todas --</option>
            <?php
            $salas = $db->query("SELECT DISTINCT nombre FROM salas ORDER BY nombre")->fetchAll(PDO::FETCH_COLUMN);
            foreach ($salas as $nombre_sala) {
                $sel = $filtro_sala === $nombre_sala ? 'selected' : '';
                echo "<option value=\"" . htmlspecialchars($nombre_sala) . "\" $sel>" . htmlspecialchars($nombre_sala) . "</option>";
            }
            ?>
        </select>

        <?php if ($is_admin): ?>
            <label for="usuario">Usuario:</label>
            <select name="usuario">
                <option value="">-- Todos --</option>
                <?php
                $usuarios = $db->query("SELECT id, nombre FROM usuarios ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);
                foreach ($usuarios as $u) {
                    $sel = $filtro_usuario == $u['id'] ? 'selected' : '';
                    echo "<option value=\"{$u['id']}\" $sel>" . htmlspecialchars($u['nombre']) . "</option>";
                }
                ?>
            </select>
        <?php endif; ?>

        <button class="mis_reservas_button" type="submit">Aplicar</button>
        <a href="index.php?page=mis_reservas&limpiar=1"><button type="button">Limpiar</button></a>
    
        <label for="orden">Ordenar por:</label>
        <select name="orden">
            <option value="fecha" <?= ($_GET['orden'] ?? '') === 'fecha' ? 'selected' : '' ?>>Fecha y hora</option>
            <option value="sala" <?= ($_GET['orden'] ?? '') === 'sala' ? 'selected' : '' ?>>Nombre de sala</option>
        </select>
    </form>
</div>

<!-- TABLA DE RESERVAS -->
<?php if (count($reservas) === 0): ?>
    <p>No hay reservas que coincidan con los filtros.</p>
<?php else: ?>
    <table border="0" cellpadding="5" cellspacing="0" class="tabla-reservas">
        <thead>
            <tr>
                <?php if ($is_admin): ?><th>Usuario</th><?php endif; ?>
                <th>Fecha</th>
                <th>Hora</th>
                <th>Sala</th>
                <th>Ubicación</th>
                <th>Motivo</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($reservas as $res): ?>
                <?php $es_pasada = strtotime($res['fecha']) < strtotime(date('Y-m-d')); ?>
                <tr>
                    <?php if ($is_admin): ?>
                        <td><?= htmlspecialchars($res['nombre_usuario']) ?></td>
                    <?php endif; ?>
                    <td<?= $es_pasada ? ' style="color: #888;"' : '' ?>><?= htmlspecialchars($res['fecha']) ?></td>
                    <td<?= $es_pasada ? ' style="color: #888;"' : '' ?>><?= htmlspecialchars($res['hora_inicio']) ?> - <?= htmlspecialchars($res['hora_fin']) ?></td>
                    <td<?= $es_pasada ? ' style="color: #888;"' : '' ?>><?= htmlspecialchars($res['nombre_sala']) ?></td>
                    <td<?= $es_pasada ? ' style="color: #888;"' : '' ?>><?= htmlspecialchars($res['ubicacion']) ?></td>
                    <td<?= $es_pasada ? ' style="color: #888;"' : '' ?>><?= htmlspecialchars($res['motivo']) ?></td>
                    <td>
                        <!-- Botón para cancelar o eliminar reserva -->
                        <form method="post" action="index.php?page=mis_reservas" onsubmit="return confirm('¿Eliminar esta reserva?');" novalidate>
                            <input type="hidden" name="cancelar" value="1">
                            <input type="hidden" name="id_reserva" value="<?= $res['id'] ?>">
                            <button type="submit"><?= $es_pasada ? '❌ Eliminar' : '❌ Cancelar' ?></button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
