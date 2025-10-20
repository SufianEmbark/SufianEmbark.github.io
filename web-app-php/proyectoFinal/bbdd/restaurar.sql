-- ELIMINAR TABLAS EXISTENTES
DROP TABLE IF EXISTS reservas;
DROP TABLE IF EXISTS comentarios;
DROP TABLE IF EXISTS fotos_sala;
DROP TABLE IF EXISTS log;
DROP TABLE IF EXISTS config;
DROP TABLE IF EXISTS salas;
DROP TABLE IF EXISTS usuarios;

-- CREAR TABLAS
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    apellidos VARCHAR(255) NOT NULL,
    dni VARCHAR(20) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    clave VARCHAR(255) NOT NULL,
    foto VARCHAR(255),
    rol ENUM('Administrador', 'Usuario', 'Cliente') NOT NULL
);

CREATE TABLE IF NOT EXISTS salas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    ubicacion VARCHAR(255) NOT NULL,
    capacidad INT NOT NULL,
    descripcion TEXT,
    reservable TINYINT(1) NOT NULL
);

CREATE TABLE IF NOT EXISTS fotos_sala (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_sala INT NOT NULL,
    archivo VARCHAR(255) NOT NULL,
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
    clave VARCHAR(255) PRIMARY KEY,
    valor TEXT
);

-- INSERTAR DATOS DE PRUEBA
INSERT INTO usuarios (nombre, apellidos, dni, email, clave, rol, foto) VALUES ('admin', 'Admin', '00000000T', 'admin@void.ugr.es', 'admin', 'Administrador', 'foto_user_admin.png');
INSERT INTO usuarios (nombre, apellidos, dni, email, clave, rol, foto) VALUES ('Javier', 'Martínez', '11111111R', 'javier@void.ugr.es', 'javier', 'Administrador', 'foto_user_javier.png');
INSERT INTO usuarios (nombre, apellidos, dni, email, clave, rol, foto) VALUES ('Pedro', 'López', '22222222W', 'pedro@void.ugr.es', 'pedro', 'Usuario', 'foto_user_pedro.png');
INSERT INTO usuarios (nombre, apellidos, dni, email, clave, rol, foto) VALUES ('Anita', 'Gómez', '33333333A', 'anita@void.ugr.es', 'anita', 'Usuario', 'foto_user_anita.png');
INSERT INTO usuarios (nombre, apellidos, dni, email, clave, rol, foto) VALUES ('Maria', 'Ruiz', '44444444G', 'maria@void.ugr.es', 'maria', 'Cliente', 'foto_user_maria.png');

INSERT INTO salas (nombre, ubicacion, capacidad, descripcion, reservable) VALUES ('Aula 0.1', 'Planta baja', 60, 'Aula con buena iluminación', 1);
INSERT INTO salas (nombre, ubicacion, capacidad, descripcion, reservable) VALUES ('Aula 0.2', 'Planta baja', 70, 'Aula con proyector', 1);
INSERT INTO salas (nombre, ubicacion, capacidad, descripcion, reservable) VALUES ('Aula 0.3', 'Primera planta', 80, 'Espaciosa y luminosa', 1);
INSERT INTO salas (nombre, ubicacion, capacidad, descripcion, reservable) VALUES ('Lab electrónica', 'Sótano', 18, 'Laboratorio de electrónica', 1);
INSERT INTO salas (nombre, ubicacion, capacidad, descripcion, reservable) VALUES ('Polivalente', 'Planta baja', 30, 'Sala multiusos', 1);
INSERT INTO salas (nombre, ubicacion, capacidad, descripcion, reservable) VALUES ('Salón de actos', 'Planta baja', 150, 'Gran salón para eventos', 0);
INSERT INTO salas (nombre, ubicacion, capacidad, descripcion, reservable) VALUES ('Salón de grados', 'Segunda planta', 80, 'Sala para presentaciones', 0);
INSERT INTO salas (nombre, ubicacion, capacidad, descripcion, reservable) VALUES ('Lab 2.1', 'Segunda planta', 38, 'Laboratorio informático', 1);

INSERT INTO fotos_sala (id_sala, archivo) VALUES (1, 'assets/images/foto1_1.png');
INSERT INTO fotos_sala (id_sala, archivo) VALUES (4, 'assets/images/foto4_1.png');
INSERT INTO fotos_sala (id_sala, archivo) VALUES (5, 'assets/images/foto5_1.png');
INSERT INTO fotos_sala (id_sala, archivo) VALUES (5, 'assets/images/foto5_2.png');
INSERT INTO fotos_sala (id_sala, archivo) VALUES (6, 'assets/images/foto6_1.png');
INSERT INTO fotos_sala (id_sala, archivo) VALUES (6, 'assets/images/foto6_2.png');
INSERT INTO fotos_sala (id_sala, archivo) VALUES (6, 'assets/images/foto6_3.png');
INSERT INTO fotos_sala (id_sala, archivo) VALUES (7, 'assets/images/foto7_1.png');
INSERT INTO fotos_sala (id_sala, archivo) VALUES (7, 'assets/images/foto7_2.png');
INSERT INTO fotos_sala (id_sala, archivo) VALUES (7, 'assets/images/foto7_3.png');
INSERT INTO fotos_sala (id_sala, archivo) VALUES (8, 'assets/images/foto8_1.png');
INSERT INTO fotos_sala (id_sala, archivo) VALUES (8, 'assets/images/foto8_2.png');
INSERT INTO fotos_sala (id_sala, archivo) VALUES (8, 'assets/images/foto8_3.png');

INSERT INTO reservas (id_usuario, id_sala, motivo, fecha, hora_inicio, hora_fin) VALUES (3, 7, 'Reserva 1', '2025-01-04', '13:00:00', '14:00:00');
INSERT INTO reservas (id_usuario, id_sala, motivo, fecha, hora_inicio, hora_fin) VALUES (3, 5, 'Reserva 2', '2025-01-13', '16:00:00', '17:00:00');
INSERT INTO reservas (id_usuario, id_sala, motivo, fecha, hora_inicio, hora_fin) VALUES (2, 3, 'Reserva 3', '2025-01-13', '13:00:00', '14:00:00');
INSERT INTO reservas (id_usuario, id_sala, motivo, fecha, hora_inicio, hora_fin) VALUES (1, 1, 'Reserva 4', '2025-01-22', '08:00:00', '09:00:00');
INSERT INTO reservas (id_usuario, id_sala, motivo, fecha, hora_inicio, hora_fin) VALUES (2, 2, 'Reserva 5', '2025-02-10', '15:00:00', '16:00:00');
INSERT INTO reservas (id_usuario, id_sala, motivo, fecha, hora_inicio, hora_fin) VALUES (2, 1, 'Reserva 6', '2025-03-07', '13:00:00', '14:00:00');
INSERT INTO reservas (id_usuario, id_sala, motivo, fecha, hora_inicio, hora_fin) VALUES (1, 8, 'Reserva 7', '2025-03-26', '12:00:00', '13:00:00');
INSERT INTO reservas (id_usuario, id_sala, motivo, fecha, hora_inicio, hora_fin) VALUES (3, 6, 'Reserva 8', '2025-01-13', '15:00:00', '16:00:00');
INSERT INTO reservas (id_usuario, id_sala, motivo, fecha, hora_inicio, hora_fin) VALUES (1, 1, 'Reserva 9', '2025-04-23', '16:00:00', '17:00:00');
INSERT INTO reservas (id_usuario, id_sala, motivo, fecha, hora_inicio, hora_fin) VALUES (3, 3, 'Reserva 10', '2025-04-28', '11:00:00', '12:00:00');
INSERT INTO reservas (id_usuario, id_sala, motivo, fecha, hora_inicio, hora_fin) VALUES (1, 2, 'Reserva 11', '2025-04-29', '11:00:00', '12:00:00');
INSERT INTO reservas (id_usuario, id_sala, motivo, fecha, hora_inicio, hora_fin) VALUES (2, 7, 'Reserva 12', '2025-01-13', '14:00:00', '15:00:00');
INSERT INTO reservas (id_usuario, id_sala, motivo, fecha, hora_inicio, hora_fin) VALUES (2, 6, 'Reserva 13', '2025-05-22', '16:00:00', '17:00:00');
INSERT INTO reservas (id_usuario, id_sala, motivo, fecha, hora_inicio, hora_fin) VALUES (1, 5, 'Reserva 14', '2025-06-05', '14:00:00', '15:00:00');
INSERT INTO reservas (id_usuario, id_sala, motivo, fecha, hora_inicio, hora_fin) VALUES (4, 1, 'Reserva 15', '2025-01-13', '12:00:00', '13:00:00');
