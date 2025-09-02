<?php
// URL Helper functions

// Fungsi redirect sudah ada di session_helper.php, jadi tidak perlu didefinisikan lagi di sini
// Jika ingin menggunakan fungsi redirect, pastikan session_helper.php sudah diinclude terlebih dahulu

// Get base URL
function base_url() {
    // Get the protocol
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
    
    // Get the host (domain name)
    $host = $_SERVER['HTTP_HOST'];
    
    // Get the base path
    $base_path = dirname($_SERVER['SCRIPT_NAME']);
    $base_path = str_replace('\\', '/', $base_path);
    $base_path = rtrim($base_path, '/');
    
    // Return the full base URL
    return $protocol . $host . $base_path;
}

// Generate URL with base
function url($path = '') {
    return base_url() . '/' . ltrim($path, '/');
}
?>
