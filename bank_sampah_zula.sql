-- =========================================================
-- Database: bank_sampah_zula
-- Dibuat berdasarkan ERD yang dikirim
-- =========================================================

CREATE DATABASE IF NOT EXISTS bank_sampah_zula
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_general_ci;

USE bank_sampah_zula;

-- ---------------------------------------------------------
-- Tabel: users
-- ---------------------------------------------------------
CREATE TABLE users (
    id_user    INT AUTO_INCREMENT PRIMARY KEY,
    nama       VARCHAR(100)    NOT NULL,
    username   VARCHAR(50)     NOT NULL UNIQUE,
    password   VARCHAR(255)    NOT NULL,
    role       ENUM('admin','guru','pengepul','siswa') NOT NULL DEFAULT 'siswa',
    saldo      DECIMAL(12,2)   NOT NULL DEFAULT 0
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Tabel: sampah (master jenis sampah)
-- ---------------------------------------------------------
CREATE TABLE sampah (
    id_sampah     INT AUTO_INCREMENT PRIMARY KEY,
    jenis_sampah  VARCHAR(100)   NOT NULL,
    harga_per_kg  DECIMAL(10,2)  NOT NULL,
    stok_kg       DECIMAL(10,2)  NOT NULL DEFAULT 0
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Tabel: transaksi (setor / tarik sampah oleh user)
-- 1 sampah -> N transaksi
-- ---------------------------------------------------------
CREATE TABLE transaksi (
    id_transaksi  INT AUTO_INCREMENT PRIMARY KEY,
    id_user       INT             NOT NULL,
    id_sampah     INT             NOT NULL,
    tipe          ENUM('setor','tarik') NOT NULL DEFAULT 'setor',
    berat_kg      DECIMAL(10,2)   NOT NULL,
    total_rp      DECIMAL(12,2)   NOT NULL,
    tanggal       DATE            NOT NULL,
    CONSTRAINT fk_transaksi_sampah
        FOREIGN KEY (id_sampah) REFERENCES sampah(id_sampah)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_transaksi_user
        FOREIGN KEY (id_user) REFERENCES users(id_user)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Tabel: setoran (di ERD tergambar dobel label "id_user",
-- ini tabel saldo/penarikan per user: N setoran -> 1 user)
-- ---------------------------------------------------------
CREATE TABLE setoran (
    id_setoran  INT AUTO_INCREMENT PRIMARY KEY,
    id_user     INT             NOT NULL,
    nominal     DECIMAL(12,2)   NOT NULL,
    tanggal     DATE            NOT NULL,
    status      ENUM('pending','disetujui','ditolak') NOT NULL DEFAULT 'pending',
    CONSTRAINT fk_setoran_user
        FOREIGN KEY (id_user) REFERENCES users(id_user)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Tabel: pengambilan_sampah (pengepul catat sampah yang diambil/terjual)
-- Murni ngurangin stok_kg, gak nyentuh saldo siapapun
-- ---------------------------------------------------------
CREATE TABLE pengambilan_sampah (
    id_pengambilan INT AUTO_INCREMENT PRIMARY KEY,
    id_sampah      INT             NOT NULL,
    id_user        INT             NOT NULL,
    berat_kg       DECIMAL(10,2)   NOT NULL,
    tanggal        DATE            NOT NULL,
    catatan        VARCHAR(255)    DEFAULT NULL,
    CONSTRAINT fk_pengambilan_sampah
        FOREIGN KEY (id_sampah) REFERENCES sampah(id_sampah)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_pengambilan_user
        FOREIGN KEY (id_user) REFERENCES users(id_user)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Index tambahan biar query cepat
-- ---------------------------------------------------------
CREATE INDEX idx_transaksi_user   ON transaksi(id_user);
CREATE INDEX idx_transaksi_sampah ON transaksi(id_sampah);
CREATE INDEX idx_setoran_user     ON setoran(id_user);
CREATE INDEX idx_pengambilan_sampah ON pengambilan_sampah(id_sampah);
CREATE INDEX idx_pengambilan_user   ON pengambilan_sampah(id_user);
