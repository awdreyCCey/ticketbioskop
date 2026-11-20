<?php
require_once __DIR__ . '/config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Metode permintaan tidak valid.');
}

$nama = trim((string) ($_POST['nama'] ?? ''));
$email = trim((string) ($_POST['email'] ?? ''));
$film_id = filter_var($_POST['film_id'] ?? null, FILTER_VALIDATE_INT);

if ($nama === '' || $email === '' || !$film_id) {
    http_response_code(400);
    exit('Data wajib belum lengkap.');
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    exit('Alamat email tidak valid.');
}
if (!isset($_POST['kursi']) || !is_array($_POST['kursi']) || count($_POST['kursi']) === 0) {
    http_response_code(400);
    exit('Silakan pilih minimal satu kursi.');
}

$kursiArray = array_values(array_unique(array_map('trim', $_POST['kursi'])));
if (count($kursiArray) > 10) {
    http_response_code(400);
    exit('Maksimal 10 kursi per pesanan.');
}

if (!isset($_FILES['bukti']) || $_FILES['bukti']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    exit('Bukti pembayaran gagal diunggah.');
}

$allowedTypes = [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
];
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mimeType = $finfo->file($_FILES['bukti']['tmp_name']);
if (!isset($allowedTypes[$mimeType])) {
    http_response_code(400);
    exit('File harus berformat JPG atau PNG.');
}
if ($_FILES['bukti']['size'] > 5 * 1024 * 1024) {
    http_response_code(400);
    exit('Ukuran file maksimal 5 MB.');
}

$stmt = mysqli_prepare($conn, 'SELECT harga FROM films WHERE id = ?');
mysqli_stmt_bind_param($stmt, 'i', $film_id);
mysqli_stmt_execute($stmt);
$filmResult = mysqli_stmt_get_result($stmt);
$film = mysqli_fetch_assoc($filmResult);
if (!$film) {
    http_response_code(404);
    exit('Film tidak ditemukan.');
}

$kursi = implode(',', $kursiArray);
$total = (float) $film['harga'] * count($kursiArray);
$uploadDir = __DIR__ . '/uploads';
if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
    http_response_code(500);
    exit('Folder upload tidak dapat dibuat.');
}

$fileName = bin2hex(random_bytes(16)) . '.' . $allowedTypes[$mimeType];
$filePath = $uploadDir . DIRECTORY_SEPARATOR . $fileName;

mysqli_begin_transaction($conn);
try {
    foreach ($kursiArray as $seatNumber) {
        $stmt = mysqli_prepare($conn, "SELECT nomor_kursi FROM seats WHERE nomor_kursi = ? AND status = 'tersedia' FOR UPDATE");
        mysqli_stmt_bind_param($stmt, 's', $seatNumber);
        mysqli_stmt_execute($stmt);
        if (mysqli_num_rows(mysqli_stmt_get_result($stmt)) !== 1) {
            throw new RuntimeException('Kursi ' . $seatNumber . ' sudah tidak tersedia.');
        }
    }

    $stmt = mysqli_prepare($conn, 'INSERT INTO orders (nama, email, film_id, kursi, total_harga, bukti_pembayaran, status) VALUES (?, ?, ?, ?, ?, ?, \'menunggu\')');
    mysqli_stmt_bind_param($stmt, 'ssisds', $nama, $email, $film_id, $kursi, $total, $fileName);
    mysqli_stmt_execute($stmt);

    foreach ($kursiArray as $seatNumber) {
        $stmt = mysqli_prepare($conn, "UPDATE seats SET status = 'terisi' WHERE nomor_kursi = ?");
        mysqli_stmt_bind_param($stmt, 's', $seatNumber);
        mysqli_stmt_execute($stmt);
    }

    if (!move_uploaded_file($_FILES['bukti']['tmp_name'], $filePath)) {
        throw new RuntimeException('Bukti pembayaran gagal disimpan.');
    }

    mysqli_commit($conn);
} catch (Throwable $exception) {
    mysqli_rollback($conn);
    if (is_file($filePath)) {
        unlink($filePath);
    }
    error_log('Ticket order failed: ' . $exception->getMessage());
    http_response_code($exception instanceof RuntimeException ? 409 : 500);
    exit($exception->getMessage());
}

header('Location: tiket.php?pesan=1');
exit;