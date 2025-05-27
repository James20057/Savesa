-- CREACIÓN DE BASE DE DATOS
CREATE DATABASE asistencia_virtual;
\c asistencia_virtual

-- ROLES
CREATE TABLE roles (
    id_rol SERIAL PRIMARY KEY,
    nombre VARCHAR(30) NOT NULL
);

-- USUARIOS
CREATE TABLE usuarios (
    id_usuario SERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    id_card VARCHAR(30) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    id_rol INT NOT NULL REFERENCES roles(id_rol),
    telefono VARCHAR(20)
);

-- CLASES
CREATE TABLE clases (
    id_clase SERIAL PRIMARY KEY,
    nombre VARCHAR(80) NOT NULL,
    horario TIMESTAMP NOT NULL,
    id_profesor INT NOT NULL REFERENCES usuarios(id_usuario),
    aula VARCHAR(30)
);

-- MATERIALES
CREATE TABLE materiales (
    id_material SERIAL PRIMARY KEY,
    titulo VARCHAR(100) NOT NULL,
    descripcion VARCHAR(255),
    tipo VARCHAR(20) NOT NULL, -- Usamos texto, no ENUM aquí
    url_archivo VARCHAR(255),
    id_profesor INT NOT NULL REFERENCES usuarios(id_usuario),
    fecha_subida DATE NOT NULL
);

-- ASISTENCIA
CREATE TABLE asistencia (
    id_asistencia SERIAL PRIMARY KEY,
    id_usuario INT NOT NULL REFERENCES usuarios(id_usuario),
    id_clase INT NOT NULL REFERENCES clases(id_clase),
    fecha DATE NOT NULL,
    presente BOOLEAN NOT NULL DEFAULT FALSE,
    justificada BOOLEAN NOT NULL DEFAULT FALSE
);

-- JUSTIFICACIONES
CREATE TABLE justificaciones (
    id_justificacion SERIAL PRIMARY KEY,
    id_asistencia INT NOT NULL REFERENCES asistencia(id_asistencia),
    motivo VARCHAR(100),
    archivo_url VARCHAR(255),
    estado VARCHAR(20) DEFAULT 'Pendiente'
);


-- ===============================
-- INSERCIÓN DE DATOS DE EJEMPLO
-- ===============================

-- ROLES
INSERT INTO roles (id_rol, nombre) VALUES 
(1, 'Estudiante'),
(2, 'Profesor'),
(3, 'Administrador');

-- USUARIOS
INSERT INTO usuarios (nombre, email, id_card, password_hash, id_rol, telefono) VALUES
('Alex Morgan','alex.morgan@email.com','10001','alex123',3,'3005001000'),
('Sara Carter','sara.carter@email.com','10002','sara123',2,'3005002000'),
('Camilo Ríos','camilo.rios@email.com','10003','camilo123',1,'3207003001'),
('Ana López','ana.lopez@email.com','10004','ana123',1,'3128001003'),
('Julián Torres','julian.torres@email.com','10005','julian123',1,'3229012234'),
('Lucía Gómez','lucia.gomez@email.com','10006','lucia123',1,'3198882341'),
('Paula Ruiz','paula.ruiz@email.com','10007','paula123',2,'3184563322'),
('David Smith','david.smith@email.com','10008','david123',2,'3162347765'),
('Laura Pérez','laura.perez@email.com','10009','laura123',1,'3173434111'),
('Martín Fuentes','martin.fuentes@email.com','10010','martin123',1,'3159998765');


-- CLASES
INSERT INTO clases (nombre, horario, id_profesor, aula) VALUES
('Programación I - Grupo 1','2024-05-27 08:00:00',2,'Aula 204'),
('Programación I - Grupo 2','2024-05-27 10:00:00',2,'Aula 205'),
('Estructuras de Datos','2024-05-27 12:00:00',8,'Sala de Computo 2'),
('Bases de Datos - Grupo 1','2024-05-28 09:00:00',7,'Aula 102'),
('Bases de Datos - Grupo 2','2024-05-28 11:00:00',7,'Aula 102'),
('Sistemas Operativos','2024-05-29 08:00:00',2,'Lab Sistemas'),
('Ingeniería de Software','2024-05-29 14:00:00',8,'Aula 305'),
('Matemáticas Discretas','2024-05-30 07:30:00',2,'Aula 201'),
('Redes de Computadores - Grupo 1','2024-05-30 10:00:00',8,'Lab Redes'),
('Redes de Computadores - Grupo 2','2024-05-30 12:00:00',8,'Lab Redes');


INSERT INTO materiales (titulo, descripcion, tipo, url_archivo, id_profesor, fecha_subida) VALUES
('Guía Programación I','Ejercicios de arreglos y ciclos','Documento','/files/programacion1_guia.pdf',2,'2024-05-10'),
('Taller Estructuras de Datos','Práctica sobre listas y pilas','Documento','/files/estructuras_taller.pdf',8,'2024-05-12'),
('Tutorial SQL Básico','Video introductorio sobre consultas SQL','Video','/files/sql_basico.mp4',7,'2024-05-14'),
('Enlace a Oracle Academy','Curso gratuito de bases de datos','Link','https://academy.oracle.com/es',7,'2024-05-15'),
('Examen Sistemas Operativos','Segundo parcial 2024-1','Documento','/files/sisop_examen2.pdf',2,'2024-05-20'),
('Resumen Redes','Material de apoyo sobre protocolos TCP/IP','Documento','/files/redes_resumen.pdf',8,'2024-05-16'),
('Enlace a GitHub UDENAR','Repositorio oficial de prácticas','Link','https://github.com/udenar-ing-sistemas','2','2024-05-17');


-- ASISTENCIA
INSERT INTO asistencia (id_usuario, id_clase, fecha, presente, justificada) VALUES
(3,1,'2024-05-27',TRUE,FALSE),
(4,1,'2024-05-27',FALSE,TRUE),
(5,1,'2024-05-27',TRUE,FALSE),
(6,1,'2024-05-27',TRUE,FALSE),
(9,2,'2024-05-27',FALSE,FALSE),
(10,2,'2024-05-27',TRUE,FALSE),
(3,2,'2024-05-27',TRUE,FALSE),
(4,2,'2024-05-27',TRUE,FALSE),
(5,2,'2024-05-27',TRUE,FALSE),
(6,2,'2024-05-27',FALSE,TRUE);


-- JUSTIFICACIONES
INSERT INTO justificaciones (id_asistencia, motivo, archivo_url, estado) VALUES
(2,'Enfermedad','/docs/justificacion_ana.pdf','Pendiente'),
(10,'Viaje familiar','/docs/viaje_lucia.pdf','Aprobada');




