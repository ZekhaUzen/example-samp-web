<?php
// Sertakan koneksi database dan session
require_once '../mysql/db_connection.php';
session_start();

// Periksa apakah pengguna sudah login
if (!isset($_SESSION['username'])) {
    header("Location: ../php/login.php");
    exit;
}

// Ambil informasi akun pengguna dari database berdasarkan Username
$username = $_SESSION['username'];
$query = "SELECT * FROM accounts WHERE Username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$account = $result->fetch_assoc();

// Periksa apakah data akun ditemukan
if (!$account) {
    echo "Gagal mengambil data akun.";
    exit;
}

// Konversi nilai admin menjadi integer
$account['admin'] = isset($account['admin']) ? (int)$account['admin'] : 0;

// Debugging (opsional: hanya untuk memastikan nilai admin benar)
if ($account['admin'] <= 1) {
    echo "Nilai admin: " . $account['admin']; // Debugging output
    exit("Anda tidak memiliki akses ke halaman ini.");
}

// Jika admin, tampilkan halaman pengaturan
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings</title>
    <link rel="stylesheet" href="./style.css">
</head>
<body>
    <div class="container">
        <div class="hamburger" onclick="toggleMenu()">
            <div></div>
            <div></div>
            <div></div>
        </div>
        <ul class="menu">
            <li><a href="../php/profile.php">Profile</a></li>
            <li><a href="settings.php">Settings</a></li>
            <li><a href="../php/logout.php">Logout</a></li>
        </ul>
        <h1 class="txt">Settings</h1>
        <p class="txt">Selamat datang, <strong><?php echo htmlspecialchars($account['Username']); ?></strong>. Anda berada di halaman pengaturan.</p>
        <div class="settings-section">
            <h2>Pengaturan Admin</h2>
            <p>Fitur-fitur pengaturan khusus admin akan ditampilkan di sini.</p>
        </div>
    </div>
    <script>
        function toggleMenu() {
            const menu = document.querySelector('.menu');
            menu.classList.toggle('active');
        }
    </script>
</body>
</html>
