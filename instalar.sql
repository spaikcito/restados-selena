CREATE DATABASE IF NOT EXISTS selena_resultados CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE selena_resultados;
CREATE TABLE IF NOT EXISTS resultados (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 fecha DATE NOT NULL, proveedor VARCHAR(32) NOT NULL, loteria VARCHAR(100) NOT NULL,
 hora VARCHAR(20) NOT NULL, numero VARCHAR(3) NOT NULL, animal VARCHAR(100) NOT NULL,
 imagen TEXT NULL, actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 UNIQUE KEY uniq_resultado (fecha,proveedor,loteria,hora,numero),
 INDEX idx_fecha_loteria (fecha,loteria), INDEX idx_fecha_proveedor (fecha,proveedor)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
