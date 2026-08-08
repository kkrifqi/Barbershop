<?php
// ============================================================
//  barber.php — Official Modern Barbershop Front-End
//  Crown Barbershop Studio
// ============================================================

require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/cek_session.php';

// ── 1. Ambil semua settings ──────────────────────────────────
$stmtSet = $pdo->query("SELECT kunci, nilai FROM settings");
$s = [];
foreach ($stmtSet->fetchAll() as $row) {
    $s[$row['kunci']] = $row['nilai'];
}

function set(string $key, array $s, string $default = ''): string {
    return htmlspecialchars($s[$key] ?? $default, ENT_QUOTES, 'UTF-8');
}

// Helper untuk format URL gambar dengan fallback file_exists() yang aman
function formatImgUrl(?string $path, string $fallback = '../assets/img/service-cut.jpg'): string {
    if (empty($path)) return $fallback;
    if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
        return htmlspecialchars($path, ENT_QUOTES, 'UTF-8');
    }
    $cleanPath = ltrim(htmlspecialchars($path, ENT_QUOTES, 'UTF-8'), '/');
    if (file_exists(__DIR__ . '/../' . $cleanPath)) {
        return '../' . $cleanPath;
    }
    return $fallback;
}

// ── 2. Data Queries ──────────────────────────────────────────
$stmtBarber = $pdo->query("SELECT * FROM barbers WHERE status = 'aktif' ORDER BY id ASC");
$barbers    = $stmtBarber->fetchAll();

$stmtSvc  = $pdo->query("SELECT * FROM services ORDER BY kategori, harga ASC");
$services = $stmtSvc->fetchAll();

// Pisahkan layanan berdasarkan kategori Dewasa & Anak-anak
$svcDewasa = array_filter($services, fn($r) => $r['kategori'] === 'Dewasa');
$svcAnak   = array_filter($services, fn($r) => $r['kategori'] === 'Anak-anak');

$stmtGal = $pdo->query("SELECT * FROM gallery ORDER BY id DESC");
$gallery = $stmtGal->fetchAll();

$hariList = [
    'Senin'   => $s['jam_senin']   ?? '10.00 - 20.00',
    'Selasa'  => $s['jam_selasa']  ?? '11.00 - 21.00',
    'Rabu'    => $s['jam_rabu']    ?? '09.00 - 20.00',
    'Kamis'   => $s['jam_kamis']   ?? '09.00 - 20.00',
    "Jum'at"  => $s['jam_jumat']   ?? 'Libur',
    'Sabtu'   => $s['jam_sabtu']   ?? '12.00 - 22.00',
    'Minggu'  => $s['jam_minggu']  ?? '08.00 - 21.00',
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= set('nama_barbershop', $s, 'Crown Barbershop') ?> | Official Grooming Studio</title>
    <meta name="description" content="Studio potong rambut pria profesional, modern haircut, skin fade, dan beard styling.">

    <!-- Ionicons -->
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">

    <!-- CSS Files -->
    <link rel="stylesheet" href="../assets/css/global.css">
    <link rel="stylesheet" href="../assets/css/barber.css">
</head>
<body>

    <!-- 1. HEADER & NAVIGATION -->
    <header class="site-header">
        <div class="nav-container">
            <a href="#hero" class="brand-logo-link">
                <img src="../assets/img/logo.svg" alt="<?= set('nama_barbershop', $s) ?> Logo" class="brand-logo-img">
                <div class="brand-text">CROWN <span>BARBER</span></div>
            </a>

            <nav class="nav-menu">
                <a href="#about" class="nav-link">Tentang</a>
                <a href="#services" class="nav-link">Layanan</a>
                <a href="#barbers" class="nav-link">Master Barber</a>
                <a href="#gallery" class="nav-link">Galeri</a>
                <a href="#location" class="nav-link">Lokasi</a>
                <a href="#feedback" class="nav-link">Feedback</a>
            </nav>

            <div class="nav-controls">
                <div id="userAuthBox">
                    <a href="../login-register/login.html" class="btn-nav-auth">
                        <ion-icon name="person-circle-outline"></ion-icon>
                        <span>Masuk</span>
                    </a>
                </div>

                <a href="../booking-page/index.html" class="btn-primary" style="padding: 8px 18px; font-size: 0.85rem;">
                    <span>Booking Online</span>
                </a>

                <button class="mobile-nav-toggle" id="mobileNavToggle" aria-label="Open Navigation">
                    <ion-icon name="menu-outline"></ion-icon>
                </button>
            </div>
        </div>
    </header>

    <!-- MOBILE DRAWER NAVIGATION -->
    <div class="drawer-backdrop" id="drawerBackdrop"></div>
    <div class="mobile-drawer" id="mobileDrawer">
        <div class="drawer-header">
            <div class="brand-text">CROWN <span>BARBER</span></div>
            <button class="btn-close-drawer" id="closeDrawerBtn">
                <ion-icon name="close-outline"></ion-icon>
            </button>
        </div>
        <div class="mobile-nav-links">
            <a href="#about">Tentang Kami</a>
            <a href="#services">Daftar Layanan</a>
            <a href="#barbers">Master Barber</a>
            <a href="#gallery">Galeri Portofolio</a>
            <a href="#location">Lokasi & Jam</a>
            <a href="#feedback">Feedback</a>
            <a href="../booking-page/index.html" style="color: var(--color-gold);">⚡ Booking Online</a>
        </div>
    </div>


    <!-- 2. HERO SECTION -->
    <section class="hero-section" id="hero">
        <div class="hero-container">
            <div class="hero-content">
                <div class="hero-badge">
                    <span class="badge-gold">
                        <ion-icon name="shield-checkmark-sharp"></ion-icon>
                        <?= set('nama_barbershop', $s, 'Crown Barbershop') ?>
                    </span>
                </div>
                
                <h1 class="hero-title">
                    Precision Haircuts & <br>
                    <span>Luxury Grooming.</span>
                </h1>
                
                <p class="hero-subtitle">
                    <?= set('tagline', $s, 'Pengalaman cukur rambut pria profesional dengan teknik presisi, suasana studio modern, dan kenyamanan maksimal.') ?>
                </p>

                <div class="hero-cta-group">
                    <a href="../booking-page/index.html" class="btn-primary">
                        <ion-icon name="calendar-sharp"></ion-icon>
                        <span>Booking Jadwal</span>
                    </a>
                    <a href="#services" class="btn-secondary">
                        <ion-icon name="cut-sharp"></ion-icon>
                        <span>Lihat Layanan</span>
                    </a>
                </div>

                <div class="hero-stats">
                    <div class="stat-item">
                        <h3><?= count($barbers) ?>+</h3>
                        <p>Master Barber</p>
                    </div>
                    <div class="stat-item">
                        <h3>4.9★</h3>
                        <p>Rating Pelanggan</p>
                    </div>
                    <div class="stat-item">
                        <h3>100%</h3>
                        <p>Presisi & Higenis</p>
                    </div>
                </div>
            </div>

            <div class="hero-visual">
                <div class="hero-image-card">
                    <img src="../assets/img/hero-model.jpg" alt="Gentleman Haircut Experience">
                    <div class="hero-card-overlay">
                        <div class="overlay-icon">
                            <ion-icon name="sparkles-sharp"></ion-icon>
                        </div>
                        <div class="overlay-text">
                            <strong>Luxury Studio Experience</strong>
                            <span>Comfort & Precision Cuts</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <!-- 3. ABOUT US SECTION -->
    <section class="about-section" id="about">
        <div class="about-grid">
            <div class="about-image-card">
                <img src="../assets/img/about-interior.jpg" alt="Barbershop Interior">
            </div>

            <div class="about-content">
                <span class="section-subtitle">Tentang Kami</span>
                <h2>Crown Barbershop Studio</h2>

                <p><?= set('about_p1', $s) ?></p>
                <p><?= set('about_p2', $s) ?></p>
                <p><?= set('about_p3', $s) ?></p>

                <div class="features-grid">
                    <div class="feature-box">
                        <ion-icon name="checkmark-circle-sharp"></ion-icon>
                        <span>Peralatan Higienis & Steril</span>
                    </div>
                    <div class="feature-box">
                        <ion-icon name="checkmark-circle-sharp"></ion-icon>
                        <span>Produk Premium Choice</span>
                    </div>
                    <div class="feature-box">
                        <ion-icon name="checkmark-circle-sharp"></ion-icon>
                        <span>Welcome Drink Complimentary</span>
                    </div>
                    <div class="feature-box">
                        <ion-icon name="checkmark-circle-sharp"></ion-icon>
                        <span>Hot Towel & Scalp Massage</span>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <!-- 4. SERVICES SECTION -->
    <section class="services-section" id="services">
        <div class="section-header">
            <span class="section-subtitle">Pilihan Treatment</span>
            <h2 class="section-title">Layanan & Pricelist</h2>
            <p class="section-desc">Pilihan treatment lengkap yang dikelompokkan berdasarkan kategori Dewasa & Anak-Anak.</p>
        </div>

        <!-- Filter Tab Buttons dengan Inline Onclick & Fallback -->
        <div class="category-tabs">
            <button type="button" class="tab-btn active" data-category="all" onclick="switchServiceCategory('all')">Semua Layanan</button>
            <button type="button" class="tab-btn" data-category="Dewasa" onclick="switchServiceCategory('Dewasa')">Layanan Dewasa</button>
            <button type="button" class="tab-btn" data-category="Anak-anak" onclick="switchServiceCategory('Anak-anak')">Layanan Anak-Anak</button>
        </div>

        <!-- KELOMPOK LAYANAN DEWASA -->
        <div class="service-category-group" id="group-dewasa" style="margin-bottom: 3.5rem;">
            <div class="category-group-header">
                <h3 class="category-group-title">
                    <ion-icon name="person-sharp" style="color: var(--color-gold);"></ion-icon>
                    Kategori Layanan Dewasa
                </h3>
            </div>
            <div class="services-grid">
                <?php foreach ($svcDewasa as $svc): 
                    $imgFallback = ($svc['nama'] === 'Cukur + Cuci + Pijat') ? '../assets/img/service-beard.jpg' : '../assets/img/service-cut.jpg';
                ?>
                <div class="service-card" data-category="Dewasa">
                    <img src="<?= formatImgUrl($svc['foto'] ?? null, $imgFallback) ?>" alt="<?= htmlspecialchars($svc['nama']) ?>" class="service-img">

                    <div class="service-body">
                        <div class="service-head">
                            <h3 class="service-title-text"><?= htmlspecialchars($svc['nama']) ?></h3>
                            <div class="service-price">Rp <?= number_format($svc['harga'], 0, ',', '.') ?></div>
                        </div>

                        <div class="service-meta">
                            <span>⏱ <?= (int)$svc['durasi'] ?> Menit</span>
                            <span>•</span>
                            <span class="badge-gold">Dewasa</span>
                        </div>

                        <p class="service-desc">
                            <?= !empty($svc['deskripsi']) ? htmlspecialchars($svc['deskripsi']) : 'Pelayanan potong rambut profesional dengan konsultasi bentuk wajah dan hair styling.' ?>
                        </p>

                        <a href="../booking-page/index.html?service_id=<?= $svc['id'] ?>" class="btn-select-service">
                            Pilih Service Dewasa
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- KELOMPOK LAYANAN ANAK-ANAK -->
        <div class="service-category-group" id="group-anak">
            <div class="category-group-header">
                <h3 class="category-group-title">
                    <ion-icon name="happy-sharp" style="color: var(--color-gold);"></ion-icon>
                    Kategori Layanan Anak-Anak
                </h3>
            </div>
            <div class="services-grid">
                <?php foreach ($svcAnak as $svc): 
                    $imgFallback = '../assets/img/service-cut.jpg';
                ?>
                <div class="service-card" data-category="Anak-anak">
                    <img src="<?= formatImgUrl($svc['foto'] ?? null, $imgFallback) ?>" alt="<?= htmlspecialchars($svc['nama']) ?>" class="service-img">

                    <div class="service-body">
                        <div class="service-head">
                            <h3 class="service-title-text"><?= htmlspecialchars($svc['nama']) ?></h3>
                            <div class="service-price">Rp <?= number_format($svc['harga'], 0, ',', '.') ?></div>
                        </div>

                        <div class="service-meta">
                            <span>⏱ <?= (int)$svc['durasi'] ?> Menit</span>
                            <span>•</span>
                            <span class="badge-gold">Anak-Anak</span>
                        </div>

                        <p class="service-desc">
                            <?= !empty($svc['deskripsi']) ? htmlspecialchars($svc['deskripsi']) : 'Potong rambut anak yang ramah, nyaman, sabar, dan rapi.' ?>
                        </p>

                        <a href="../booking-page/index.html?service_id=<?= $svc['id'] ?>" class="btn-select-service">
                            Pilih Service Anak
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>


    <!-- 5. BARBERS CREW SECTION -->
    <section class="barbers-section" id="barbers">
        <div class="section-header">
            <span class="section-subtitle">Tim Profesional</span>
            <h2 class="section-title">Master Barber Kami</h2>
            <p class="section-desc">Barber berpengalaman yang siap membantu menemukan gaya rambut terbaik Anda.</p>
        </div>

        <div class="barbers-grid">
            <?php foreach ($barbers as $b): ?>
            <div class="barber-card">
                <div class="barber-img-wrap">
                    <img src="<?= formatImgUrl($b['foto'] ?? null, '../assets/img/barber-wahyu.jpg') ?>" alt="<?= htmlspecialchars($b['nama']) ?>">
                    <div class="rating-badge">
                        <ion-icon name="star-sharp"></ion-icon>
                        <span><?= htmlspecialchars($b['rating'] ?? '4.9') ?></span>
                    </div>
                </div>

                <div class="barber-body">
                    <h3 class="barber-name"><?= htmlspecialchars($b['nama']) ?></h3>

                    <div class="tags-list">
                        <?php 
                            $tags = explode(',', $b['spesialisasi_tags'] ?? $b['keahlian'] ?? 'Classic Cut, Modern Fade');
                            foreach ($tags as $tag):
                        ?>
                            <span class="tag-pill">#<?= htmlspecialchars(trim($tag)) ?></span>
                        <?php endforeach; ?>
                    </div>

                    <p class="barber-desc"><?= htmlspecialchars($b['deskripsi'] ?? 'Berpengalaman dalam potongan rambut klasik dan modern skin fade.') ?></p>

                    <a href="../booking-page/index.html?barber_id=<?= $b['id'] ?>" class="btn-secondary" style="width: 100%; text-align: center; justify-content: center;">
                        Pilih Barber Ini
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>


    <!-- 6. PORTFOLIO GALLERY SECTION -->
    <section class="gallery-section" id="gallery">
        <div class="section-header">
            <span class="section-subtitle">Portofolio</span>
            <h2 class="section-title">Galeri Hasil Cukur</h2>
            <p class="section-desc">Koleksi foto hasil potongan rambut dan kepuasan pelanggan di Crown Barbershop.</p>
        </div>

        <div class="gallery-grid">
            <?php if ($gallery): ?>
                <?php foreach ($gallery as $foto): ?>
                <div class="gallery-card">
                    <img src="<?= formatImgUrl($foto['foto'] ?? null, '../assets/img/gallery-fade.jpg') ?>" alt="Hasil Cukur">
                    <div class="gallery-overlay">
                        <span class="gallery-cat"><?= htmlspecialchars($foto['kategori'] ?? 'Haircut') ?></span>
                        <h4 class="gallery-title"><?= htmlspecialchars($foto['judul'] ?? 'Modern Gentleman Fade') ?></h4>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="gallery-card">
                    <img src="../assets/img/gallery-fade.jpg" alt="Textured Fade Cut">
                    <div class="gallery-overlay">
                        <span class="gallery-cat">Haircut</span>
                        <h4 class="gallery-title">Textured Skin Fade</h4>
                    </div>
                </div>
                <div class="gallery-card">
                    <img src="../assets/img/hero-model.jpg" alt="Gentleman Styling">
                    <div class="gallery-overlay">
                        <span class="gallery-cat">Styling</span>
                        <h4 class="gallery-title">Executive Gentleman Cut</h4>
                    </div>
                </div>
                <div class="gallery-card">
                    <img src="../assets/img/service-beard.jpg" alt="Beard Grooming">
                    <div class="gallery-overlay">
                        <span class="gallery-cat">Beard Grooming</span>
                        <h4 class="gallery-title">Royal Beard Sculpt</h4>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- LIGHTBOX MODAL -->
    <div class="lightbox-modal" id="lightboxModal">
        <div class="lightbox-content">
            <button class="lightbox-close" id="lightboxCloseBtn">&times;</button>
            <img src="" id="lightboxImage" alt="Gallery Preview">
        </div>
    </div>


    <!-- 7. LOCATION & OPERATIONAL HOURS SECTION -->
    <section class="location-section" id="location">
        <div class="section-header">
            <span class="section-subtitle">Kunjungi Studio Kami</span>
            <h2 class="section-title">Lokasi & Jam Operasional</h2>
        </div>

        <div class="location-grid">
            <div class="map-box">
                <h3>Lokasi Studio</h3>
                <div class="iframe-wrap">
                    <iframe
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d12856.305090615931!2d107.53946956769138!3d-7.041821542234824!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e68e8d93186776d%3A0x4c85be42268f3b45!2sPolresta%20Bandung!5e0!3m2!1sid!2sid!4v1771549862886!5m2!1sid!2sid"
                        allowfullscreen="" loading="lazy">
                    </iframe>
                </div>
                <p style="color: var(--text-muted); font-size: 0.9rem;">
                    <strong><?= set('nama_barbershop', $s) ?></strong><br>
                    <?= nl2br(set('alamat', $s, 'Jl. Melati Indah No. 27, Kota Seruni')) ?>
                </p>
            </div>

            <div class="hours-box">
                <h3>Jam Operasional</h3>
                <div class="hours-list">
                    <?php foreach ($hariList as $hari => $jam): 
                        $isClosed = (mb_strtolower(trim($jam)) === 'libur' || mb_strtolower(trim($jam)) === 'tutup');
                    ?>
                    <div class="hour-item <?= $isClosed ? 'closed' : '' ?>">
                        <span class="day"><?= htmlspecialchars($hari) ?></span>
                        <span class="time"><?= htmlspecialchars($jam) ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>


    <!-- 8. FEEDBACK FORM SECTION -->
    <section class="feedback-section" id="feedback">
        <div class="feedback-card">
            <div class="section-header" style="margin-bottom: 2rem;">
                <span class="section-subtitle">Masukan Anda</span>
                <h2 class="section-title">Kirimkan Feedback</h2>
                <p class="section-desc">Bantu kami memberikan pengalaman terbaik untuk setiap kunjungan Anda.</p>
            </div>

            <form action="#" method="POST" onsubmit="event.preventDefault(); alert('Terima kasih atas feedback Anda!');">
                <div class="form-grid">
                    <div class="input-group">
                        <label>Nama Lengkap</label>
                        <input type="text" placeholder="Nama Anda" required>
                    </div>
                    <div class="input-group">
                        <label>Alamat Email</label>
                        <input type="email" placeholder="nama@email.com" required>
                    </div>
                </div>

                <div class="input-group" style="margin-bottom: 1.5rem;">
                    <label>Pesan Feedback</label>
                    <textarea placeholder="Tuliskan masukan atau masukan Anda di sini..." required></textarea>
                </div>

                <button type="submit" class="btn-primary" style="width: 100%;">
                    <ion-icon name="paper-plane-sharp"></ion-icon>
                    <span>Kirim Feedback</span>
                </button>
            </form>
        </div>
    </section>


    <!-- 9. FOOTER -->
    <footer class="site-footer">
        <div class="footer-container">
            <div class="footer-brand">
                <div class="brand-text" style="font-size: 1.3rem;">CROWN <span>BARBER</span></div>
                <p><?= set('about_p1', $s, 'Studio potong rambut pria presisi dengan standar pelayanan profesional.') ?></p>
            </div>

            <div>
                <h4 style="font-size: 0.95rem; margin-bottom: 0.8rem; color: #fff;">Navigasi</h4>
                <ul style="display: flex; flex-direction: column; gap: 6px; font-size: 0.88rem; color: var(--text-muted);">
                    <li><a href="#about">Tentang Kami</a></li>
                    <li><a href="#services">Daftar Layanan</a></li>
                    <li><a href="#barbers">Master Barber</a></li>
                    <li><a href="#gallery">Galeri Portofolio</a></li>
                    <li><a href="../booking-page/index.html" style="color: var(--color-gold);">Booking Online</a></li>
                </ul>
            </div>

            <div>
                <h4 style="font-size: 0.95rem; margin-bottom: 0.8rem; color: #fff;">Kontak</h4>
                <p style="font-size: 0.88rem; color: var(--text-muted); margin-bottom: 4px;">
                    <ion-icon name="call-sharp" style="color: var(--color-gold);"></ion-icon> <?= set('telepon', $s, '(021) 8890 1122') ?>
                </p>
                <p style="font-size: 0.88rem; color: var(--text-muted);">
                    <ion-icon name="mail-sharp" style="color: var(--color-gold);"></ion-icon> <?= set('email', $s, 'info@crownbarber.com') ?>
                </p>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; <?= date('Y') ?> <?= set('nama_barbershop', $s, 'Crown Barbershop') ?>. All Rights Reserved.</p>
        </div>
    </footer>

    <!-- JS Files -->
    <script src="../assets/js/barber.js"></script>
</body>
</html>
