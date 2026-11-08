-- Base de datos del Ejercicio 1: Lista de Tareas Interactiva
-- Importar desde phpMyAdmin (pestaña "Importar")

CREATE DATABASE IF NOT EXISTS pwatarea4 CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE pwatarea4;

CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(60) NOT NULL,
    usuario VARCHAR(30) NOT NULL UNIQUE,
    clave VARCHAR(255) NOT NULL,
    rol ENUM('admin','usuario') NOT NULL DEFAULT 'usuario',
    creado TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS tareas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(150) NOT NULL,
    descripcion TEXT,
    prioridad ENUM('baja','media','alta') NOT NULL DEFAULT 'media',
    completada TINYINT(1) NOT NULL DEFAULT 0,
    usuario_id INT NOT NULL,
    creado TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Usuarios de prueba (la clave de ambos es: 123456)
INSERT INTO usuarios (nombre, usuario, clave, rol) VALUES
('Administrador', 'admin', '$2y$10$j/ALhz9Hqm5a7J3zLHCyFe5S01zB8BEIfYsW7vP87wYx.bondSa0W', 'admin'),
('Usuario de prueba', 'juan', '$2y$10$j/ALhz9Hqm5a7J3zLHCyFe5S01zB8BEIfYsW7vP87wYx.bondSa0W', 'usuario');
