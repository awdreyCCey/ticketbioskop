<?php
require_once dirname(__DIR__) . '/config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderId = filter_var($_POST['order_id'] ?? null, FILTER_VALIDATE_INT);
    if (!$orderId) {
        http_response_code(400);
        exit('Pesanan tidak valid.');
    }

    $stmt = mysqli_prepare($conn, "UPDATE orders SET status = 'disetujui', kode_transaksi = ? WHERE id = ? AND status = 'menunggu'");
    $attempt = 0;
    do {
        $kode = 'TKT' . strtoupper(bin2hex(random_bytes(4)));
        mysqli_stmt_bind_param($stmt, 'si', $kode, $orderId);
        try {
            mysqli_stmt_execute($stmt);
            $updated = mysqli_stmt_affected_rows($stmt);
            break;
        } catch (mysqli_sql_exception $exception) {
            $updated = 0;
            $attempt++;
        }
    } while ($attempt < 5);

    header('Location: admin.php');
    exit;
}

$query = mysqli_query($conn, 'SELECT id, nama, email, kursi, total_harga, bukti_pembayaran, status, kode_transaksi FROM orders ORDER BY id DESC');
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard Admin</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
<h1>Dashboard Admin</h1>
<table border="1">
<tr>
<th>Nama</th>
<th>Email</th>
<th>Kursi</th>
<th>Total</th>
<th>Bukti</th>
<th>Status</th>
<th>Kode</th>
<th>Aksi</th>
</tr>
<?php while ($order = mysqli_fetch_assoc($query)) : ?>
<tr>
<td><?php echo htmlspecialchars($order['nama'], ENT_QUOTES, 'UTF-8'); ?></td>
<td><?php echo htmlspecialchars($order['email'], ENT_QUOTES, 'UTF-8'); ?></td>
<td><?php echo htmlspecialchars($order['kursi'], ENT_QUOTES, 'UTF-8'); ?></td>
<td>Rp <?php echo number_format((float) $order['total_harga'], 0, ',', '.'); ?></td>
<td><img src="../uploads/<?php echo rawurlencode(basename($order['bukti_pembayaran'])); ?>" width="100" alt="Bukti pembayaran"></td>
<td><?php echo htmlspecialchars($order['status'], ENT_QUOTES, 'UTF-8'); ?></td>
<td><?php echo htmlspecialchars((string) $order['kode_transaksi'], ENT_QUOTES, 'UTF-8'); ?></td>
<td>
<?php if ($order['status'] === 'menunggu') : ?>
<form method="post" style="display:inline">
    <input type="hidden" name="order_id" value="<?php echo (int) $order['id']; ?>">
    <button type="submit">Setujui</button>
</form>
<?php endif; ?>
</td>
</tr>
<?php endwhile; ?>
</table>
</body>
</html>