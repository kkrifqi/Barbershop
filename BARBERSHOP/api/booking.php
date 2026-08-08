<?php
// ============================================================
//  api/booking.php
//
//  GET  ?data=services  → ambil semua layanan dari DB
//  GET  ?data=barbers   → ambil semua barber aktif dari DB
//  POST action=submit   → simpan booking baru ke DB
// ============================================================

require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/cek_session.php';

header('Content-Type: application/json');

// ── GET ──────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $data = $_GET['data'] ?? '';

    // Layanan
    if ($data === 'services') {
        $stmt = $pdo->query("SELECT * FROM services ORDER BY kategori, harga ASC");
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
        exit;
    }

    // Barber aktif
    if ($data === 'barbers') {
        $stmt = $pdo->query("SELECT id, nama, foto, instagram FROM barbers WHERE status = 'aktif' ORDER BY id ASC");
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Parameter tidak dikenali.']);
    exit;
}


// ── POST ─────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Harus login
    if (!$sudahLogin) {
        echo json_encode([
            'success'  => false, 
            'message'  => 'Silakan login terlebih dahulu untuk melakukan booking.', 
            'redirect' => '../login-register/login.html'
        ]);
        exit;
    }

    $body   = json_decode(file_get_contents('php://input'), true) ?? [];
    $action = $body['action'] ?? '';

    if ($action !== 'submit') {
        echo json_encode(['success' => false, 'message' => 'Action tidak dikenali.']);
        exit;
    }

    // Ambil & validasi input
    $service_id = (int) ($body['service_id'] ?? 0);
    $barber_id  = (int) ($body['barber_id']  ?? 0);
    $tanggal    = trim($body['tanggal'] ?? '');
    $jam        = trim($body['jam']     ?? '');

    // 1. Validasi Layanan
    if (!$service_id) {
        echo json_encode(['success' => false, 'message' => 'Silakan pilih jenis layanan terlebih dahulu.']);
        exit;
    }

    // 2. Validasi Tanggal & Jam
    if (!$tanggal || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) {
        echo json_encode(['success' => false, 'message' => 'Tanggal booking tidak valid atau belum dipilih.']);
        exit;
    }

    if (!$jam || !preg_match('/^\d{2}:\d{2}$/', $jam)) {
        echo json_encode(['success' => false, 'message' => 'Jam booking tidak valid atau belum dipilih.']);
        exit;
    }

    // 3. Penanganan Opsi "Siapa saja" (barber_id === 0)
    if ($barber_id === 0) {
        // Cari barber aktif yang belum ada jadwal di jam & tanggal tersebut
        $stmtAvail = $pdo->prepare(
            "SELECT id FROM barbers 
             WHERE status = 'aktif' 
             AND id NOT IN (
                 SELECT barber_id FROM bookings 
                 WHERE tanggal = ? AND jam = ? AND status NOT IN ('canceled')
             )
             ORDER BY RAND() LIMIT 1"
        );
        $stmtAvail->execute([$tanggal, $jam]);
        $autoBarberId = $stmtAvail->fetchColumn();

        if ($autoBarberId) {
            $barber_id = (int) $autoBarberId;
        } else {
            // Jika semua barber penuh di slot itu, ambil barber aktif pertama
            $firstActive = $pdo->query("SELECT id FROM barbers WHERE status = 'aktif' ORDER BY id ASC LIMIT 1")->fetchColumn();
            if ($firstActive) {
                $barber_id = (int) $firstActive;
            } else {
                echo json_encode(['success' => false, 'message' => 'Tidak ada barber aktif yang tersedia saat ini.']);
                exit;
            }
        }
    }

    // Cek apakah slot sudah terisi untuk barber tersebut
    $cek = $pdo->prepare(
        "SELECT id FROM bookings
         WHERE barber_id = ? AND tanggal = ? AND jam = ?
         AND status NOT IN ('canceled')"
    );
    $cek->execute([$barber_id, $tanggal, $jam]);
    if ($cek->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Slot waktu dengan barber ini sudah terisi. Silakan pilih jam lain.']);
        exit;
    }

    // Simpan booking ke database
    $stmt = $pdo->prepare(
        "INSERT INTO bookings (user_id, barber_id, service_id, tanggal, jam, status)
         VALUES (?, ?, ?, ?, ?, 'pending')"
    );
    $stmt->execute([$userID, $barber_id, $service_id, $tanggal, $jam]);
    $bookingId = $pdo->lastInsertId();

    echo json_encode([
        'success'    => true,
        'message'    => 'Booking berhasil!',
        'booking_id' => $bookingId,
    ]);
    exit;
}

http_response_code(405);
echo json_encode(['success' => false, 'message' => 'Method tidak diizinkan.']);
