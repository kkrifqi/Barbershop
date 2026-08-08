-- ============================================================
-- Migration v2: Database Upgrade for Image Support & Enhanced Features
-- Database: db_barbershop
-- ============================================================

USE db_barbershop;

-- 1. Upgrade Tabel Services (Tambah Gambar, Deskripsi, & Flag Popularity)
ALTER TABLE services
ADD COLUMN IF NOT EXISTS foto VARCHAR(255) DEFAULT NULL AFTER kategori,
ADD COLUMN IF NOT EXISTS deskripsi TEXT DEFAULT NULL AFTER harga,
ADD COLUMN IF NOT EXISTS is_popular TINYINT(1) DEFAULT 0 AFTER durasi;

-- 2. Upgrade Tabel Barbers (Tambah Tag Spesialisasi & Rating)
ALTER TABLE barbers
ADD COLUMN IF NOT EXISTS spesialisasi_tags VARCHAR(255) DEFAULT 'Haircut, Styling' AFTER keahlian,
ADD COLUMN IF NOT EXISTS rating DECIMAL(2,1) DEFAULT 4.9 AFTER deskripsi;

-- 3. Upgrade Tabel Gallery (Tambah Judul, Kategori Filter, & Deskripsi)
ALTER TABLE gallery
ADD COLUMN IF NOT EXISTS judul VARCHAR(150) DEFAULT 'Gentleman Style' AFTER id,
ADD COLUMN IF NOT EXISTS kategori VARCHAR(50) DEFAULT 'Haircut' AFTER judul,
ADD COLUMN IF NOT EXISTS deskripsi TEXT DEFAULT NULL AFTER kategori;

-- ============================================================
-- Sample Data Updates (Gambar High Resolution Barbershop)
-- ============================================================

UPDATE services SET 
    foto = 'assets/img/service-cut.jpg',
    deskripsi = 'Potongan rambut presisi tinggi sesuai kontur wajah, disempurnakan dengan hair styling pomade.',
    is_popular = 1
WHERE id = 1;

UPDATE services SET 
    foto = 'assets/img/service-cut.jpg',
    deskripsi = 'Layanan cukur lengkap dengan cuci keramas, relaksasi hair tonic, dan styling.',
    is_popular = 1
WHERE id = 2;

UPDATE services SET 
    foto = 'assets/img/service-beard.jpg',
    deskripsi = 'Perawatan royal lengkap: Potong rambut, keramas, pijat pundak & kepala, serta warm towel beard shave.',
    is_popular = 1
WHERE id = 3;

UPDATE barbers SET 
    foto = 'assets/img/barber-wahyu.jpg',
    spesialisasi_tags = 'Modern Fade, Textured Crop, Beard Trim',
    rating = 4.9
WHERE id = 1;

UPDATE barbers SET 
    foto = 'assets/img/barber-wahyu.jpg',
    spesialisasi_tags = 'Beard Sculpting, Hot Towel Shave, Razor Line',
    rating = 5.0
WHERE id = 2;

UPDATE barbers SET 
    foto = 'assets/img/barber-wahyu.jpg',
    spesialisasi_tags = 'Textured Cut, Messy Modern, Hair Color',
    rating = 4.8
WHERE id = 3;

-- Sample Gallery Inserts
INSERT INTO gallery (judul, kategori, foto, deskripsi) VALUES
('Textured Skin Fade', 'Haircut', 'assets/img/gallery-fade.jpg', 'Potongan fade bersih dengan tekstur atas modern'),
('Executive Gentleman Cut', 'Styling', 'assets/img/hero-model.jpg', 'Gaya rambut klasik pria eksekutif presisi'),
('Royal Beard Sculpt', 'Beard', 'assets/img/service-beard.jpg', 'Perawatan jenggot dengan handuk hangat dan razor presisi');
