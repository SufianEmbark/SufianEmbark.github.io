DROP TABLE IF EXISTS usuarios;
DROP TABLE IF EXISTS salas;
DROP TABLE IF EXISTS fotos_sala;
DROP TABLE IF EXISTS reservas;
DROP TABLE IF EXISTS comentarios;
DROP TABLE IF EXISTS log;
DROP TABLE IF EXISTS config;

CREATE TABLE IF NOT EXISTS usuarios (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nombre TEXT NOT NULL,
            apellidos TEXT NOT NULL,
            dni TEXT NOT NULL,
            email TEXT NOT NULL UNIQUE,
            clave TEXT NOT NULL,
            foto BLOB,
            rol TEXT NOT NULL CHECK(rol IN ('Administrador', 'Usuario', 'Cliente'))
        );

        CREATE TABLE IF NOT EXISTS salas (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nombre TEXT NOT NULL,
            ubicacion TEXT NOT NULL,
            capacidad INTEGER NOT NULL,
            descripcion TEXT,
            reservable INTEGER NOT NULL CHECK(reservable IN (0,1))
        );

        CREATE TABLE IF NOT EXISTS fotos_sala (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            id_sala INTEGER,
            foto BLOB,
            FOREIGN KEY(id_sala) REFERENCES salas(id) ON DELETE CASCADE
        );

        CREATE TABLE IF NOT EXISTS reservas (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            id_usuario INTEGER,
            id_sala INTEGER,
            motivo TEXT,
            fecha TEXT,
            hora_inicio TEXT,
            hora_fin TEXT,
            FOREIGN KEY(id_usuario) REFERENCES usuarios(id),
            FOREIGN KEY(id_sala) REFERENCES salas(id)
        );

        CREATE TABLE IF NOT EXISTS comentarios (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            id_usuario INTEGER,
            id_sala INTEGER,
            comentario TEXT,
            fecha TEXT,
            FOREIGN KEY(id_usuario) REFERENCES usuarios(id),
            FOREIGN KEY(id_sala) REFERENCES salas(id)
        );

        CREATE TABLE IF NOT EXISTS log (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            fecha TEXT,
            descripcion TEXT
        );

        CREATE TABLE IF NOT EXISTS config (
            clave TEXT PRIMARY KEY,
            valor TEXT
        );
