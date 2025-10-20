<?php
// Incluye funciones comunes y obtiene el usuario actual
require_once __DIR__ . '/../includes/functions.php';
$usuario = current_user();
$errores = [];

// --- PROCESAR CREACIÓN DE RESERVA ---
if (is_logged_in() && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_reserva'])) {
    $fecha = $_POST['fecha'] ?? '';
    $id_sala = intval($_POST['id_sala'] ?? 0);
    $hora_inicio = $_POST['hora_inicio'] ?? '';
    $hora_fin = $_POST['hora_fin'] ?? '';
    $motivo = trim($_POST['motivo'] ?? '');

    // Validación básica
    if ($fecha && $id_sala && $hora_inicio && $hora_fin && $motivo) {
        // Comprueba si ya hay una reserva en ese tramo horario
        $stmt = $db->prepare("SELECT COUNT(*) FROM reservas 
                              WHERE id_sala = :sala AND fecha = :fecha 
                              AND hora_inicio < :fin AND hora_fin > :inicio");
        $stmt->execute([
            ':sala' => $id_sala,
            ':fecha' => $fecha,
            ':inicio' => $hora_fin,
            ':fin' => $hora_inicio
        ]);

        // Si no hay conflicto, crea la reserva
        if ($stmt->fetchColumn() == 0) {
            $stmt = $db->prepare("INSERT INTO reservas (id_usuario, id_sala, motivo, fecha, hora_inicio, hora_fin)
                                  VALUES (:usuario, :sala, :motivo, :fecha, :inicio, :fin)");
            $stmt->execute([
                ':usuario' => $usuario['id'],
                ':sala' => $id_sala,
                ':motivo' => $motivo,
                ':fecha' => $fecha,
                ':inicio' => $hora_inicio,
                ':fin' => $hora_fin
            ]);

            // Registro en log
            $id_reserva = $db->lastInsertId();

            $log = $db->prepare("INSERT INTO log (descripcion, fecha) VALUES (:desc, NOW())");
            $log->execute([':desc' => "Reserva creada: ID $id_reserva en Sala $id_sala el $fecha ($hora_inicio-$hora_fin) por usuario " . $usuario['email']]);


            // Redirección tras crear la reserva
            header("Location: index.php?page=reservas&fecha=$fecha");
            exit;
        } else {
            $errores[] = "Esa franja horaria ya está reservada.";
        }
    } else {
        $errores[] = "Es obligatorio describir el motivo de la reserva.";
    }
}


// --- DATOS PARA MOSTRAR EL CALENDARIO ---
$fecha = $_GET['fecha'] ?? date('Y-m-d'); // Fecha seleccionada o actual
$mostrar_fecha = date('Y-m-d', strtotime($fecha)); // Asegura formato
$dia_mostrar = fecha_espanol($mostrar_fecha); // Formato español

// Obtiene las salas del centro
$salas = $db->query("SELECT * FROM salas ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);

// Consulta las reservas para la fecha seleccionada
$stmt = $db->prepare("SELECT r.*, u.nombre as nombre_usuario, s.nombre as nombre_sala
                      FROM reservas r
                      JOIN usuarios u ON r.id_usuario = u.id
                      JOIN salas s ON r.id_sala = s.id
                      WHERE fecha = :fecha");
$stmt->execute([':fecha' => $mostrar_fecha]);
$reservas_dia = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Organiza reservas por sala y hora para la tabla
$reservas_por_sala_hora = [];
foreach ($reservas_dia as $res) {
    $reserva_hora_inicio = (int)substr($res['hora_inicio'], 0, 2);
    $reserva_hora_fin = (int)substr($res['hora_fin'], 0, 2);
    for ($h = $reserva_hora_inicio; $h < $reserva_hora_fin; $h++) {
        $hora_key = sprintf("%02d:00", $h);
        $reservas_por_sala_hora[$res['id_sala']][$hora_key] = $res;
    }
}

// Horas configuradas del centro
$hora_inicio = get_config('hora_apertura') ?: '08:00';
$hora_fin = get_config('hora_cierre') ?: '20:00';

$h_inicio = (int)substr($hora_inicio, 0, 2);
$h_fin = (int)substr($hora_fin, 0, 2);

// Genera las horas a mostrar en el calendario
$horas = [];
for ($h = $h_inicio; $h < $h_fin; $h++) {
    $horas[] = sprintf("%02d:00", $h);
}

?>

<!-- HTML: INTERFAZ DE RESERVAS -->
<div class="reservas-container">
    <h2 class ="reservas-titulo">Calendario de Reservas</h2>
    <p class = "DiaReservas"><strong>Día:</strong> <?= htmlspecialchars($dia_mostrar) ?></p>

    <!-- Muestra errores si hay -->
    <?php if (!empty($errores)): ?>
        <ul class="errores">
            <?php foreach ($errores as $e): ?>
                <li>❌ <?= htmlspecialchars($e) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <!-- Formulario de selección de fecha -->
    <form method="get" action="index.php" novalidate>
        <input type="hidden" name="page" value="reservas">
        <label>Seleccionar fecha:</label>
        <input type="date" name="fecha" value="<?= $mostrar_fecha ?>">
        <button class="button-ir-reservas" type="submit">Ir</button>
        <button type="button" onclick="location.href='index.php?page=reservas&fecha=<?= date('Y-m-d') ?>'">Hoy</button>
        <button type="button" onclick="location.href='index.php?page=reservas&fecha=<?= date('Y-m-d', strtotime($mostrar_fecha . ' -1 day')) ?>'">Día anterior</button>
        <button type="button" onclick="location.href='index.php?page=reservas&fecha=<?= date('Y-m-d', strtotime($mostrar_fecha . ' +1 day')) ?>'">Día siguiente</button>
    </form>

    <!-- Tabla de reservas -->
    <div class="tabla-reservas-c-wrapper">
        <table border="0" cellpadding="5" cellspacing="0" class="tabla-reservas-c">
            <thead>
                <tr>
                    <th>Sala / Hora</th>
                    <?php foreach ($horas as $hora): ?>
                        <th><?= $hora ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($salas as $sala): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($sala['nombre']) ?></strong></td>
                        <?php foreach ($horas as $hora):
                            $reserva = $reservas_por_sala_hora[$sala['id']][$hora] ?? null;
                            if ($reserva): ?>
                                <!-- Celda ocupada: color según si es del usuario actual -->
                            <td
                                class="reservada"
                                style="background-color: <?= $reserva['id_usuario'] == $usuario['id'] ? '#b2f2bb' : '#cce5ff' ?>;"
                                data-fulltext="<?= htmlspecialchars($reserva['motivo']) ?> (<?= htmlspecialchars($reserva['nombre_usuario']) ?>)"
                            >
                                <div class="contenido-reserva">
                                    <?= htmlspecialchars($reserva['motivo']) ?><br>
                                    (<?= htmlspecialchars($reserva['nombre_usuario']) ?>)
                                </div>
                            </td>
                            <?php elseif ($sala['reservable'] == 0): ?>
                                <!-- Celda gris si la sala no es reservable -->
                                <td style="background-color: #e0e0e0; text-align:center;">No reservable</td>
                            <?php else: ?>
                                <td style="background-color: #e2f7e1;">
                                    <?php if (is_logged_in()): ?>
                                        <form method="post" style="margin:0; font-size: small;" novalidate>
                                            <input type="hidden" name="crear_reserva" value="1">
                                            <input type="hidden" name="fecha" value="<?= $mostrar_fecha ?>">
                                            <input type="hidden" name="id_sala" value="<?= $sala['id'] ?>">
                                            <input type="hidden" name="hora_inicio" value="<?= $hora ?>">
                                            <input type="hidden" name="hora_fin" value="<?= date('H:i', strtotime($hora) + 3600) ?>">
                                            <input type="text" name="motivo" placeholder="Motivo" required style="width: 100%; font-size:10px;">
                                            <button type="submit" style="width: 100%; font-size:10px;">Reservar</button>
                                        </form>
                                    <?php else: ?>
                                        <div style="text-align:center; font-size:10px; color:#888;">
                                            Libre
                                        </div>
                                    <?php endif; ?>    
                                </td>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>