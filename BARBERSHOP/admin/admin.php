<?php
// ============================================================
//  admin.php — Admin Panel Crown Barbershop
// ============================================================

require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/cek_session.php';
guardAdmin();

// ── Ambil semua barber dari DB ───────────────────────────────
$stmtBarber = $pdo->query("SELECT * FROM barbers ORDER BY id ASC");
$barbers    = $stmtBarber->fetchAll();

// ── Ambil semua service dari DB ──────────────────────────────
$stmtSvc  = $pdo->query("SELECT * FROM services ORDER BY kategori, harga ASC");
$services = $stmtSvc->fetchAll();

$svcDewasa = array_filter($services, fn($r) => $r['kategori'] === 'Dewasa');
$svcAnak   = array_filter($services, fn($r) => $r['kategori'] === 'Anak-anak');

// ── Ambil semua foto gallery dari DB ────────────────────────
$stmtGal = $pdo->query("SELECT * FROM gallery ORDER BY uploaded_at DESC");
$gallery = $stmtGal->fetchAll();

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crown Barbershop | Admin Panel</title>

    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="../assets/css/global.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>

<div class="admin-wrapper">

    <!-- SIDEBAR -->
    <div class="sidebar">
        <div class="sidebar-brand">
            <div class="brand-header-wrap">
                <img src="../assets/img/logo.svg" alt="Crown Logo" class="brand-header-logo">
                <h2>CROWN <span>BARBER</span></h2>
            </div>
            <span class="subtitle">ADMIN PANEL</span>
        </div>

        <nav class="sidebar-nav">
            <a href="#" class="nav-link active" data-page="dashboard">
                <ion-icon name="grid-outline"></ion-icon> Dashboard
            </a>
            <a href="#" class="nav-link" data-page="booking">
                <ion-icon name="calendar-outline"></ion-icon> Booking
                <span class="nav-badge" id="badge-booking">4</span>
            </a>
            <a href="#" class="nav-link" data-page="barber">
                <ion-icon name="people-outline"></ion-icon> Barber
            </a>
            <a href="#" class="nav-link" data-page="service">
                <ion-icon name="list-outline"></ion-icon> Service
            </a>
            <a href="#" class="nav-link" data-page="gallery">
                <ion-icon name="images-outline"></ion-icon> Gallery
            </a>
            <a href="#" class="nav-link" data-page="settings">
                <ion-icon name="settings-outline"></ion-icon> Settings
            </a>
        </nav>

        <div class="sidebar-footer">
            <ion-icon name="person-circle-outline"></ion-icon>
            <span><?= htmlspecialchars($namaUser) ?></span>
            <button class="btn-logout-admin" id="btn-logout-admin" title="Logout">
                <ion-icon name="log-out-outline"></ion-icon>
            </button>
        </div>
    </div>
    <!-- END SIDEBAR -->


    <!-- MAIN CONTENT -->
    <div class="main-content">

        <!-- TOPBAR -->
        <div class="topbar">
            <h3 class="topbar-title" id="topbar-title">Dashboard</h3>
            <a href="../pages/barber.php" class="btn-ke-user">
                <ion-icon name="arrow-back-outline"></ion-icon>
                Lihat Website
            </a>
        </div>


        <!-- HALAMAN: DASHBOARD -->
        <div class="page active" id="page-dashboard">

            <div class="section-title">
                <h1>Dashboard</h1>
                <div class="line"></div>
            </div>

            <div class="kartu-grid">
                <div class="kartu">
                    <div class="kartu-icon"><ion-icon name="calendar-outline"></ion-icon></div>
                    <div class="kartu-info">
                        <p class="kartu-label">Booking Hari Ini</p>
                        <h2 class="kartu-angka">12</h2>
                    </div>
                </div>
                <div class="kartu kartu--yellow">
                    <div class="kartu-icon"><ion-icon name="time-outline"></ion-icon></div>
                    <div class="kartu-info">
                        <p class="kartu-label">Booking Pending</p>
                        <h2 class="kartu-angka">4</h2>
                    </div>
                </div>
                <div class="kartu kartu--green">
                    <div class="kartu-icon"><ion-icon name="people-outline"></ion-icon></div>
                    <div class="kartu-info">
                        <p class="kartu-label">Barber Aktif</p>
                        <h2 class="kartu-angka"><?= count(array_filter($barbers, fn($b) => $b['status'] === 'aktif')) ?></h2>
                    </div>
                </div>
                <div class="kartu">
                    <div class="kartu-icon"><ion-icon name="images-outline"></ion-icon></div>
                    <div class="kartu-info">
                        <p class="kartu-label">Foto Gallery</p>
                        <h2 class="kartu-angka">24</h2>
                    </div>
                </div>
            </div>

            <div class="section-title section-title--mt">
                <h1>Booking Terbaru</h1>
                <div class="line"></div>
            </div>

            <div class="tabel-wrapper">
                <table class="tabel">
                    <thead>
                        <tr>
                            <th>Nama</th><th>Layanan</th><th>Barber</th><th>Jam</th><th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-dashboard"></tbody>
                </table>
            </div>
        </div>
        <!-- END DASHBOARD -->


        <!-- HALAMAN: BOOKING -->
        <div class="page" id="page-booking">
            <div class="section-title">
                <h1>Booking</h1>
                <div class="line"></div>
            </div>
            <div class="filter-bar">
                <input type="text" class="input-cari" id="input-cari-booking" placeholder="Cari nama pelanggan...">
                <select class="select-filter" id="filter-status">
                    <option value="semua">Semua Status</option>
                    <option value="pending">Pending</option>
                    <option value="accepted">Accepted</option>
                    <option value="completed">Completed</option>
                    <option value="canceled">Canceled</option>
                </select>
            </div>
            <div class="tabel-wrapper">
                <table class="tabel" id="tabel-booking">
                    <thead>
                        <tr>
                            <th>No</th><th>Nama</th><th>Layanan</th><th>Barber</th>
                            <th>Tanggal</th><th>Jam</th><th>Status</th><th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-booking"></tbody>
                </table>
            </div>
        </div>
        <!-- END BOOKING -->


        <!-- HALAMAN: BARBER -->
        <div class="page" id="page-barber">
            <div class="section-title">
                <h1>Barber</h1>
                <div class="line"></div>
            </div>
            <button class="btn-tambah" onclick="tampilkanFormBarber()">
                <ion-icon name="add-outline"></ion-icon> Tambah Barber
            </button>
            <div class="form-card" id="form-barber" style="display:none;">
                <h3 class="form-title">Tambah Barber Baru</h3>
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" id="input-nama-barber" placeholder="Nama barber">
                </div>
                <div class="row-two">
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" id="input-email-barber" placeholder="email@crownbarber.com">
                    </div>
                    <div class="form-group">
                        <label>Instagram</label>
                        <input type="text" id="input-ig-barber" placeholder="@handle_ig">
                    </div>
                </div>
                <div class="form-group">
                    <label>Keahlian</label>
                    <input type="text" id="input-keahlian-barber" placeholder="Contoh: Modern Fade, Skin Fade...">
                </div>
                <div class="form-group">
                    <label>Deskripsi</label>
                    <textarea id="input-desc-barber" placeholder="Deskripsi singkat barber..."></textarea>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select id="input-status-barber">
                        <option value="aktif">Aktif</option>
                        <option value="nonaktif">Tidak Tersedia</option>
                    </select>
                </div>
                <div class="form-aksi">
                    <button class="btn-simpan" onclick="simpanBarber()">Simpan</button>
                    <button class="btn-batal" onclick="tutupFormBarber()">Batal</button>
                </div>
            </div>

            <!-- ── Daftar barber dari DB ── -->
            <div class="cont-pencukur" id="daftar-barber">
                <?php foreach ($barbers as $b): ?>
                <div class="cont-perorang"
                     id="barber-db-<?= $b['id'] ?>"
                     data-barber-id="<?= $b['id'] ?>">
                    <div class="img-pen">
                        <img src="<?= $b['foto'] ? htmlspecialchars('../' . $b['foto']) : '../assets/img/mount-batur.jpg' ?>"
                             alt="<?= htmlspecialchars($b['nama']) ?>">
                    </div>
                    <div class="p-pen">
                        <h2 class="nama-barber"><?= htmlspecialchars(strtoupper($b['nama'])) ?></h2>
                        <div class="contact-barber">
                            <?php if ($b['email']): ?>
                            <div class="row-contact">
                                <ion-icon name="mail-outline" class="icon-contact"></ion-icon>
                                <a href="mailto:<?= htmlspecialchars($b['email']) ?>"><?= htmlspecialchars($b['email']) ?></a>
                            </div>
                            <?php endif; ?>
                            <?php if ($b['instagram']): ?>
                            <div class="row-contact">
                                <ion-icon name="logo-instagram" class="icon-contact"></ion-icon>
                                <a href="#"><?= htmlspecialchars($b['instagram']) ?></a>
                            </div>
                            <?php endif; ?>
                        </div>
                        <p class="desc-barber"><?= htmlspecialchars($b['deskripsi'] ?? '') ?></p>
                        <div class="status-barber">
                            <span class="status <?= $b['status'] === 'aktif' ? 'accepted' : 'canceled' ?>">
                                <?= $b['status'] === 'aktif' ? 'Aktif' : 'Tidak Tersedia' ?>
                            </span>
                        </div>
                        <div class="barber-aksi">
                            <button class="btn-aksi btn-hapus"
                                    onclick="hapusBarber('barber-db-<?= $b['id'] ?>')">
                                <ion-icon name="trash-outline"></ion-icon> Hapus
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>

                <?php if (empty($barbers)): ?>
                <p style="color: var(--color-text-secondary); padding: 1rem 0;">
                    Belum ada barber. Tambahkan barber baru di atas.
                </p>
                <?php endif; ?>
            </div>
        </div>
        <!-- END BARBER -->


        <!-- HALAMAN: SERVICE -->
        <div class="page" id="page-service">
            <div class="section-title">
                <h1>Service</h1>
                <div class="line"></div>
            </div>
            <button class="btn-tambah" onclick="tampilkanFormService()">
                <ion-icon name="add-outline"></ion-icon> Tambah Layanan
            </button>
            <div class="form-card" id="form-service" style="display:none;">
                <h3 class="form-title">Tambah Layanan Baru</h3>
                <div class="row-two">
                    <div class="form-group">
                        <label>Nama Layanan</label>
                        <input type="text" id="input-nama-service" placeholder="Nama layanan">
                    </div>
                    <div class="form-group">
                        <label>Kategori</label>
                        <select id="input-kategori-service">
                            <option value="Dewasa">Dewasa</option>
                            <option value="Anak-anak">Anak-anak</option>
                        </select>
                    </div>
                </div>
                <div class="row-two">
                    <div class="form-group">
                        <label>Harga (Rp)</label>
                        <input type="number" id="input-harga-service" placeholder="35000">
                    </div>
                    <div class="form-group">
                        <label>Durasi (menit)</label>
                        <input type="number" id="input-durasi-service" placeholder="30">
                    </div>
                </div>
                <div class="form-aksi">
                    <button class="btn-simpan" onclick="simpanService()">Simpan</button>
                    <button class="btn-batal" onclick="tutupFormService()">Batal</button>
                </div>
            </div>

            <div class="tab-bar">
                <button class="tab-btn active" onclick="gantiTab(this,'tab-dewasa')">Dewasa</button>
                <button class="tab-btn" onclick="gantiTab(this,'tab-anak')">Anak-anak</button>
            </div>

            <!-- ── Tab Dewasa dari DB ── -->
            <div class="tab-content active" id="tab-dewasa">
                <div class="tabel-wrapper">
                    <table class="tabel">
                        <thead>
                            <tr><th>Nama Layanan</th><th>Harga</th><th>Durasi</th><th>Aksi</th></tr>
                        </thead>
                        <tbody id="tbody-dewasa">
                            <?php foreach ($svcDewasa as $item): ?>
                            <tr data-service-id="<?= $item['id'] ?>">
                                <td><?= htmlspecialchars($item['nama']) ?></td>
                                <td>Rp <?= number_format($item['harga'], 0, ',', '.') ?></td>
                                <td>± <?= (int)$item['durasi'] ?> menit</td>
                                <td>
                                    <button class="btn-aksi btn-hapus" onclick="hapusBaris(this)">Hapus</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ── Tab Anak-anak dari DB ── -->
            <div class="tab-content" id="tab-anak">
                <div class="tabel-wrapper">
                    <table class="tabel">
                        <thead>
                            <tr><th>Nama Layanan</th><th>Harga</th><th>Durasi</th><th>Aksi</th></tr>
                        </thead>
                        <tbody id="tbody-anak">
                            <?php foreach ($svcAnak as $item): ?>
                            <tr data-service-id="<?= $item['id'] ?>">
                                <td><?= htmlspecialchars($item['nama']) ?></td>
                                <td>Rp <?= number_format($item['harga'], 0, ',', '.') ?></td>
                                <td>± <?= (int)$item['durasi'] ?> menit</td>
                                <td>
                                    <button class="btn-aksi btn-hapus" onclick="hapusBaris(this)">Hapus</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!-- END SERVICE -->


        <!-- HALAMAN: GALLERY -->
        <div class="page" id="page-gallery">
            <div class="section-title">
                <h1>Manajemen Galeri</h1>
                <p class="section-subtitle-text">Kelola portofolio foto dan unggah media baru untuk website Crown Barbershop.</p>
            </div>

            <!-- PANEL 1: DRAG & DROP UPLOAD MEDIA BARU (INSPIRED BY USER IMAGE 1) -->
            <div class="panel-card" style="margin-bottom: 2.5rem;">
                <div class="panel-header">
                    <h3><ion-icon name="cloud-upload-sharp" style="color: var(--color-gold);"></ion-icon> Unggah Foto Baru</h3>
                    <span class="nav-badge">Drag & Drop File</span>
                </div>
                <div class="panel-body" style="padding: 2rem;">
                    <div class="upload-area-card" id="upload-area">
                        <!-- Stacked Card Silhouette Visual (Image 1 Style) -->
                        <div class="stacked-upload-icon">
                            <div class="card-stack-bg"></div>
                            <div class="card-stack-main">
                                <div class="plus-circle-icon">
                                    <ion-icon name="add-sharp"></ion-icon>
                                </div>
                            </div>
                        </div>
                        
                        <div class="upload-text-content">
                            <h4 class="upload-main-text">Tarik & Lepas Foto di Sini, atau <span style="color: var(--color-gold); text-decoration: underline;">Cari File Foto</span></h4>
                            <p class="upload-sub-text">Mendukung format JPG, PNG, WEBP, atau GIF — Maksimal 5MB per file</p>
                        </div>

                        <input type="file" id="input-foto" accept="image/*" multiple onchange="uploadFoto(this)">
                    </div>

                    <div id="upload-progress" style="display:none; margin-top: 1rem; text-align: center;">
                        <p style="font-size:0.9rem; color: var(--color-gold); font-weight: 600;">
                            <ion-icon name="sync-outline" style="animation: spin 1s linear infinite;"></ion-icon>
                            Mengupload foto ke galeri...
                        </p>
                    </div>
                </div>
            </div>

            <!-- PANEL 2: HISTORY / GAMBAR YANG SUDAH ADA DI GALERI -->
            <div class="panel-card">
                <div class="panel-header">
                    <h3><ion-icon name="images-sharp" style="color: var(--color-gold);"></ion-icon> History Galeri Foto (Terpasang)</h3>
                    <span style="font-size: 0.85rem; color: var(--text-muted);">Foto portofolio di website utama</span>
                </div>
                <div class="panel-body" style="padding: 1.8rem;">
                    <div class="gallery-admin-grid" id="gallery-admin">
                        <?php if (!empty($gallery)): ?>
                            <?php foreach ($gallery as $foto): 
                                $imgPath = formatImgUrl($foto['foto'] ?? null, '../assets/img/gallery-fade.jpg');
                            ?>
                            <div class="gallery-card-item" data-gallery-id="<?= $foto['id'] ?>">
                                <div class="gallery-img-wrap">
                                    <img src="<?= $imgPath ?>" alt="Gallery Foto">
                                    <div class="gallery-card-overlay">
                                        <button class="btn-delete-photo" onclick="hapusFoto(this)">
                                            <ion-icon name="trash-sharp"></ion-icon> Hapus Foto
                                        </button>
                                    </div>
                                </div>
                                <div class="gallery-card-footer">
                                    <span class="gallery-item-title"><?= htmlspecialchars($foto['judul'] ?? 'Portofolio Cut') ?></span>
                                    <span class="gallery-item-tag"><?= htmlspecialchars($foto['kategori'] ?? 'Haircut') ?></span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <!-- Fallback Sample Photos jika database gallery masih kosong -->
                            <div class="gallery-card-item" data-gallery-id="sample-1">
                                <div class="gallery-img-wrap">
                                    <img src="../assets/img/gallery-fade.jpg" alt="Textured Fade Cut">
                                    <div class="gallery-card-overlay">
                                        <button class="btn-delete-photo" onclick="this.closest('.gallery-card-item').remove()">
                                            <ion-icon name="trash-sharp"></ion-icon> Hapus Foto
                                        </button>
                                    </div>
                                </div>
                                <div class="gallery-card-footer">
                                    <span class="gallery-item-title">Textured Skin Fade</span>
                                    <span class="gallery-item-tag">Haircut</span>
                                </div>
                            </div>
                            <div class="gallery-card-item" data-gallery-id="sample-2">
                                <div class="gallery-img-wrap">
                                    <img src="../assets/img/hero-model.jpg" alt="Executive Cut">
                                    <div class="gallery-card-overlay">
                                        <button class="btn-delete-photo" onclick="this.closest('.gallery-card-item').remove()">
                                            <ion-icon name="trash-sharp"></ion-icon> Hapus Foto
                                        </button>
                                    </div>
                                </div>
                                <div class="gallery-card-footer">
                                    <span class="gallery-item-title">Executive Gentleman</span>
                                    <span class="gallery-item-tag">Styling</span>
                                </div>
                            </div>
                            <div class="gallery-card-item" data-gallery-id="sample-3">
                                <div class="gallery-img-wrap">
                                    <img src="../assets/img/service-beard.jpg" alt="Royal Beard Sculpt">
                                    <div class="gallery-card-overlay">
                                        <button class="btn-delete-photo" onclick="this.closest('.gallery-card-item').remove()">
                                            <ion-icon name="trash-sharp"></ion-icon> Hapus Foto
                                        </button>
                                    </div>
                                </div>
                                <div class="gallery-card-footer">
                                    <span class="gallery-item-title">Royal Beard Sculpt</span>
                                    <span class="gallery-item-tag">Beard</span>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <!-- END GALLERY -->


        <!-- HALAMAN: SETTINGS -->
        <div class="page" id="page-settings">
            <div class="section-title">
                <h1>Settings</h1>
                <div class="line"></div>
            </div>
            <div class="form-card">
                <h3 class="form-title">Informasi Barbershop</h3>
                <div class="form-group"><label>Nama Barbershop</label><input type="text" id="set-nama" value="Crown Barbershop"></div>
                <div class="form-group"><label>Tagline</label><input type="text" id="set-tagline" value="Look Sharp. Feel Royal. The Crown."></div>
                <div class="form-group"><label>Alamat</label><textarea id="set-alamat">Jl. Melati Indah No. 27, Kel. Sukamurni, Kec. Cendana&#10;Kota Seruni 45123 – Indonesia</textarea></div>
                <div class="row-two">
                    <div class="form-group"><label>Nomor Telepon</label><input type="text" id="set-telp" value="(021) 8890 1122"></div>
                    <div class="form-group"><label>Email</label><input type="email" id="set-email" value="info@crownbarber.com"></div>
                </div>
            </div>
            <div class="form-card form-card--mt">
                <h3 class="form-title">Konten About Us</h3>
                <div class="form-group"><label>Paragraf 1</label><textarea id="set-about1">Di Crown Barbershop, kami percaya bahwa rambut Anda adalah mahkota yang menentukan karakter Anda.</textarea></div>
                <div class="form-group"><label>Paragraf 2</label><textarea id="set-about2">Datanglah, nikmati pelayanan terbaik kami, dan biarkan barber ahli kami memberikan transformasi yang membuat Anda keluar dengan kepercayaan diri maksimal.</textarea></div>
                <div class="form-group"><label>Paragraf 3 (Teks miring)</label><textarea id="set-about3">Mulai dari classic pompadour, modern skin fade, hingga ritual cukur jenggot tradisional — di Crown, setiap pelanggan dilayani layaknya raja.</textarea></div>
            </div>
            <div class="form-card form-card--mt">
                <h3 class="form-title">Jam Operasional</h3>
                <div class="jam-edit-list">
                    <div class="jam-edit-row"><span class="jam-hari">Senin</span><input type="text" class="jam-input" value="10.00 - 20.00"></div>
                    <div class="jam-edit-row"><span class="jam-hari">Selasa</span><input type="text" class="jam-input" value="11.00 - 21.00"></div>
                    <div class="jam-edit-row"><span class="jam-hari">Rabu</span><input type="text" class="jam-input" value="09.00 - 20.00"></div>
                    <div class="jam-edit-row"><span class="jam-hari">Kamis</span><input type="text" class="jam-input" value="09.00 - 20.00"></div>
                    <div class="jam-edit-row"><span class="jam-hari jam-hari--libur">Jum'at</span><input type="text" class="jam-input jam-input--libur" value="Libur"></div>
                    <div class="jam-edit-row"><span class="jam-hari">Sabtu</span><input type="text" class="jam-input" value="12.00 - 22.00"></div>
                    <div class="jam-edit-row"><span class="jam-hari">Minggu</span><input type="text" class="jam-input" value="08.00 - 21.00"></div>
                </div>
            </div>
            <div class="form-card form-card--mt">
                <h3 class="form-title">Media Sosial</h3>
                <div class="form-group"><label>Instagram</label><input type="text" id="set-ig" value="@crownbarbershop"></div>
                <div class="form-group"><label>Facebook</label><input type="text" id="set-fb" value="Crown Barbershop Official"></div>
                <div class="form-group"><label>TikTok</label><input type="text" id="set-tiktok" value="@crown.barber"></div>
            </div>
            <button class="btn-simpan-settings" onclick="simpanSettings()">
                <ion-icon name="save-outline"></ion-icon>
                Simpan Semua Perubahan
            </button>
        </div>
        <!-- END SETTINGS -->

    </div>
    <!-- END MAIN CONTENT -->

</div>

<script src="../assets/js/admin.js"></script>

<script>
// ============================================================
//  BOOKING — Load dari DB
// ============================================================

const STATUS_LABEL = {
    pending   : 'Pending',
    accepted  : 'Accepted',
    completed : 'Completed',
    canceled  : 'Canceled',
};

// Tombol aksi sesuai status
function tombolAksi(id, status) {
    if (status === 'pending') {
        return `<button class="btn-aksi btn-edit"  onclick="ubahStatusDB(${id},'accepted')">Terima</button>
                <button class="btn-aksi btn-hapus" onclick="ubahStatusDB(${id},'canceled')">Batal</button>`;
    }
    if (status === 'accepted') {
        return `<button class="btn-aksi btn-edit"  onclick="ubahStatusDB(${id},'completed')">Selesai</button>
                <button class="btn-aksi btn-hapus" onclick="ubahStatusDB(${id},'canceled')">Batal</button>`;
    }
    if (status === 'completed') {
        return `<button class="btn-aksi btn-hapus" onclick="ubahStatusDB(${id},'canceled')">Batal</button>`;
    }
    if (status === 'canceled') {
        return `<button class="btn-aksi btn-edit"  onclick="ubahStatusDB(${id},'accepted')">Aktifkan</button>`;
    }
    return '';
}

// Format tanggal: 2025-03-17 → 17 Mar 2025
function formatTanggal(tgl) {
    const bulan = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
    const [y, m, d] = tgl.split('-');
    return `${parseInt(d)} ${bulan[parseInt(m) - 1]} ${y}`;
}

// ── Render tabel booking ─────────────────────────────────────
async function loadBookings() {
    try {
        const res  = await fetch('../api/bookings_list.php');
        const data = await res.json();

        if (!data.success) return;

        const tbody     = document.getElementById('tbody-booking');
        const tbodyDash = document.getElementById('tbody-dashboard');

        if (!data.data.length) {
            const emptyRow = `<tr><td colspan="8" style="text-align:center;color:#888;padding:20px;">
                Belum ada booking.</td></tr>`;
            if (tbody)     tbody.innerHTML     = emptyRow;
            if (tbodyDash) tbodyDash.innerHTML = emptyRow;

            // Update badge & kartu dashboard
            updateDashboardKartu(0, 0);
            return;
        }

        let no      = 1;
        let pending = 0;
        let rowsBooking  = '';
        let rowsDashboard = '';

        data.data.forEach(b => {
            if (b.status === 'pending') pending++;

            const statusHtml = `<span class="status ${b.status}">${STATUS_LABEL[b.status]}</span>`;
            const tgl        = formatTanggal(b.tanggal);
            const jam        = b.jam.substring(0, 5); // HH:MM

            // Baris untuk halaman Booking (lengkap)
            rowsBooking += `
                <tr data-status="${b.status}" data-id="${b.id}">
                    <td>${String(no).padStart(3,'0')}</td>
                    <td>${b.nama_user}</td>
                    <td>${b.nama_service}</td>
                    <td>${b.nama_barber}</td>
                    <td>${tgl}</td>
                    <td>${jam}</td>
                    <td>${statusHtml}</td>
                    <td>${tombolAksi(b.id, b.status)}</td>
                </tr>
            `;

            // 4 baris teratas untuk dashboard
            if (no <= 4) {
                rowsDashboard += `
                    <tr>
                        <td>${b.nama_user}</td>
                        <td>${b.nama_service}</td>
                        <td>${b.nama_barber}</td>
                        <td>${jam}</td>
                        <td>${statusHtml}</td>
                    </tr>
                `;
            }

            no++;
        });

        if (tbody)     tbody.innerHTML     = rowsBooking;
        if (tbodyDash) tbodyDash.innerHTML = rowsDashboard;

        // Update badge & kartu dashboard
        updateDashboardKartu(data.data.length, pending);

    } catch (err) {
        console.error('Gagal load bookings:', err);
    }
}

// ── Update angka di kartu & badge sidebar ───────────────────
function updateDashboardKartu(total, pending) {
    // Badge di sidebar
    const badge = document.getElementById('badge-booking');
    if (badge) badge.textContent = pending;

    // Kartu "Booking Hari Ini"
    const kartuTotal = document.querySelector('.kartu:first-child .kartu-angka');
    if (kartuTotal) kartuTotal.textContent = total;

    // Kartu "Booking Pending"
    const kartuPending = document.querySelector('.kartu--yellow .kartu-angka');
    if (kartuPending) kartuPending.textContent = pending;
}

// ── Ubah status booking ke DB ────────────────────────────────
async function ubahStatusDB(bookingId, statusBaru) {
    try {
        const res  = await fetch('../api/booking_status.php', {
            method : 'POST',
            headers: { 'Content-Type': 'application/json' },
            body   : JSON.stringify({ booking_id: bookingId, status: statusBaru })
        });
        const data = await res.json();

        if (data.success) {
            // Reload tabel setelah update berhasil
            loadBookings();
        } else {
            alert('Gagal update status: ' + data.message);
        }
    } catch (err) {
        alert('Terjadi kesalahan koneksi.');
    }
}

// ── Filter tabel booking (override fungsi di admin.js) ───────
// Perlu di-override karena filterBooking() asli pakai querySelectorAll
// yang sudah tidak relevan setelah tbody diisi dinamis
function filterBooking() {
    const keyword       = document.getElementById('input-cari-booking')?.value.toLowerCase() || '';
    const statusDipilih = document.getElementById('filter-status')?.value || 'semua';
    const rows          = document.querySelectorAll('#tbody-booking tr[data-status]');

    rows.forEach(row => {
        const nama      = row.querySelector('td:nth-child(2)')?.textContent.toLowerCase() || '';
        const status    = row.getAttribute('data-status');
        const cocoNama  = nama.includes(keyword);
        const cocoStatus= statusDipilih === 'semua' || status === statusDipilih;
        row.style.display = (cocoNama && cocoStatus) ? '' : 'none';
    });
}

// ── Init ─────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', loadBookings);
</script>

</body>
</html>
