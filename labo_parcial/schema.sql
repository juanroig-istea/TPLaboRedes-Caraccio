CREATE TABLE IF NOT EXISTS login (
  user VARCHAR(80) PRIMARY KEY,
  password VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS jugadores (
  numero_jugador INT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  apellido VARCHAR(100) NOT NULL,
  dni VARCHAR(30) NOT NULL,
  telefono VARCHAR(40) NOT NULL,
  categoria ENUM('Primera','Reserva','Sub 20','Sub 17','Sub 15','Infantiles') NOT NULL DEFAULT 'Primera',
  posicion ENUM('Arquero','Defensor','Mediocampista','Delantero') NOT NULL DEFAULT 'Mediocampista',
  fecha_nacimiento DATE NOT NULL,
  obra_social VARCHAR(120) NOT NULL,
  apto_medico ENUM('Vigente','Pendiente','Vencido') NOT NULL DEFAULT 'Pendiente',
  estado ENUM('Activo','Lesionado','Suspendido','Baja') NOT NULL DEFAULT 'Activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Crear usuario inicial. Reemplaza el hash con el generado por tools/generar_hash.php
-- INSERT INTO login (user, password) VALUES ('admin', '$2y$10$HASH_AQUI');
