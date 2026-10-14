-- =========================================================
-- Migrasi: role users dari (admin, nasabah) jadi 4 role
-- (admin, guru, pengepul, siswa)
-- Jalankan ini SEKALI di database bank_sampah_zula yang udah ada.
-- =========================================================

USE bank_sampah_zula;

-- 1. Perbesar ENUM role dulu (data lama 'nasabah' masih valid sementara)
ALTER TABLE users
    MODIFY COLUMN role ENUM('admin','nasabah','guru','pengepul','siswa') NOT NULL DEFAULT 'siswa';

-- 2. Ganti semua yang tadinya 'nasabah' jadi 'siswa'
UPDATE users SET role = 'siswa' WHERE role = 'nasabah';

-- 3. Baru persempit ENUM ke 4 role final (nasabah udah gak dipakai lagi)
ALTER TABLE users
    MODIFY COLUMN role ENUM('admin','guru','pengepul','siswa') NOT NULL DEFAULT 'siswa';

-- 4. Tabel baru: catatan pengambilan/penjualan sampah oleh pengepul
--    (murni ngurangin stok_kg, gak nyentuh saldo siapapun)
CREATE TABLE IF NOT EXISTS pengambilan_sampah (
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

CREATE INDEX idx_pengambilan_sampah ON pengambilan_sampah(id_sampah);
CREATE INDEX idx_pengambilan_user   ON pengambilan_sampah(id_user);
