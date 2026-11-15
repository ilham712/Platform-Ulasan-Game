<?php
// reviews/edit.php - Mengubah ulasan (UPDATE)

require_once __DIR__ . '/../includes/auth_check.php';

$user_id = $_SESSION['user_id'];
$id      = (int) ($_GET['id'] ?? 0);

// Untuk Ambil ulasan yang mau diedit.
//    "AND user_id = ?" memastikan user hanya bisa mengedit ulasan miliknya sendiri.
$stmt = $koneksi->prepare('SELECT * FROM reviews WHERE id = ? AND user_id = ?');
$stmt->bind_param('ii', $id, $user_id);
$stmt->execute();
$ulasan = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$ulasan) {
    header('Location: ' . BASE_URL . 'dashboard.php?pesan=tidak_ditemukan');
    exit;
}

// Isi form dengan data lama
$error    = '';
$judul    = $ulasan['judul_game'];
$genre    = $ulasan['genre'];
$platform = $ulasan['platform'];
$rating   = (int) $ulasan['rating'];
$isi      = $ulasan['isi_ulasan'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Ambil data baru dari form
    $judul    = trim($_POST['judul_game'] ?? '');
    $genre    = trim($_POST['genre'] ?? '');
    $platform = trim($_POST['platform'] ?? '');
    $rating   = (int) ($_POST['rating'] ?? 0);
    $isi      = trim($_POST['isi_ulasan'] ?? '');

    // Validasi
    if ($judul === '' || $genre === '' || $platform === '' || $isi === '') {
        $error = 'Semua kolom (kecuali gambar) harus diisi.';
    } elseif ($rating < 1 || $rating > 5) {
        $error = 'Pilih rating antara 1 sampai 5.';
    } elseif (strlen($isi) < 20) {
        $error = 'Isi ulasan minimal 20 karakter.';
    }

    // Gambar: kalau tidak upload yang baru, tetap pakai gambar lama
    $nama_gambar = $ulasan['gambar'];

     // Kalau kotak "Hapus gambar" dicentang, kosongkan nama gambarnya
    if (isset($_POST['hapus_gambar'])) {
        $nama_gambar = null;
    }

    if ($error === '' && $_FILES['gambar']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file     = $_FILES['gambar'];
        $ekstensi = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $error = 'Gambar gagal diunggah. Coba lagi.';
        } elseif (!in_array($ekstensi, ['jpg', 'jpeg', 'png', 'webp'])) {
            $error = 'Gambar harus berformat JPG, PNG, atau WebP.';
        } elseif (getimagesize($file['tmp_name']) === false) {
            $error = 'File yang diunggah bukan gambar.';
        } elseif ($file['size'] > 2 * 1024 * 1024) {
            $error = 'Ukuran gambar maksimal 2 MB.';
        } else {
            $nama_gambar = uniqid() . '.' . $ekstensi;
            move_uploaded_file($file['tmp_name'], __DIR__ . '/../assets/uploads/' . $nama_gambar);
        }
    }

    // Simpan perubahan
    if ($error === '') {
        $stmt = $koneksi->prepare(
            'UPDATE reviews
             SET judul_game = ?, genre = ?, platform = ?, rating = ?, isi_ulasan = ?, gambar = ?
             WHERE id = ? AND user_id = ?'
        );
        $stmt->bind_param('sssissii', $judul, $genre, $platform, $rating, $isi, $nama_gambar, $id, $user_id);
        $stmt->execute();
        $stmt->close();

        // Kalau gambar diganti, hapus file gambar yang lama dari folder
        if ($ulasan['gambar'] && $nama_gambar !== $ulasan['gambar']) {
            @unlink(__DIR__ . '/../assets/uploads/' . $ulasan['gambar']);
        }

        header('Location: ' . BASE_URL . 'reviews/detail.php?id=' . $id . '&pesan=edit');
        exit;
    }
}

$page_title = 'Edit Ulasan';
include __DIR__ . '/../includes/header.php';
?>

<main class="container container-form">
    <form class="form-card" method="POST" action="" enctype="multipart/form-data">
        <h1>Edit ulasan</h1>

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
                       value="<?= htmlspecialchars($genre) ?>" required>
            </div>
            <div>
                <label for="platform">Platform</label>
                <input type="text" id="platform" name="platform" maxlength="50"
                       value="<?= htmlspecialchars($platform) ?>" required>
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

        <label for="gambar">Ganti gambar sampul (opsional)</label>
        <?php if ($ulasan['gambar']): ?>
            <img class="preview" src="<?= BASE_URL ?>assets/uploads/<?= htmlspecialchars($ulasan['gambar']) ?>"
                 alt="Sampul saat ini">
             <label class="check">
                <input type="checkbox" name="hapus_gambar" value="1">
                Hapus gambar ini
            </label>
        <?php endif; ?>
        <input type="file" id="gambar" name="gambar" accept="image/*">
        <p class="hint">Kosongkan kalau tidak ingin mengganti gambar.</p>

        <div class="form-actions">
            <button type="submit" class="btn">Simpan perubahan</button>
            <a class="btn btn-ghost" href="<?= BASE_URL ?>reviews/detail.php?id=<?= $id ?>">Batal</a>
        </div>
    </form>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
