<?php
session_start();

// Periksa apakah pengguna sudah login
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}
// Tentukan lokasi dan nama file log
$logFile = 'server_debug.txt';  // Menggunakan ekstensi .txt untuk file log

// Membuka file log untuk ditulis
$logHandle = fopen($logFile, 'a');  // 'a' berarti append, data akan ditambahkan ke akhir file

if ($logHandle) {
    // Menulis data log ke file (misalnya menulis response dari server)
    fwrite($logHandle, "Debugging response: \n");
    fwrite($logHandle, var_export($response, true));  // Menulis data mentah server dengan format yang bisa dibaca
    fwrite($logHandle, "\n\n");  // Menambahkan garis baru setelah setiap log
    
    // Menutup file log setelah menulis
    fclose($logHandle);
} else {
    echo "Gagal membuka file log.";
}

function getSampServerInfo($ip, $port) {
    $socket = fsockopen('udp://' . $ip, $port, $errno, $errstr, 2);
    if (!$socket) {
        return null; // Server offline
    }

    stream_set_timeout($socket, 2);
    stream_set_blocking($socket, true);

    // Format query standar SA-MP (A)
    $query = "\xFF\xFF\xFF\xFF\x00\x01\x00\x00";  // Query standar untuk SA-MP
    fwrite($socket, $query);
    
    $response = fread($socket, 2048);
    fclose($socket);

    if (!$response || strlen($response) < 16) {
        return null; // Jika responnya kosong atau data tidak lengkap
    }

    $info = [];
    $offset = 0;

    // Read Hostname (biasanya dimulai setelah 16 byte pertama)
    $hostname = substr($response, 13, strpos($response, "\x00", 13) - 13);
    $info['hostname'] = $hostname;

    // Read Gamemode (sesuai offset dan panjang string dalam response)
    $gamemode = substr($response, strpos($response, "\x00", 13) + 1, strpos($response, "\x00", strpos($response, "\x00", 13) + 1) - (strpos($response, "\x00", 13) + 1));
    $info['gamemode'] = $gamemode;

    // Read Language (biasanya setelah Gamemode)
    $language = substr($response, strpos($response, "\x00", strpos($response, "\x00", 13) + 1) + 1, strpos($response, "\x00", strpos($response, "\x00", strpos($response, "\x00", 13) + 1) + 1) - (strpos($response, "\x00", strpos($response, "\x00", 13) + 1) + 1));
    $info['language'] = $language;

    // Read Jumlah Pemain (offset untuk jumlah pemain dan kapasitas)
    $players = unpack('v', substr($response, 3, 2))[1];  // Jumlah Pemain
    $maxPlayers = unpack('v', substr($response, 5, 2))[1];  // Kapasitas Maksimum Pemain

    $info['players'] = $players;
    $info['maxPlayers'] = $maxPlayers;

    return $info;
}

// Konfigurasi server
$serverIp = '127.0.0.1'; // Ganti dengan IP server
$serverPort = 7777;      // Ganti dengan port server

$serverInfo = getSampServerInfo($serverIp, $serverPort);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status Server</title>
    <link rel="stylesheet" href="../css/zekha.css">
    <script>
        function toggleMenu() {
            var menu = document.getElementById("menu");
            menu.classList.toggle("active");
        }
    </script>
</head>
<body>
    <div class="hamburger" onclick="toggleMenu()">
        <div></div>
        <div></div>
        <div></div>
    </div>
    <ul id="menu" class="menu">
        <li><a href="zekha.php">Home</a></li>
        <li><a href="profile.php">Profile</a></li>
        <li><a href="../admin/settings.php">Settings</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
        <!-- Informasi Pengguna -->
    <div class="server-info">
        <h1 class="txt">Selamat datang, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong></h1>
    </div>
    <div class="server-info">
    <h1>Status Server</h1>
    <?php if ($serverInfo): ?>
        <p>Hostname: <?php echo htmlspecialchars($serverInfo['hostname']); ?></p>
        <p>Gamemode: <?php echo htmlspecialchars($serverInfo['gamemode']); ?></p>
        <p>Language: <?php echo htmlspecialchars($serverInfo['language']); ?></p>
        <p>Jumlah Pemain: <?php echo $serverInfo['players']; ?> / <?php echo $serverInfo['maxPlayers']; ?></p>
    <?php else: ?>
        <p>Status Server: Offline</p>
        <p>Hostname: Null</p>
        <p>Gamemode: Null</p>
        <p>Language: Null</p>
        <p>Jumlah Pemain: 0 / 0</p>
    <?php endif; ?>
</div>


</body>
</html>
