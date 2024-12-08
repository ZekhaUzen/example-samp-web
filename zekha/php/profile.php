<?php
// Koneksi ke database
include('../mysql/db_connection.php');

// Cek apakah user sudah login
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username']; // Ambil username dari sesi login

// Ambil data user dari tabel `accounts` untuk mendapatkan ucp name (Username)
$sql_account = "SELECT * FROM accounts WHERE Username = '$username'";
$result_account = mysqli_query($conn, $sql_account);

// Cek apakah query berhasil dan data ditemukan
if (mysqli_num_rows($result_account) > 0) {
    $account = mysqli_fetch_assoc($result_account);
} else {
    // Jika tidak ada data ditemukan, beri pesan error
    echo "Akun tidak ditemukan.";
    exit();
}

// Ambil data karakter (Username dan Masters) dari tabel `users` berdasarkan username
$sql_user = "SELECT * FROM users WHERE Masters = '{$account['Username']}'";
$result_user = mysqli_query($conn, $sql_user);

// Ambil semua data karakter terkait dengan username
$characters = [];
while ($user = mysqli_fetch_assoc($result_user)) {
    $characters[] = $user; // Simpan karakter dalam array
}

// Periksa apakah pengguna adalah admin
$isAdmin = ($account['admin'] > 1);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Pengguna</title>
    <link rel="stylesheet" href="../css/profile.css">
    <script>
        function toggleMenu() {
            var menu = document.getElementById("menu");
            menu.classList.toggle("active");
        }
    </script>
</head>
<body>
    <!-- Hamburger Menu -->
    <div class="hamburger" onclick="toggleMenu()">
        <div></div>
        <div></div>
        <div></div>
    </div>

    <!-- Navigation Menu -->
    <ul id="menu" class="menu">
        <li><a href="zekha.php">Home</a></li>
        <li><a href="profile.php">Profile</a></li>
        <li><a href="../admin/settings.php">Settings</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>

    <?php if ($isAdmin): ?>
    <ul id="adminMenu" class="menu">
        <li><a href="dashboard.php">Dashboard</a></li>
        <li><a href="../admin/settings.php">Settings</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
    <?php endif; ?>

    <div class="profile-info">
        <h1>Profil Pengguna</h1>
        <div class="profile-details">
            <p class="txt"><strong>Username (UCP):</strong> <?php echo htmlspecialchars($account['Username']); ?></p>
            <?php 
            // Konversi RegisterDate dari timestamp ke format tanggal
            if (!empty($account['RegisterDate'])) {
                $registerDate = date("d-m-Y H:i:s", $account['RegisterDate']); // Format: dd-mm-yyyy hh:mm:ss
            } else {
                $registerDate = "Tidak tersedia"; // Jika data kosong
            }
            ?>
            <p class="txt"><strong>Tanggal Pendaftaran:</strong> <?php echo htmlspecialchars($registerDate); ?></p>
        </div>
    </div>

    <!-- Daftar Karakter -->
    <div class="profile-info">
        <h1>Karakter Anda</h1>
        <?php if (count($characters) > 0): ?>
            <ul>
                <?php foreach ($characters as $character): ?>
                    <p class="txt"><strong>Nama Karakter:</strong> <?php echo htmlspecialchars($character['Username']); ?></p>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="txt">Tidak ada karakter yang ditemukan untuk akun ini.</p>
        <?php endif; ?>
    </div>

</body>
</html>
