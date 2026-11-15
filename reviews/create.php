<?php
// reviews/create.php - Menambah ulasan baru (CREATE)

require_once __DIR__ . '/../includes/auth_check.php';

$error    = '';
$judul    = '';
$genre    = '';
$platform = '';
$rating   = '';
$isi      = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Untuk Ambil data dari form
    $judul    = trim($_POST['judul_game'] ?? '');
    $genre    = trim($_POST['genre'] ?? '');
    $platform = trim($_POST['platform'] ?? '');
    $rating   = (int) ($_POST['rating'] ?? 0);
    $isi      = trim($_POST['isi_ulasan'] ?? '');

    // Untuk Validasi
    if ($judul === '' || $genre === '' || $platform === '' || $isi === '') {
        $error = 'Semua kolom (kecuali gambar) harus diisi.';
    } elseif ($rating < 1 || $rating > 5) {
        $error = 'Pilih rating antara 1 sampai 5.';
    } elseif (strlen($isi) < 20) {
        $error = 'Isi ulasan minimal 20 karakter.';
    }

    // Untuk Upload gambar (boleh dikosongkan)
    $nama_gambar = null;

    if ($error === '' && $_FILES['gambar']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file     = $_FILES['gambar'];
        $ekstensi = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $error = 'Gambar gagal diunggah. Coba lagi.';
        } elseif (!in_array($ekstensi, ['jpg', 'jpeg', 'png', 'webp'])) {
            $error = 'Gambar harus berformat JPG, PNG, atau WebP.';
        } elseif (getimagesize($file['tmp_name']) === false) {
            $error = 'File yang diunggah bukan gambar.'; // cek isi file, bukan hanya namanya
        } elseif ($file['size'] > 2 * 1024 * 1024) {
            $error = 'Ukuran gambar maksimal 2 MB.';
        } else {
            // Beri nama acak supaya tidak bentrok dengan gambar lain
            $nama_gambar = uniqid() . '.' . $ekstensi;
            move_uploaded_file($file['tmp_name'], __DIR__ . '/../assets/uploads/' . $nama_gambar);
        }
    }

    // Kalau semua aman, simpan ke database
    if ($error === '') {
        $user_id = $_SESSION['user_id'];

        $stmt = $koneksi->prepare(
            'INSERT INTO reviews (user_id, judul_game, genre, platform, rating, isi_ulasan, gambar)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->bind_param('isssiss', $user_id, $judul, $genre, $platform, $rating, $isi, $nama_gambar);
        $stmt->execute();
        $id_baru = $stmt->insert_id; // id ulasan yang baru dibuat
        $stmt->close();

        header('Location: ' . BASE_URL . 'reviews/detail.php?id=' . $id_baru . '&pesan=tambah');
        exit;
    }
}

$page_title = 'Tulis Ulasan';
include __DIR__ . '/../includes/header.php';
?>

<main class="container container-form">
    <!-- enctype wajib ada supaya form bisa mengirim file -->
    <form class="form-card" method="POST" action="" enctype="multipart/form-data">
        <h1>Tulis ulasan baru</h1>

        <?php if ($error): ?>
            <p class="alert alert-error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <label for="judul_game">Judul game</label>
        <input type="text" id="judul_game" name="judul_game" maxlength="150"
               value="<?= htmlspecialchars($judul) ?>" required>

        <div class="row-2">
            <div>
                <label for="genre">Genre</label>
                <input type="text" id="genre" name="genre" maxlength="50"
                       placeholder="contoh: RPG" value="<?= htmlspecialchars($genre) ?>" required>
            </div>
            <div>
                <label for="platform">Platform</label>
                <input type="text" id="platform" name="platform" maxlength="50"
                       placeholder="contoh: PC" value="<?= htmlspecialchars($platform) ?>" required>
            </div>
        </div>

        <label for="rating">Rating</label>
        <select id="rating" name="rating" required>
            <option value="">-- Pilih rating --</option>
            <?php for ($i = 5; $i >= 1; $i--): ?>
                <option value="<?= $i ?>" <?= $rating === $i ? 'selected' : '' ?>>
                    <?= str_repeat('★', $i) ?> (<?= $i ?>)
                </option>
            <?php endfor; ?>
        </select>

        <label for="isi_ulasan">Isi ulasan</label>
        <textarea id="isi_ulasan" name="isi_ulasan" rows="7" required><?= htmlspecialchars($isi) ?></textarea>

        <label for="gambar">Gambar sampul (opsional)</label>
        <input type="file" id="gambar" name="gambar" accept="image/*">
        <p class="hint">JPG, PNG, atau WebP, maksimal 2 MB.</p>

        <div class="form-actions">
            <button type="submit" class="btn">Simpan</button>
            <a class="btn btn-ghost" href="<?= BASE_URL ?>dashboard.php">Batal</a>
        </div>
    </form>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
