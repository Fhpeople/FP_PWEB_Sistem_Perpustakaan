<?php
if ($_SESSION['pengguna']['status_pengguna'] == 'anggota') {
    echo "<script>alert('Akses ditolak.'); location.href='?page=buku';</script>";
    exit;
}
?>
<?php
$id = $_GET['id'];
$query = mysqli_query($koneksi, "DELETE FROM kategori_buku WHERE id_kategori=$id");
?>
<script>
    alert('Kategori berhasil dihapus.');
    location.href = "index.php?page=kategori";
</script>