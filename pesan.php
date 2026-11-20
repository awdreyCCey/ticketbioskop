<?php
require_once __DIR__ . '/config/koneksi.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    http_response_code(400);
    exit('Film tidak valid.');
}

$stmt = mysqli_prepare($conn, 'SELECT id, nama_film, harga FROM films WHERE id = ?');
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$film = mysqli_fetch_assoc($result);

if (!$film) {
    http_response_code(404);
    exit('Film tidak ditemukan.');
}

$kursi = mysqli_query($conn, "SELECT nomor_kursi, status FROM seats ORDER BY nomor_kursi");
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pesan Tiket</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<h2>Pesan Tiket</h2>
<form action="upload_bukti.php" method="post" enctype="multipart/form-data">
    <input type="hidden" name="film_id" value="<?php echo (int) $film['id']; ?>">

    <label>Nama<br>
        <input type="text" name="nama" maxlength="100" required>
    </label>
    <br><br>

    <label>Email<br>
        <input type="email" name="email" maxlength="150" required>
    </label>
    <br><br>

    <p>Film: <?php echo htmlspecialchars($film['nama_film'], ENT_QUOTES, 'UTF-8'); ?></p>

    <fieldset>
        <legend>Pilih Kursi</legend>
        <?php while ($seat = mysqli_fetch_assoc($kursi)) : ?>
            <?php if ($seat['status'] === 'terisi') : ?>
                <label>
                    <input type="checkbox" disabled>
                    <?php echo htmlspecialchars($seat['nomor_kursi'], ENT_QUOTES, 'UTF-8'); ?> (terisi)
                </label>
            <?php else : ?>
                <label>
                    <input type="checkbox" name="kursi[]" value="<?php echo htmlspecialchars($seat['nomor_kursi'], ENT_QUOTES, 'UTF-8'); ?>">
                    <?php echo htmlspecialchars($seat['nomor_kursi'], ENT_QUOTES, 'UTF-8'); ?>
                </label>
            <?php endif; ?>
        <?php endwhile; ?>
    </fieldset>

    <br>
    <label>Upload Bukti Pembayaran (JPG/PNG)<br>
        <input type="file" name="bukti" accept=".jpg,.jpeg,.png,image/jpeg,image/png" required>
    </label>
    <br><br>
    <button type="submit">Kirim Pesanan</button>
</form>
</body>
</html>