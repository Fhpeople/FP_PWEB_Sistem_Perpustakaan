<?php

if (!isset($_SESSION['pengguna']['id_pengguna'])) {
    echo "<script>alert('Silakan login terlebih dahulu!'); window.location.href='login.php';</script>";
    exit();
}

if (!isset($_GET['id'])) {
    echo "<script>alert('ID Buku tidak valid!'); window.location.href='?page=buku';</script>";
    exit();
}

$id_buku = mysqli_real_escape_string($koneksi, $_GET['id']);
$id_pengguna = $_SESSION['pengguna']['id_pengguna'];

$query_cek_buku = "SELECT * FROM buku WHERE id_buku = '$id_buku'";
$result_cek_buku = mysqli_query($koneksi, $query_cek_buku);

if (mysqli_num_rows($result_cek_buku) == 0) {
    echo "<script>alert('Buku tidak ditemukan!'); window.location.href='?page=buku';</script>";
    exit();
}

$buku = mysqli_fetch_assoc($result_cek_buku);

if ($buku['status_buku'] != 'tersedia') {
    echo "<script>alert('Maaf, buku sedang tidak tersedia. Status: " . ucfirst($buku['status_buku']) . "'); window.location.href='?page=buku';</script>";
    exit();
}

$query_cek_peminjaman = "SELECT * FROM transaksi_peminjaman 
                         WHERE id_pengguna = '$id_pengguna' 
                         AND id_buku = '$id_buku' 
                         AND status_peminjaman = 'dipinjam'";
$result_cek_peminjaman = mysqli_query($koneksi, $query_cek_peminjaman);

if (mysqli_num_rows($result_cek_peminjaman) > 0) {
    echo "<script>alert('Anda sudah meminjam buku ini dan belum mengembalikannya!'); window.location.href='?page=buku';</script>";
    exit();
}

$query_cek_booking = "SELECT * FROM booking_buku 
                      WHERE id_pengguna = '$id_pengguna' 
                      AND id_buku = '$id_buku' 
                      AND status_booking = 'masa booking'";
$result_cek_booking = mysqli_query($koneksi, $query_cek_booking);

$tanggal_peminjaman = date('Y-m-d');
$status_peminjaman = 'dipinjam';

$query_insert = "INSERT INTO transaksi_peminjaman 
                 (id_pengguna, id_buku, tanggal_peminjaman, tanggal_pengembalian, status_peminjaman) 
                 VALUES 
                 ('$id_pengguna', '$id_buku', '$tanggal_peminjaman', NULL, '$status_peminjaman')";

if (mysqli_query($koneksi, $query_insert)) {
    $query_update_buku = "UPDATE buku SET status_buku = 'dipinjam' WHERE id_buku = '$id_buku'";
    mysqli_query($koneksi, $query_update_buku);
    
    if (mysqli_num_rows($result_cek_booking) > 0) {
        $query_update_booking = "UPDATE booking_buku 
                                SET status_booking = 'buku dipinjam' 
                                WHERE id_pengguna = '$id_pengguna' 
                                AND id_buku = '$id_buku' 
                                AND status_booking = 'menunggu'" ;
        mysqli_query($koneksi, $query_update_booking);
    }
    
    echo "<script>
        alert('Peminjaman berhasil untuk buku \"" . addslashes($buku['judul']) . "\"!\\nJangan lupa kembalikan tepat waktu ya!');
        window.location.href='?page=peminjaman';
    </script>";
    exit();
} else {
    echo "<script>alert('Gagal melakukan peminjaman: " . mysqli_error($koneksi) . "'); window.location.href='?page=buku';</script>";
    exit();
}
?>
