<?php
// Memulai session
session_start();

// Menghapus session
session_destroy();

// Redirect ke halaman register
header("Location: register.php");
exit;
?>
