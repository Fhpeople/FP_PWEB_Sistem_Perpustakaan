<?php
if ($_SESSION['pengguna']['status_pengguna'] == 'anggota') {
    echo "<script>alert('Akses ditolak.'); location.href='?page=buku';</script>";
    exit;
}
?>
<?php
$id = $_GET['id'];
$query = mysqli_query($koneksi, "DELETE FROM buku WHERE id_buku=$id");
?>
<script>
    alert('Buku berhasil dihapus.');
    location.href = "index.php?page=buku";
</script>