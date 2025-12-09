-- BD Terminal (asientos por tipo/piso y estado por viaje)
CREATE DATABASE IF NOT EXISTS terminal CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE terminal;

-- ========== Empresas ==========
DROP TABLE IF EXISTS empresas;
CREATE TABLE empresas (
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

-- ========== Vehiculos]  ==========
DROP TABLE IF EXISTS vehiculos;
CREATE TABLE vehiculos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  empresa_id INT NOT NULL,
  tipo VARCHAR(40) NOT NULL,
  placa VARCHAR(15),
  capacidad INT,
  caracteristicas TEXT,
  FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE
);

-- ========== Rutas ==========
DROP TABLE IF EXISTS rutas;
CREATE TABLE rutas (
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

-- ========== Asientos ==========
DROP TABLE IF EXISTS plantillas_asientos;
CREATE TABLE plantillas_asientos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tipo_vehiculo VARCHAR(40) NOT NULL,
  piso INT NOT NULL DEFAULT 1,
  filas INT NOT NULL,
  columnas INT NOT NULL,
  UNIQUE KEY uniq_tipo_piso (tipo_vehiculo, piso)
);


INSERT INTO plantillas_asientos (tipo_vehiculo, piso, filas, columnas) VALUES
('TAXI',1,2,2),
('AEROVAN',1,4,3),
('BUS_1PISO',1,10,4),
('BUS_2PISOS',1,8,4),
('BUS_2PISOS',2,8,4);

-- ========== Asientos por viaje ==========
DROP TABLE IF EXISTS asientos_viaje;
CREATE TABLE asientos_viaje (
  id INT AUTO_INCREMENT PRIMARY KEY,
  ruta_id INT NOT NULL,
  fecha DATE NOT NULL,
  horario VARCHAR(30) NOT NULL,
  piso INT NOT NULL DEFAULT 1,
  asiento_numero INT NOT NULL,
  estado ENUM('DISPONIBLE','OCUPADO') NOT NULL DEFAULT 'DISPONIBLE',
  actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_viaje_asiento (ruta_id, fecha, horario, piso, asiento_numero),
  FOREIGN KEY (ruta_id) REFERENCES rutas(id) ON DELETE CASCADE
);

-- ========== Tickets ==========
DROP TABLE IF EXISTS tickets;
CREATE TABLE tickets (
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
  cliente_contacto VARCHAR(120) NOT NULL,

  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

  FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE RESTRICT,
  FOREIGN KEY (ruta_id) REFERENCES rutas(id) ON DELETE SET NULL
);

INSERT INTO empresas (nombre, descripcion, logo_ruta, tipos_vehiculos, telefono, correo, direccion) VALUES
('Transportico SAS','Taxi y Aerovan urbano/aeropuerto','images/Transportico.png','Taxi,Aerovan','(604) 555 1010','contacto@transportico.com','Local 12, Módulo A — Terminal'),
('Trans Vanegas','Intermunicipal: Gacela y buses','images/Trans vanegas.png','Bus 2 pisos','(604) 555 2020','info@losvanegas.co','Local 5, Módulo B — Terminal'),
('El Dorado','Media/larga distancia, buses 2 pisos y busetas','images/Eldorado.png','Bus 2 pisos,Busetas','(604) 555 3030','servicio@eldorado.com.co','Local 18, Módulo C — Terminal'),
('Trans Volver','Urbano/intermunicipal: bus, aerovan, taxi','images/Trans volver .png','Bus,Aerovan,Taxi','(604) 555 4040','reservas@transvolver.co','Local 2, Módulo A — Terminal'),
('Servi Rutas Ltda.','Metropolitano/empresarial: bus, taxi y busetas','images/Servirutasltda.png','Bus,Taxi,Busetas','(604) 555 5050','comercial@Servirutasltda.com','Local 9, Módulo B — Terminal');

-- Rutas 
INSERT INTO rutas (empresa_id, tipo_vehiculo, origen, destino, horario, costo)
SELECT id,'TAXI','Marinilla','Medellín','04:00 am',10000 FROM empresas WHERE nombre='Transportico SAS';
INSERT INTO rutas (empresa_id, tipo_vehiculo, origen, destino, horario, costo)
SELECT id,'AEROVAN','Marinilla','Medellín','05:00 am',100000 FROM empresas WHERE nombre='Transportico SAS';
INSERT INTO rutas (empresa_id, tipo_vehiculo, origen, destino, horario, costo)
SELECT id,'BUS_2PISOS','Marinilla','Manizales','07:00 am',110000 FROM empresas WHERE nombre='Trans Vanegas';
INSERT INTO rutas (empresa_id, tipo_vehiculo, origen, destino, horario, costo)
SELECT id,'BUS_2PISOS','Marinilla','Cali','12:00 pm',140000 FROM empresas WHERE nombre='El Dorado';
INSERT INTO rutas (empresa_id, tipo_vehiculo, origen, destino, horario, costo)
SELECT id,'BUS_1PISO','Marinilla','Barranquilla','07:00 pm',200000 FROM empresas WHERE nombre='Servi Rutas Ltda.';
INSERT INTO rutas (empresa_id, tipo_vehiculo, origen, destino, horario, costo)
SELECT id,'AEROVAN','Marinilla','Rionegro','08:00 pm',10000 FROM empresas WHERE nombre='Trans Volver';
