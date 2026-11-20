<?php
require_once __DIR__ . '/config/koneksi.php';

$query = mysqli_query($conn, 'SELECT id, nama_film, jadwal, harga FROM films ORDER BY jadwal');
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bioskop Online</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<h1>DAFTAR FILM BIOSKOP</h1>
<div class="container">
<?php while ($film = mysqli_fetch_assoc($query)) : ?>
<div class="film">
    <h2><?php echo htmlspecialchars($film['nama_film'], ENT_QUOTES, 'UTF-8'); ?></h2>
    <p>Jadwal: <?php echo htmlspecialchars($film['jadwal'], ENT_QUOTES, 'UTF-8'); ?></p>
    <p>Harga: Rp <?php echo number_format((float) $film['harga'], 0, ',', '.'); ?></p>
    <a href="pesan.php?id=<?php echo urlencode((string) $film['id']); ?>">
        <button type="button">Pesan Tiket</button>
    </a>
</div>
<?php endwhile; ?>
</div>
</body>
</html>