-- Crear BD
CREATE DATABASE IF NOT EXISTS terminal CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE terminal;

-- EMPRESAS
CREATE TABLE IF NOT EXISTS empresas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(120) NOT NULL,
  descripcion TEXT,
  logo_ruta VARCHAR(255),
  tipos_vehiculos VARCHAR(120),
  telefono VARCHAR(50),
  correo VARCHAR(120),
  direccion VARCHAR(200),
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- VEHICULOS
CREATE TABLE IF NOT EXISTS vehiculos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  empresa_id INT NOT NULL,
  tipo VARCHAR(40) NOT NULL,
  placa VARCHAR(15),
  capacidad INT,
  caracteristicas TEXT,
  FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE
);

-- RUTAS
CREATE TABLE IF NOT EXISTS rutas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  empresa_id INT NOT NULL,
  tipo_vehiculo VARCHAR(40) NOT NULL,
  origen VARCHAR(80) NOT NULL,
  destino VARCHAR(80) NOT NULL,
  horario VARCHAR(30) NOT NULL,
  costo INT NOT NULL,
  activo TINYINT(1) DEFAULT 1,
  FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE
);

-- TICKETS
CREATE TABLE IF NOT EXISTS tickets (
  id INT AUTO_INCREMENT PRIMARY KEY,
  empresa_id INT NOT NULL,
  ruta_id INT,
  tipo_vehiculo VARCHAR(40) NOT NULL,
  fecha DATE NOT NULL,
  horario VARCHAR(30) NOT NULL,
  origen VARCHAR(80) NOT NULL,
  destino VARCHAR(80) NOT NULL,
  sillas VARCHAR(120),
  cantidad INT NOT NULL DEFAULT 1,
  costo_unitario INT NOT NULL,
  total INT NOT NULL,

  cliente_nombre VARCHAR(120) NOT NULL,
  cliente_cedula VARCHAR(40)  NOT NULL,
  cliente_contacto VARCHAR(120) NOT NULL, -- celular o email

  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

  FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE RESTRICT
);

-- Datos de EMPRESAS
INSERT INTO empresas (nombre, descripcion, logo_ruta, tipos_vehiculos, telefono, correo, direccion) VALUES
('Transportico SAS','Taxi y Aerovan urbano/aeropuerto','images/Transportico.png','Taxi,Aerovan','(604) 555 1010','contacto@transportico.com','Local 12, Módulo A — Terminal'),
('Trans Vanegas','Intermunicipal: Gacela y buses','images/Trans vanegas.png','Bus 2 pisos','(604) 555 2020','info@losvanegas.co','Local 5, Módulo B — Terminal'),
('El Dorado','Media/larga distancia, buses 2 pisos y busetas','images/Eldorado.png','Bus 2 pisos,Busetas','(604) 555 3030','servicio@eldorado.com.co','Local 18, Módulo C — Terminal'),
('Trans Volver','Urbano/intermunicipal: bus, aerovan, taxi','images/Trans volver .png','Bus,Aerovan,Taxi','(604) 555 4040','reservas@transvolver.co','Local 2, Módulo A — Terminal'),
('Servi Rutas Ltda.','Metropolitano/empresarial: bus, taxi y busetas','images/Servirutasltda.png','Bus,Taxi,Busetas','(604) 555 5050','comercial@Servirutasltda.com','Local 9, Módulo B — Terminal');

-- Datos de RUTAS
INSERT INTO rutas (empresa_id, tipo_vehiculo, origen, destino, horario, costo)
SELECT e.id,'TAXI','Marinilla','Medellín','04:00 am',100000 FROM empresas e WHERE e.nombre='Transportico SAS';
INSERT INTO rutas (empresa_id, tipo_vehiculo, origen, destino, horario, costo)
SELECT e.id,'AEROVAN','Marinilla','Medellín','05:00 am',100000 FROM empresas e WHERE e.nombre='Transportico SAS';
INSERT INTO rutas (empresa_id, tipo_vehiculo, origen, destino, horario, costo)
SELECT e.id,'BUS_2PISOS','Marinilla','Manizales','07:00 am',110000 FROM empresas e WHERE e.nombre='Trans Vanegas';
INSERT INTO rutas (empresa_id, tipo_vehiculo, origen, destino, horario, costo)
SELECT e.id,'BUS_2PISOS','Marinilla','Cali','12:00 pm',140000 FROM empresas e WHERE e.nombre='El Dorado';
INSERT INTO rutas (empresa_id, tipo_vehiculo, origen, destino, horario, costo)
SELECT e.id,'BUS_1PISO','Marinilla','Barranquilla','07:00 pm',200000 FROM empresas e WHERE e.nombre='Servi Rutas Ltda.';
INSERT INTO rutas (empresa_id, tipo_vehiculo, origen, destino, horario, costo)
SELECT e.id,'AEROVAN','Marinilla','Rionegro','08:00 pm',10000 FROM empresas e WHERE e.nombre='Trans Volver';
