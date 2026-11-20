CREATE DATABASE IF NOT EXISTS bioskop_db
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE bioskop_db;

CREATE TABLE IF NOT EXISTS films (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nama_film VARCHAR(150) NOT NULL,
    jadwal VARCHAR(100) NOT NULL,
    harga DECIMAL(12,2) NOT NULL DEFAULT 0,
    PRIMARY KEY (id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS seats (
    nomor_kursi VARCHAR(10) NOT NULL,
    status ENUM('tersedia', 'terisi') NOT NULL DEFAULT 'tersedia',
    PRIMARY KEY (nomor_kursi)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS orders (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    film_id INT UNSIGNED NOT NULL,
    kursi VARCHAR(255) NOT NULL,
    total_harga DECIMAL(12,2) NOT NULL,
    bukti_pembayaran VARCHAR(255) NOT NULL,
    status ENUM('menunggu', 'disetujui', 'ditolak') NOT NULL DEFAULT 'menunggu',
    kode_transaksi VARCHAR(20) DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_orders_kode (kode_transaksi),
    KEY idx_orders_film (film_id),
    CONSTRAINT fk_orders_film FOREIGN KEY (film_id) REFERENCES films(id)
) ENGINE=InnoDB;

INSERT INTO films (nama_film, jadwal, harga)
SELECT 'Contoh Film', 'Jumat, 19.00', 45000
WHERE NOT EXISTS (SELECT 1 FROM films LIMIT 1);

INSERT INTO seats (nomor_kursi, status)
SELECT CONCAT('A', numbers.n), 'tersedia'
FROM (
    SELECT 1 AS n UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5
    UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9 UNION ALL SELECT 10
) AS numbers
WHERE NOT EXISTS (SELECT 1 FROM seats LIMIT 1);
