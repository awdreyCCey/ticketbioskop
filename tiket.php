<?php
require_once __DIR__ . '/config/koneksi.php';

if (isset($_GET['pesan'])) {
    $message = 'Pesanan berhasil dikirim. Menunggu konfirmasi admin.';
} else {
    $kode = trim((string) ($_GET['kode'] ?? ''));
    $data = null;
    if ($kode !== '') {
        $stmt = mysqli_prepare($conn, 'SELECT nama, kursi, status, kode_transaksi FROM orders WHERE kode_transaksi = ?');
        mysqli_stmt_bind_param($stmt, 's', $kode);
        mysqli_stmt_execute($stmt);
        $data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    }

    if (!$data) {
        http_response_code(404);
        exit('Tiket tidak ditemukan.');
    }
    $message = null;
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tiket Bioskop</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php if ($message !== null) : ?>
    <h1>Pesanan Diterima</h1>
    <p><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></p>
<?php else : ?>
    <h1>Tiket Bioskop</h1>
    <p>Kode Transaksi: <?php echo htmlspecialchars($data['kode_transaksi'], ENT_QUOTES, 'UTF-8'); ?></p>
    <p>Nama: <?php echo htmlspecialchars($data['nama'], ENT_QUOTES, 'UTF-8'); ?></p>
    <p>Kursi: <?php echo htmlspecialchars($data['kursi'], ENT_QUOTES, 'UTF-8'); ?></p>
    <p>Status: <?php echo htmlspecialchars($data['status'], ENT_QUOTES, 'UTF-8'); ?></p>
<?php endif; ?>
</body>
</html>