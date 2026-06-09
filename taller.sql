CREATE DATABASE IF NOT EXISTS tallermec CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE tallermec;

CREATE TABLE usuarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(32) NOT NULL,
    rol ENUM('admin','cliente','mecanico','electricista') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE vehiculos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_cliente INT NOT NULL,
    marca VARCHAR(100) NOT NULL,
    modelo VARCHAR(100) NOT NULL,
    anio YEAR NOT NULL,
    patente VARCHAR(20) NOT NULL UNIQUE,
    color VARCHAR(50) NOT NULL,
    FOREIGN KEY (id_cliente) REFERENCES usuarios(id)
);

CREATE TABLE ordenes_trabajo (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_vehiculo INT NOT NULL,
    id_cliente INT NOT NULL,
    id_trabajador INT NOT NULL,
    tipo ENUM('mecanica','electricidad') NOT NULL,
    descripcion TEXT NOT NULL,
    estado ENUM('pendiente','en_proceso','completado','cancelado') NOT NULL DEFAULT 'pendiente',
    fecha_ingreso DATE NOT NULL,
    fecha_estimada DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_vehiculo) REFERENCES vehiculos(id),
    FOREIGN KEY (id_cliente) REFERENCES usuarios(id),
    FOREIGN KEY (id_trabajador) REFERENCES usuarios(id)
);

INSERT INTO usuarios (nombre, apellido, email, password, rol) VALUES
('Admin', 'Sistema', 'admin@taller.com', MD5('admin123'), 'admin'),
('Roberto', 'Silva', 'roberto@taller.com', MD5('mec123'), 'mecanico'),
('Rodirgo', 'Perez', 'rperez@taller.com', MD5('elec123'), 'electricista'),
('Alexis', 'Quiroga', 'aquiroga@cliente.com', MD5('cli123'), 'cliente'),
('Maria', 'Lopez', 'maria@cliente.com', MD5('cli456'), 'cliente');

INSERT INTO vehiculos (id_cliente, marca, modelo, anio, patente, color) VALUES
(4, 'Toyota', 'Corolla', 2019, 'ABC123', 'Blanco'),
(4, 'Ford', 'Ranger', 2021, 'XYZ789', 'Gris'),
(5, 'Volkswagen', 'Golf', 2018, 'DEF456', 'Rojo'),
(5, 'Chevrolet', 'Onix', 2022, 'GHI321', 'Negro');

INSERT INTO ordenes_trabajo (id_vehiculo, id_cliente, id_trabajador, tipo, descripcion, estado, fecha_ingreso, fecha_estimada) VALUES
(1, 4, 2, 'mecanica', 'Cambio de aceite y filtros. Revision de frenos delanteros.', 'pendiente', '2026-05-28', '2026-06-03'),
(2, 4, 2, 'mecanica', 'Ajuste de suspension y alineacion. Balanceo de ruedas.', 'en_proceso', '2026-05-25', '2026-06-02'),
(3, 5, 3, 'electricidad', 'Falla en sistema de arranque. Revision de bateria y alternador.', 'pendiente', '2026-05-30', '2026-06-04'),
(4, 5, 3, 'electricidad', 'Instalacion de alarma y luces LED adicionales.', 'completado', '2026-05-20', '2026-05-27');
